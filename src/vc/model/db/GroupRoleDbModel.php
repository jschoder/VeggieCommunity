<?php
namespace vc\model\db;

class GroupRoleDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_group_role';
    const OBJECT_CLASS = '\\vc\object\\GroupRole';

    public function getRole($groupId, $profileId)
    {
        if (empty($groupId) || empty($profileId)) {
            return false;
        }

        $query = 'SELECT role FROM vc_group_role WHERE group_id = ? AND profile_id = ? LIMIT 1';
        $statement = $this->getDb()->queryPrepared($query, array(intval($groupId), intval($profileId)));
        $statement->store_result();

        if ($statement->num_rows == 0) {
            $statement->close();
            return null;
        } else {
            $statement->bind_result($role);
            $statement->fetch();
            $statement->close();
            return $role;
        }
    }

    public function getGroupRoles($groupId)
    {
        $groupRoles = array(
            \vc\object\GroupRole::ROLE_MODERATOR => array(),
            \vc\object\GroupRole::ROLE_ADMIN => array(),
        );

        if (empty($groupId)) {
            return $groupRoles;
        }

        $query = 'SELECT role, profile_id FROM vc_group_role WHERE group_id = ?';
        $statement = $this->getDb()->queryPrepared($query, array(intval($groupId)));
        $statement->bind_result($role, $profileId);
        while ($statement->fetch()) {
            $groupRoles[$role][] = $profileId;
        }
        $statement->close();

        return $groupRoles;
    }

    public function deleteRole($groupId, $profileId)
    {
        $query = 'DELETE FROM vc_group_role WHERE group_id = ? AND profile_id = ?';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('ii', $groupId, $profileId);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while deleting group role: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('groupId' => $groupId,
                      'profileId' => $profileId)
            );
            return false;
        }
        $statement->store_result();
        $affectedRows = $this->getDb()->getAffectedRows();
        $statement->close();
        return $affectedRows == 1;
    }

    public function fixGroupAdminsIfRequired($groupId, $currentUser)
    {
        $currentGroupRoles = $this->getGroupRoles($groupId);
        if (empty($currentGroupRoles[\vc\object\GroupRole::ROLE_ADMIN])) {
            // No admin left in the group. Fix this
            if (!empty($currentGroupRoles[\vc\object\GroupRole::ROLE_MODERATOR])) {
                // The group has still moderators. Simply make them admin
                $this->update(
                    array('group_id' => $groupId),
                    array('role' => \vc\object\GroupRole::ROLE_ADMIN),
                    false
                );
            } else {
                // The group has neither moderators nor admins. Make the side moderators the new admins.
                $query = 'SELECT id FROM vc_profile WHERE admin % 8 > 3';
                $statement = $this->getDb()->queryPrepared($query);
                $statement->bind_result($profileId);
                $profileIds = array();
                while ($statement->fetch()) {
                    $profileIds[] = $profileId;
                }
                $statement->close();

                foreach ($profileIds as $profileId) {
                    $groupMemberModel = $this->getDbModel('GroupMember');
                    $groupMemberCount = $groupMemberModel->getCount(
                        array(
                            'group_id' => $groupId,
                            'profile_id' => $profileId
                        )
                    );
                    if ($groupMemberCount === 0) {
                        $groupMemberObject = new \vc\object\GroupMember();
                        $groupMemberObject->groupId = $groupId;
                        $groupMemberObject->profileId = $profileId;
                        $groupMemberObject->confirmedBy = $profileId;
                        $groupMemberObject->confirmedAt = date('Y-m-d H:i:s');
                        $groupMemberModel->insertObject($currentUser, $groupMemberObject);
                    }

                    $groupRoleCount = $this->getCount(
                        array(
                            'group_id' => $groupId,
                            'profile_id' => $profileId
                        )
                    );
                    if ($groupRoleCount === 0) {
                        $groupRoleObject = new \vc\object\GroupRole();
                        $groupRoleObject->groupId = $groupId;
                        $groupRoleObject->profileId = $profileId;
                        $groupRoleObject->role = \vc\object\GroupRole::ROLE_ADMIN;
                        $groupRoleObject->grantedBy = $profileId;
                        $this->insertObject($currentUser, $groupRoleObject);
                    }
                }
            }
        }
    }
}
