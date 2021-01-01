<?php
namespace vc\controller\web\subscription;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.noactivesession'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['entityType']) ||
            empty($formValues['entityId'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.subscribe.failed'));
            return;
        }

        if ($formValues['entityType'] == \vc\config\EntityTypes::GROUP_FORUM) {
            $groupForumModel = $this->getDbModel('GroupForum');
            $forumObject = $groupForumModel->loadObject(array('hash_id' => $formValues['entityId']));
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
            $groupMemberModel = $this->getDbModel('GroupMember');
            if (!$groupMemberModel->isMember($forumObject->groupId, $this->getSession()->getUserId())) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONMEMBER,
                    array(
                        'formValues' => $formValues
                    )
                );
                echo \vc\view\json\View::renderStatus(false);
                return;
            }
            $entityId = $forumObject->id;
        } elseif ($formValues['entityType'] == \vc\config\EntityTypes::FORUM_THREAD) {
            $threadModel = $this->getDbModel('ForumThread');
            $threadObject = $threadModel->loadObject(array('hash_id' => $formValues['entityId']));
            if ($threadObject->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
                if (empty($threadObject)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM_THREAD,
                        array(
                            'formValues' => $formValues
                        )
                    );
                    echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.subscribe.failed'));
                    return;
                }
                $groupForumModel = $this->getDbModel('GroupForum');
                $groupId = $groupForumModel->getField('group_id', 'id', $threadObject->contextId);
                $groupMemberModel = $this->getDbModel('GroupMember');
                if (!$groupMemberModel->isMember($groupId, $this->getSession()->getUserId())) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONMEMBER,
                        array(
                            'formValues' => $formValues
                        )
                    );
                    echo \vc\view\json\View::renderStatus(false);
                    return;
                }
            } elseif ($threadObject->contextType == \vc\config\EntityTypes::EVENT) {
                $eventModel = $this->getDbModel('Event');
                if (!$eventModel->canSeeEvent($this->getSession()->getUserId(), $threadObject->contextId)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_ACCESS_INVISIBLE_EVENT,
                        array(
                            'formValues' => $formValues
                        )
                    );
                    echo \vc\view\json\View::renderStatus(false);
                    return;
                }
            }
            $entityId = $threadObject->id;
        } else {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.subscribe.failed'));
            return;
        }

        $subscriptionModel = $this->getDbModel('Subscription');
        $success = $subscriptionModel->add(
            $this->getSession()->getUserId(),
            intval($formValues['entityType']),
            $entityId
        );
        echo \vc\view\json\View::renderStatus($success);
    }
}
