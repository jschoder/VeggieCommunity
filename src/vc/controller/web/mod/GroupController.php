<?php
namespace vc\controller\web\mod;

class GroupController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $groupModel = $this->getDbModel('Group');
        $this->view($groupModel);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        $groupModel = $this->getDbModel('Group');
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        if (!empty($_POST['action']) && is_array($_POST['action'])) {
            foreach ($_POST['action'] as $groupId => $action) {
                if ($action == 'confirm') {
                    $confirmed = $groupModel->setGroupConfirmed($groupId, $this->getSession()->getUserId());
                    if ($confirmed) {
                        $this->createDefaultGroupElements($groupModel, $groupId);
                    }
                } elseif ($action == 'delete') {
                    $groupModel->setGroupDenied($groupId, $this->getSession()->getUserId());
                } elseif ($action == 'deny') {
                    $groupModel->setGroupDenied($groupId, $this->getSession()->getUserId());
                }
            }
        }
        throw new \vc\exception\RedirectException($this->path . 'mod/groups/');
    }

    private function createDefaultGroupElements($groupModel, $groupId)
    {
        $success = true;

        $group = $groupModel->loadObject(array('id' => $groupId));
        if ($group !== null) {
            $groupMemberModel = $this->getDbModel('GroupMember');
            $groupMemberObject = new \vc\object\GroupMember();
            $groupMemberObject->groupId = $group->id;
            $groupMemberObject->profileId = $group->createdBy;
            $groupMemberObject->confirmedAt = date('Y-m-d H:i:s');
            $savedMember = $groupMemberModel->insertObject($this->getSession()->getProfile(), $groupMemberObject);
            if ($savedMember === false) {
                $success = false;
            }

            $groupRoleModel = $this->getDbModel('GroupRole');
            $groupRoleObject = new \vc\object\GroupRole();
            $groupRoleObject->groupId = $group->id;
            $groupRoleObject->profileId = $group->createdBy;
            $groupRoleObject->role = \vc\object\GroupRole::ROLE_ADMIN;
            $savedRole = $groupRoleModel->insertObject($this->getSession()->getProfile(), $groupRoleObject);
            if ($savedRole === false) {
                $success = false;
            }

            $groupForumModel = $this->getDbModel('GroupForum');
            $savedForum = $groupForumModel->createDefaultForum(
                $group->id,
                $this->getSession()->getProfile()
            );

            // :TODO: JOE !!! - auto subscribe to forum (also on manually added forums)

            if ($savedForum === false) {
                $success = false;
            } else {
                $groupNotificationModel = $this->getDbModel('GroupNotification');
                $groupNotificationModel->add(
                    $group->createdBy,
                    \vc\object\GroupNotification::TYPE_GROUP_CREATION_ACCEPTED,
                    $group->id,
                    $this->getSession()->getUserId()
                );
            }
        } else {
            $success = false;
        }
        return $success;
    }

    private function view($groupModel)
    {
        $groups = $groupModel->getUnconfirmedGroups();

        $this->setTitle('Unconfirmed Groups');

        $this->getView()->set('groups', $groups);
        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/groups', true);
    }
}
