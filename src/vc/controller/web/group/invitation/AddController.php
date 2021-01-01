<?php
namespace vc\controller\web\group\invitation;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo $this->getView()->element(
                'notification',
                array(
                    'notification' => array(
                        'type' => self::NOTIFICATION_WARNING,
                        'message' => gettext('error.noactivesession')
                    )
                )
            );
            return;
        }

        if (empty($this->siteParams) === 0) {
            echo $this->getView()->element(
                'notification',
                array(
                    'notification' => array(
                        'type' => self::NOTIFICATION_ERROR,
                        'message' => gettext('error.invalidrequest')
                    )
                )
            );
            return;
        }

        $groupModel = $this->getDbModel('Group');
        $groupId = $groupModel->getIdByHashId($this->siteParams[0]);
        if ($groupId === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array(
                    'siteParams' => $this->siteParams
                )
            );
            echo $this->getView()->element(
                'notification',
                array(
                    'notification' => array(
                        'type' => self::NOTIFICATION_ERROR,
                        'message' => gettext('error.invalidrequest')
                    )
                )
            );
            return;
        }

        $friendIds = $this->getOwnFriendsConfirmed();

        // Remove the ones who are already members of already invited by current user
        $groupMemberModel = $this->getDbModel('GroupMember');
        $groupMemberIds = $groupMemberModel->getMemberIds($groupId);
        $groupInvitationModel = $this->getDbModel('GroupInvitation');
        $invitedUsers = $groupInvitationModel->getInvitedUsers($groupId, $this->getSession()->getUserId());
        $friendIds = array_diff($friendIds, $invitedUsers, $groupMemberIds);

        $profileModel = $this->getDbModel('Profile');
        $friendProfiles = $profileModel->getSmallProfiles(
            $this->locale,
            $friendIds,
            "last_update DESC"
        );
        $this->getView()->set('profiles', $friendProfiles);

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures(
            $this->getSession()->getUserId(),
            $friendProfiles
        );
        $this->getView()->set('pictures', $pictures);

        echo $this->getView()->render('group/invitation/add', false);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('group.invitation.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['groupId']) ||
            empty($formValues['profileId']) ||
            !is_array($formValues['profileId'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $groupModel = $this->getDbModel('Group');
        $groupId = $groupModel->getIdByHashId($formValues['groupId']);
        if ($groupId === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array(
                    'siteParams' => $this->siteParams
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

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

        $groupInvitationModel = $this->getDbModel('GroupInvitation');
        $groupNotificationModel = $this->getDbModel('GroupNotification');
        $success = true;
        $currentUser = $this->getSession()->getProfile();
        foreach ($formValues['profileId'] as $profileId) {
            if (in_array(intval($profileId), $this->getOwnFriendsConfirmed())) {
                $groupInvitationObject = new \vc\object\GroupInvitation();
                $groupInvitationObject->groupId = $groupId;
                $groupInvitationObject->profileId = intval($profileId);
                $groupInvitationObject->comment = $formValues['comment'];
                $groupInvitationObject->createdBy = $this->getSession()->getUserId();
                $groupInvitationModel->insertObject($currentUser, $groupInvitationObject);

                $success = $success && $groupNotificationModel->add(
                    $profileId,
                    \vc\object\GroupNotification::TYPE_GROUP_INVITATION,
                    $groupId,
                    $this->getSession()->getUserId()
                );
            }
        }

        echo \vc\view\json\View::renderStatus($success);
    }
}
