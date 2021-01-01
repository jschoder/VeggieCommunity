<?php
namespace vc\controller\web\group;

class MyGroupsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('groups.mygroups.title'));

        $groupForumModel = $this->getDbModel('GroupForum');
        $lastUpdates = $groupForumModel->getLastUpdates($this->getSession()->getUserId());
        $this->getView()->set('lastUpdates', $lastUpdates);

        $groupMemberModel = $this->getDbModel('GroupMember');
        $unconfirmedMembers = $groupMemberModel->getUnconfirmedMembersByModerator($this->getSession()->getUserId());
        $this->getView()->set('unconfirmedMembers', $unconfirmedMembers);

        $flagModel = $this->getDbModel('Flag');
        $flags = $flagModel->getGroupFlagsForMods($this->getSession()->getUserId());
        $this->getView()->set('flags', $flags);

        $groupInvitationModel = $this->getDbModel('GroupInvitation');
        $invitations = $groupInvitationModel->getOpenInvitations($this->getSession()->getUserId());
        $this->getView()->set('invitations', $invitations);

        echo $this->getView()->render('group/mygroups', true);
    }
}
