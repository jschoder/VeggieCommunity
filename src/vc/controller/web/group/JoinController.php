<?php
namespace vc\controller\web\group;

class JoinController extends \vc\controller\web\AbstractWebController
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
            throw new \vc\exception\NotFoundException();
        }
        $groupHashId = $formValues['id'];

        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'groups/info/' . $groupHashId);
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $groupModel = $this->getDbModel('Group');
        $groupObject = $groupModel->loadObject(array('hash_id' => $groupHashId));
        if ($groupObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array('FormValues' => $formValues)
            );
            throw new \vc\exception\NotFoundException();
        }

        $groupBanModel = $this->getDbModel('GroupBan');
        $groupBanObject = $groupBanModel->loadObject(
            array('group_id' => $groupObject->id, 'profile_id' => $this->getSession()->getUserId())
        );
        if ($groupBanObject !== null) {
            $notification = $this->setNotification(self::NOTIFICATION_WARNING, gettext('group.join.banned'));
            throw new \vc\exception\RedirectException(
                $this->path . 'groups/info/' . $groupHashId . '/?notification=' . $notification
            );
        }

        $groupInvitationModel = $this->getDbModel('GroupInvitation');
        $groupInvitationModel->update(
            array(
                'group_id' => $groupObject->id,
                'profile_id' => $this->getSession()->getUserId(),
                'updated_at' => null
            ),
            array(
                'updated_at' => date('Y-m-d H:i:s')
            ),
            false
        );

        $groupMemberObject = new \vc\object\GroupMember();
        $groupMemberObject->groupId = $groupObject->id;
        if ($groupObject->autoConfirmMembers) {
            $groupMemberObject->confirmedBy = 0;
            $groupMemberObject->confirmedAt = date('Y-m-d H:i:s');
        }
        $groupMemberModel = $this->getDbModel('GroupMember');
        $objectSaved = $groupMemberModel->insertObject(
            $this->getSession()->getProfile(),
            $groupMemberObject
        );
        
        if ($groupObject->id === 5) {
            $profileCommentLog = new \vc\object\ProfileCommentLog();
            $profileCommentLog->profileId = $this->getSession()->getUserId();
            $profileCommentLog->comment = 'Joined Group "Sexanfragen"';
        
            $profileCommentLogModel = $this->getDbModel('ProfileCommentLog');
            $profileCommentLogModel->insertObject(
                $this->getSession()->getProfile(),
                $profileCommentLog
            );
        }

        if ($objectSaved !== false) {
            if ($groupObject->autoConfirmMembers) {
                $subscriptionModel = $this->getDbModel('Subscription');
                $subscriptionModel->subscribeToAllGroupForums($groupObject->id, $this->getSession()->getUserId());

                $url = 'groups/forum/';
                $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('group.join.autoconfirmed'));
            } else {
                $cacheModel = $this->getModel('Cache');
                $groupRoleModel = $this->getDbModel('GroupRole');
                $groupNotificationModel = $this->getDbModel('GroupNotification');
                $groupRoles = $groupRoleModel->getGroupRoles($groupObject->id);
                foreach ($groupRoles[\vc\object\GroupRole::ROLE_ADMIN] as $adminUserId) {
                    $groupNotificationModel->add(
                        $adminUserId,
                        \vc\object\GroupNotification::TYPE_GROUP_MEMBER_REQUESTS,
                        $groupObject->id,
                        $this->getSession()->getUserId()
                    );

                    $settings = $cacheModel->getSettings($adminUserId);
                    if ($settings->getValue(\vc\object\Settings::GROUP_MEMBER_NOTIFICATION)) {
                        $mailComponent = $this->getComponent('Mail');
                        $mailComponent->sendMailToUser(
                            $this->locale,
                            $adminUserId,
                            'group.join.mailNotification',
                            'group-member-request',
                            array(
                                'GROUPNAME' => $groupObject->name,
                                'LINK' => 'groups/info/' . $groupObject->hashId . '/'
                            )
                        );
                    }
                }

                $url = 'groups/forum/';
                $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('group.join.success'));
            }
        } else {
            $url = 'groups/info/';
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('group.join.failed'));
        }
        throw new \vc\exception\RedirectException(
            $this->path . $url . $groupHashId . '/?notification=' . $notification
        );
    }
}
