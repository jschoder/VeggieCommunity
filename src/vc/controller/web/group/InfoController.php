<?php
namespace vc\controller\web\group;

class InfoController extends AbstractGroupController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (count($this->siteParams) === 0) {
            throw new \vc\exception\NotFoundException();
        }

        $this->loadGroup($this->siteParams[0]);
        $groupObject = $this->getGroupObject();
        $this->setTitle($groupObject->name);

        if ($this->siteParams[0] === 'ft2euk0') {
            $this->getView()->setHeader('robots', 'noindex, follow');
        }

        if ($this->getGroupRole() !== null) {
            $groupMemberModel = $this->getDbModel('GroupMember');
            $unconfirmedMemberIds = $groupMemberModel->getUnconfirmedMembersByGroup($groupObject->id);
            if (count($unconfirmedMemberIds) > 0) {
                $profileModel = $this->getDbModel('Profile');
                $pictureModel = $this->getDbModel('Picture');
                $unconfirmedMembers = $profileModel->getProfiles($this->locale, $unconfirmedMemberIds);
                $unconfirmedMemberPictures = $pictureModel->readPictures(
                    $this->getSession()->getUserId(),
                    $unconfirmedMembers
                );
                $this->getView()->set('unconfirmedMembers', $unconfirmedMembers);
                $this->getView()->set('unconfirmedMemberPictures', $unconfirmedMemberPictures);
            }
        }

        $groupForumModel = $this->getDbModel('GroupForum');
        $forums = $groupForumModel->loadObjects(
            array('group_id' => $groupObject->id, 'deleted_at IS NULL'),
            array(),
            'weight ASC'
        );
        if (empty($forums)) {
            \vc\lib\ErrorHandler::error(
                'No forums for group #' . $groupObject->id,
                __FILE__,
                __LINE__
            );
            throw new \vc\exception\NotFoundException();
        }
        $this->getView()->set('forums', $forums);

        if ($this->getSession()->hasActiveSession()) {
            $groupInvitationModel = $this->getDbModel('GroupInvitation');
            $invitations = $groupInvitationModel->getOpenInvitationsForGroup(
                $this->getSession()->getUserId(),
                $groupObject->id
            );
            $this->getView()->set('invitations', $invitations);
        }

        $profileModel = $this->getDbModel('Profile');
        $members = $profileModel->loadObjects(
            array('vc_group_member.group_id' => $groupObject->id, 'vc_group_member.confirmed_at IS NOT NULL'),
            array('INNER JOIN vc_group_member ON vc_group_member.profile_id = vc_profile.id'),
            'vc_group_member.confirmed_at DESC',
            null,
            '\\vc\object\\Profile'
        );
        $memberIds = array();
        foreach ($members as $member) {
            $memberIds[] = $member->id;
        }

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures($this->getSession()->getUserId(), $members);
        $this->getView()->set('pictures', $pictures);

        $this->getView()->set('members', $members);

        echo $this->getView()->render('group/info', true);
    }
}
