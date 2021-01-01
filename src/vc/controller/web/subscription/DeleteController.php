<?php
namespace vc\controller\web\subscription;

class DeleteController extends \vc\controller\web\AbstractWebController
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
                echo \vc\view\json\View::renderStatus(true);
                return;
            }
            $entityId = $forumObject->id;
        } elseif ($formValues['entityType'] == \vc\config\EntityTypes::FORUM_THREAD) {
            $threadModel = $this->getDbModel('ForumThread');
            $threadObject = $threadModel->loadObject(array('hash_id' => $formValues['entityId']));
            if (empty($threadObject)) {
                echo \vc\view\json\View::renderStatus(true);
                return;
            }
            $entityId = $threadObject->id;
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.unsubscribe.failed'));
            return;
        }

        $subscriptionModel = $this->getDbModel('Subscription');
        $success = $subscriptionModel->deleteSubscription(
            $this->getSession()->getUserId(),
            intval($formValues['entityType']),
            $entityId
        );

        echo \vc\view\json\View::renderStatus($success);
    }
}
