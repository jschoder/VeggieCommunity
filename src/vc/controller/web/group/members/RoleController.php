<?php
namespace vc\controller\web\group\members;

class RoleController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        $success = true;

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
            !array_key_exists('profileId', $formValues) ||
            !array_key_exists('role', $formValues) ||
            !array_key_exists('action', $formValues)) {
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

        if ($formValues['action'] == 'add') {
            // Modified rows not really necessary
            $groupRoleModel->deleteRole($groupObject->id, intval($formValues['profileId']));

            $groupRoleObject = new \vc\object\GroupRole();
            $groupRoleObject->groupId = $groupObject->id;
            $groupRoleObject->profileId = $formValues['profileId'];
            $groupRoleObject->role = $formValues['role'];
            $groupRoleModel->insertObject($this->getSession()->getProfile(), $groupRoleObject);
        } elseif ($formValues['action'] == 'remove') {
            $success = $groupRoleModel->deleteRole($groupObject->id, intval($formValues['profileId']));
            $groupRoleModel->fixGroupAdminsIfRequired($groupObject->id, $this->getSession()->getProfile());
        } else {
            $success = false;
        }

        if ($success) {
            $profileBoxRendered = $this->renderProfileBox($groupObject, $formValues['profileId']);
            echo \vc\view\json\View::render(array('success' => $success,
                                                  'profilebox' => $profileBoxRendered));
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.action.failed'));
        }
    }

    private function renderProfileBox($groupObject, $profileId)
    {
        $profileModel = $this->getDbModel('Profile');
        $members = $profileModel->loadObjects(
            array(
                'vc_group_member.group_id' => $groupObject->id,
                'vc_group_member.profile_id' => $profileId,
                'vc_group_member.confirmed_at IS NOT NULL'
            ),
            array('INNER JOIN vc_group_member ON vc_group_member.profile_id = vc_profile.id'),
            'vc_group_member.confirmed_at DESC',
            null,
            '\\vc\object\\Profile'
        );

        if (count($members) === 0) {
            return null;
        }
        $member = $members[0];

        $groupRoleModel = $this->getDbModel('GroupRole');
        $groupRole = $groupRoleModel->getRole($groupObject->id, $profileId);

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures($this->getSession()->getUserId(), $members);
        $actions = array();
        if ($this->getSession()->getUserId() != $member->id) {
            $actions[] = sprintf(
                '<a class="remove" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
                $groupObject->hashId,
                $member->id,
                gettext('group.members.action.remove')
            );
            $actions[] = sprintf(
                '<a class="ban" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
                $groupObject->hashId,
                $member->id,
                gettext('group.members.action.ban')
            );
            if ($groupRole == \vc\object\GroupRole::ROLE_ADMIN) {
                $actions[] = sprintf(
                    '<a class="adminRemove" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
                    $groupObject->hashId,
                    $member->id,
                    gettext('group.members.action.admin.remove')
                );
            } elseif ($groupRole == \vc\object\GroupRole::ROLE_MODERATOR) {
                $actions[] = sprintf(
                    '<a class="modRemove" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
                    $groupObject->hashId,
                    $member->id,
                    gettext('group.members.action.moderator.remove')
                );
                $actions[] = sprintf(
                    '<a class="adminAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
                    $groupObject->hashId,
                    $member->id,
                    gettext('group.members.action.admin.add')
                );
            } else {
                $actions[] = sprintf(
                    '<a class="modAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
                    $groupObject->hashId,
                    $member->id,
                    gettext('group.members.action.moderator.add')
                );
                $actions[] = sprintf(
                    '<a class="adminAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
                    $groupObject->hashId,
                    $member->id,
                    gettext('group.members.action.admin.add')
                );
            }
        }
        $rendering = $this->getView()->element(
            'profilebox',
            array('path' => $this->path,
                  'imagesPath' => $this->imagesPath,
                  'usersOnline'=>$this->getUsersOnline(),
                  'currentUser'=>$this->getSession()->getProfile(),
                  'sessionSettings' => $this->getSession()->getSettings(),

                  'id'=>'groupMemberProfilebox' . $member->id,
                  'profile'=>$member,
                  'picture'=>$pictures[$member->id],
                  'isAdmin'=>$this->getSession()->isAdmin(),

                  'ownFavorites' => $this->getOwnFavorites(),
                  'ownFriendsConfirmed' => $this->getOwnFriendsConfirmed(),
                  'ownFriendsToConfirm' => $this->getOwnFriendsToConfirm(),
                  'ownFriendsWaitForConfirm' => $this->getOwnFriendsWaitForConfirm(),
                  'blocked' => $this->getBlocked(),
                  'actions'=>$actions)
        );
        return $rendering;
    }
}
