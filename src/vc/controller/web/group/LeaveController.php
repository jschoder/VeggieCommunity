<?php
namespace vc\controller\web\group;

class LeaveController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        $formValues = $_POST;
        if (!array_key_exists('id', $formValues)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('group.leave.failed'));
            throw new \vc\exception\RedirectException($this->path . 'groups/?notification=' . $notification);
        }

        if (!$this->getSession()->hasActiveSession()) {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('group.members.noactivesession'));
            throw new \vc\exception\RedirectException($this->path . 'groups/?notification=' . $notification);
        }

        $groupModel = $this->getDbModel('Group');
        $groupObject = $groupModel->loadObject(array('hash_id' => $formValues['id']));
        if ($groupObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array(
                    'groupHashId' => $formValues['id']
                )
            );
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('group.leave.failed'));
            throw new \vc\exception\RedirectException(
                $this->path . 'groups/info/' . $formValues['id'] . '/?notification=' . $notification
            );
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }
        

        if ($groupObject->id === 5) {
            $profileCommentLog = new \vc\object\ProfileCommentLog();
            $profileCommentLog->profileId = $this->getSession()->getUserId();
            $profileCommentLog->comment = 'Left Group "Sexanfragen"';
        
            $profileCommentLogModel = $this->getDbModel('ProfileCommentLog');
            $profileCommentLogModel->insertObject(
                $this->getSession()->getProfile(),
                $profileCommentLog
            );
        }

        $groupMemberModel = $this->getDbModel('GroupMember');
        $success = $groupMemberModel->deleteMember($groupObject->id, $this->getSession()->getUserId());
        if ($success) {
            $subscriptionModel = $this->getDbModel('Subscription');
            $subscriptionModel->unsubscribeAllGroupForums($groupObject->id, $this->getSession()->getUserId());

            $groupRoleModel = $this->getDbModel('GroupRole');
            $groupRoleModel->delete(
                array(
                    'profile_id' => $this->getSession()->getUserId(),
                    'group_id' => $groupObject->id
                )
            );
            $groupRoleModel->fixGroupAdminsIfRequired($groupObject->id, $this->getSession()->getProfile());
            
            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations($this->getSession()->getUserId());
            
            throw new \vc\exception\RedirectException($this->path . 'groups/info/' . $formValues['id']);
        } else {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('group.leave.failed'));
            throw new \vc\exception\RedirectException(
                $this->path . 'groups/info/' . $formValues['id'] . '/?notification=' . $notification
            );
        }
    }
}
