<?php
namespace vc\controller\web\group\members;

class RemoveController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = $_POST;
        if (!array_key_exists('groupId', $formValues) ||
            !array_key_exists('profileId', $formValues)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.action.failed'));
            return;
        }

        $groupModel = $this->getDbModel('Group');
        $groupObject = $groupModel->loadObject(array('hash_id' => $formValues['groupId']));
        if ($groupObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array(
                    'groupHashId' => $formValues['groupId']
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.action.failed'));
            return;
        }

        $groupRoleModel = $this->getDbModel('GroupRole');
        $groupRole = $groupRoleModel->getRole($groupObject->id, $this->getSession()->getUserId());
        // Neither mod nor admin
        if ($groupRole === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONADMIN,
                array(
                    'groupId' => $groupObject->id,
                    'userId' => $this->getSession()->getUserId()
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.action.failed'));
            return;
        }

        $groupMemberModel = $this->getDbModel('GroupMember');
        $success = $groupMemberModel->deleteMember($groupObject->id, intval($formValues['profileId']));
        if ($success) {
            $groupRoleModel->delete(
                array(
                    'profile_id' => intval($formValues['profileId']),
                    'group_id' => $groupObject->id
                )
            );
            $groupRoleModel->fixGroupAdminsIfRequired($groupObject->id, $this->getSession()->getProfile());

            if (array_key_exists('ban', $formValues) &&
                !empty($formValues['ban'])) {
                $groupBanModel = $this->getDbModel('GroupBan');
                $groupBanObject = new \vc\object\GroupBan();
                $groupBanObject->groupId = $groupObject->id;
                $groupBanObject->profileId = intval($formValues['profileId']);
                $groupBanObject->bannedBy = $this->getSession()->getUserId();
                $groupBanObject->reason = '';
                $success = $groupBanModel->insertObject($this->getSession()->getProfile(), $groupBanObject);
            }
        }

        $cacheModel = $this->getModel('Cache');
        $cacheModel->resetProfileRelations(intval($formValues['profileId']));

        if ($success) {
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.action.failed'));
        }
    }
}
