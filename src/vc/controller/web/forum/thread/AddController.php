<?php
namespace vc\controller\web\forum\thread;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['contextType']) ||
            empty($formValues['contextId']) ||
           (empty($formValues['subject']) && empty($formValues['picture'])) ||
           (empty($formValues['picture']) && empty($formValues['body']))) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $contextType = intval($formValues['contextType']);
        if ($contextType == \vc\config\EntityTypes::GROUP_FORUM) {
            $groupForumModel = $this->getDbModel('GroupForum');
            $forumObject = $groupForumModel->loadObject(
                array('hash_id' => $formValues['contextId'])
            );
            if (empty($forumObject)) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM,
                    array(
                        'formValues' => $formValues
                    )
                );
                echo \vc\view\json\View::renderStatus(false);
                return;
            }
            $contextId = $forumObject->id;
            $groupMemberModel = $this->getDbModel('GroupMember');
            $canPost = $groupMemberModel->isMember($forumObject->groupId, $this->getSession()->getUserId());
        } elseif ($contextType == \vc\config\EntityTypes::EVENT) {
            $eventModel = $this->getDbModel('Event');
            $contextId = $eventModel->getIdByHashId($formValues['contextId']);
            if (empty($contextId)) {
                $canPost = false;
            } else {
                $canPost = $eventModel->canSeeEvent($this->getSession()->getUserId(), $contextId);
            }
        } elseif ($contextType == \vc\config\EntityTypes::PROFILE) {
            $contextId = $request->getInt('contextId');
            $canPost = $this->getSession()->getUserId() == $contextId;
        } else {
            \vc\lib\ErrorHandler::error(
                'Invalid ContextType: ' . $contextType,
                __FILE__,
                __LINE__
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

        if (!empty($formValues['picture'])) {
            // Make sure that nobody uses this script to get to a more sensitive path
            $filename = preg_replace('@([^a-z,0-9,\.])@', '', $formValues['picture']);

            if (file_exists(TEMP_PIC_DIR . '/full/' . $filename)) {
                rename(
                    TEMP_PIC_DIR . '/full/' . $filename,
                    THREAD_PIC_DIR . '/full/' . $filename
                );
            } else {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_MISSING_PICTURE,
                    array(
                        'formValues' => $formValues,
                        'filename' => $filename
                    )
                );
                echo \vc\view\json\View::renderStatus(false);
                return;
            }
        }

        $forumThreadObject = new \vc\object\ForumThread();
        $forumThreadObject->contextType = $contextType;
        $forumThreadObject->contextId = $contextId;
        $forumThreadObject->subject = $formValues['subject'];
        $forumThreadObject->body = $formValues['body'];
        $forumThreadObject->additional = empty($formValues['picture'])
            ? null
            : array(
                \vc\object\ForumThread::ADDITIONAL_PICTURE_PATH => 'forum/thread/picture',
                \vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME => $formValues['picture']
            );

        $forumThreadModel = $this->getDbModel('ForumThread');
        $objectId = $forumThreadModel->insertObject($this->getSession()->getProfile(), $forumThreadObject);

        if ($objectId !== false) {
            $this->getEventService()->added(
                \vc\config\EntityTypes::FORUM_THREAD,
                $objectId,
                $this->getSession()->getUserId()
            );

            $subscriptionModel = $this->getDbModel('Subscription');
            $subscriptionModel->add(
                $this->getSession()->getUserId(),
                \vc\config\EntityTypes::FORUM_THREAD,
                $objectId
            );

            if ($contextType == \vc\config\EntityTypes::GROUP_FORUM) {
                $groupNotificationModel = $this->getDbModel('GroupNotification');
                $groupNotificationModel->addBySubscription(
                    \vc\config\EntityTypes::GROUP_FORUM,
                    $contextId,
                    $this->getSession()->getUserId()
                );
            }


            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false);
        }
    }
}
