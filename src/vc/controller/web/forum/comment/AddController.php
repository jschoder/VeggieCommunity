<?php
namespace vc\controller\web\forum\comment;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['thread']) ||
            empty($formValues['body'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $forumThreadModel = $this->getDbModel('ForumThread');
        $forumThread = $forumThreadModel->loadObject(array('hash_id' => $formValues['thread']));
        if (empty($forumThread)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM_THREAD,
                array(
                    'threadHashId' => $formValues['thread']
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        if ($forumThread->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
            $groupForumModel = $this->getDbModel('GroupForum');
            $groupId = $groupForumModel->getField('group_id', 'id', $forumThread->contextId);
            $groupMemberModel = $this->getDbModel('GroupMember');
            $canPost = $groupMemberModel->isMember($groupId, $this->getSession()->getUserId());
        } elseif ($forumThread->contextType == \vc\config\EntityTypes::EVENT) {
            $eventModel = $this->getDbModel('Event');
            $canPost = $eventModel->canSeeEvent($this->getSession()->getUserId(), $forumThread->contextId);
        } elseif ($forumThread->contextType == \vc\config\EntityTypes::PROFILE) {
            $canPost = $this->getSession()->hasActiveSession();
        } else {

            \vc\lib\ErrorHandler::error(
                'Invalid ContextType: ' . $forumThread->contextType,
                __FILE__,
                __LINE__,
                array(
                    'threadId' => $forumThread->id
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        if (!$canPost) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_CONTEXT_AS_NONMEMBER,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $forumThreadCommentObject = new \vc\object\ForumThreadComment();
        $forumThreadCommentObject->threadId = $forumThread->id;
        $forumThreadCommentObject->body = $formValues['body'];

        $forumThreadCommentModel = $this->getDbModel('ForumThreadComment');
        $objectId = $forumThreadCommentModel->insertObject(
            $this->getSession()->getProfile(),
            $forumThreadCommentObject
        );

        if ($objectId !== false) {
            $forumThreadModel->updateLastUpdateTimestamp($forumThread->id);

            $updateModel = $this->getDbModel('Update');
            $updateModel->add(
                $objectId,
                \vc\config\EntityTypes::FORUM_COMMENT,
                \vc\object\Update::ACTION_ADD,
                $forumThread->contextType,
                $forumThread->contextId
            );

            $subscriptionModel = $this->getDbModel('Subscription');
            $subscriptionModel->add(
                $this->getSession()->getUserId(),
                \vc\config\EntityTypes::FORUM_THREAD,
                $forumThread->id
            );

            if ($forumThread->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
                $groupNotificationModel = $this->getDbModel('GroupNotification');
                $groupNotificationModel->addBySubscription(
                    \vc\config\EntityTypes::FORUM_THREAD,
                    $forumThread->id,
                    $this->getSession()->getUserId()
                );
                $groupNotificationModel->deleteNotification(
                    $this->getSession()->getUserId(),
                    \vc\object\GroupNotification::TYPE_FORUM_NEW_COMMENT,
                    $forumThread->id
                );
            }

            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false);
        }
    }
}
