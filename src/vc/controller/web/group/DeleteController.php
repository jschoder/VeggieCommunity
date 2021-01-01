<?php
namespace vc\controller\web\group;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (count($this->siteParams) === 0 ||
            !$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\NotFoundException();
        }

        $groupModel = $this->getDbModel('Group');
        $groupObject = $groupModel->loadObject(array('hash_id' => $this->siteParams[0]));
        if ($groupObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array('SiteParams' => $this->siteParams)
            );
            throw new \vc\exception\NotFoundException();
        }

        $groupMemberModel = $this->getDbModel('GroupMember');
        $groupRoleModel = $this->getDbModel('GroupRole');
        $userRole = $groupRoleModel->getRole(
            $groupObject->id,
            $this->getSession()->getUserId()
        );
        if ($userRole !== \vc\object\GroupRole::ROLE_ADMIN) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONADMIN,
                array(
                    'groupId' => $groupObject->id,
                    'userId' => $this->getSession()->getUserId()
                )
            );
            throw new \vc\exception\NotFoundException();
        }

        // Catch the group members before deleting them
        $groupMembers = $groupMemberModel->getFieldList(
            'profile_id',
            array(
                'group_id' => $groupObject->id,
                'confirmed_at IS NOT NULL'
            )
        );

        $groupMembersDeleted = $groupMemberModel->delete(array('group_id' => $groupObject->id));
        $groupRoleDeleted = $groupRoleModel->delete(array('group_id' => $groupObject->id));
        $groupUpdated = $groupModel->update(
            array(
                'id' => $groupObject->id
            ),
            array(
                'deleted_by' => $this->getSession()->getUserId(),
                'deleted_at' => date('Y-m-d H:i:s')
            )
        );

        $cacheModel = $this->getModel('Cache');
        foreach ($groupMembers as $groupMemberId) {
            $cacheModel->resetProfileRelations($groupMemberId);
        }

        if ($groupMembersDeleted && $groupRoleDeleted && $groupUpdated) {
            $subscriptionModel = $this->getDbModel('Subscription');
            $subscriptionModel->unsubscribeAllGroupMembers($groupObject->id);

            $notification = $this->setNotification(
                self::NOTIFICATION_SUCCESS,
                gettext('group.delete.success')
            );
        } else {
            $notification = $this->setNotification(
                self::NOTIFICATION_ERROR,
                gettext('group.delete.failed')
            );
        }
        throw new \vc\exception\RedirectException(
            $this->path . 'groups/?notification=' . $notification
        );
    }
}
