<?php
namespace vc\controller\web\like;

class LikeController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('like.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['entityType']) ||
            empty($formValues['entityId']) ||
            empty($formValues['value']) ||
            ($formValues['value'] != -1 && $formValues['value'] != 1)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('like.failed'));
            return;
        }

        if ($formValues['entityType'] == \vc\config\EntityTypes::FORUM_THREAD) {
            $threadModel = $this->getDbModel('ForumThread');
            $threadObject = $threadModel->loadObject(array('hash_id' => $formValues['entityId']));
            if ($threadObject === null) {
                echo \vc\view\json\View::renderStatus(false, gettext('like.failed'));
                return;
            }

            if ($threadObject->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
                $groupForumModel = $this->getDbModel('GroupForum');
                $groupId = $groupForumModel->getField('group_id', 'id', $threadObject->contextId);
                $groupMemberModel = $this->getDbModel('GroupMember');
                if (!$groupMemberModel->isMember($groupId, $this->getSession()->getUserId())) {
                    echo \vc\view\json\View::renderStatus(false, gettext('like.failed'));
                    return;
                }
            } elseif ($threadObject->contextType == \vc\config\EntityTypes::EVENT) {
                $eventModel = $this->getDbModel('Event');
                if (!$eventModel->canSeeEvent($this->getSession()->getUserId(), $threadObject->contextId)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_ACCESS_INVISIBLE_EVENT,
                        array(
                            'eventId' => $threadObject->contextId
                        )
                    );
                    return false;
                }
            }
            $entityId = $threadObject->id;
        } elseif ($formValues['entityType'] == \vc\config\EntityTypes::FORUM_COMMENT) {
            $threadCommentModel = $this->getDbModel('ForumThreadComment');
            $threadCommentObject = $threadCommentModel->loadObject(array('hash_id' => $formValues['entityId']));
            if ($threadCommentObject === null) {
                echo \vc\view\json\View::renderStatus(false, gettext('like.failed'));
                return;
            }
            $threadModel = $this->getDbModel('ForumThread');
            $threadObject = $threadModel->loadObject(array('id' => $threadCommentObject->threadId));
            if ($threadObject->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
                $groupForumModel = $this->getDbModel('GroupForum');
                $groupId = $groupForumModel->getField('group_id', 'id', $threadObject->contextId);
                $groupMemberModel = $this->getDbModel('GroupMember');
                if (!$groupMemberModel->isMember($groupId, $this->getSession()->getUserId())) {
                    echo \vc\view\json\View::renderStatus(false, gettext('like.failed'));
                    return;
                }
            } elseif ($threadObject->contextType == \vc\config\EntityTypes::EVENT) {
                $eventModel = $this->getDbModel('Event');
                if (!$eventModel->canSeeEvent($this->getSession()->getUserId(), $threadObject->contextId)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_ACCESS_INVISIBLE_EVENT,
                        array(
                            'eventId' => $threadObject->contextId
                        )
                    );
                    return false;
                }
            }
            $entityId = $threadCommentObject->id;
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('like.failed'));
            return;
        }

        $likeModel = $this->getDbModel('Like');
        $previousLike = $likeModel->getCount(array(
            'entity_type' => intval($formValues['entityType']),
            'entity_id' => $entityId,
            'profile_id' => $this->getSession()->getUserId(),
            'up_down' => intval($formValues['value'])
        ));

        if ($previousLike) {
            $likeSaved = $likeModel->delete(array(
                'entity_type' => intval($formValues['entityType']),
                'entity_id' => $entityId,
                'profile_id' => $this->getSession()->getUserId(),
                'up_down' => intval($formValues['value'])
            ));
        } else {
            $likeSaved = $likeModel->set(
                intval($formValues['entityType']),
                $entityId,
                $this->getSession()->getUserId(),
                intval($formValues['value'])
            );
        }


        if ($likeSaved === false) {
            echo \vc\view\json\View::renderStatus(false, gettext('like.failed'));
        } else {
            $values = $likeModel->get($formValues['entityType'], $entityId);
            $values['success'] = true;
            echo \vc\view\json\View::render($values);
        }
    }
}
