<?php
namespace vc\shell\cron\task\workers\daily;

class OrphanGroupsTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'SELECT DISTINCT
                  vc_group.id, page_admin.id
                  FROM vc_group_member
                  INNER JOIN vc_profile member_profile
                  ON member_profile.id = vc_group_member.profile_id AND member_profile.active > 0
                  INNER JOIN vc_group
                  ON vc_group_member.group_id = vc_group.id
                  INNER JOIN vc_group_role
                  ON vc_group_role.group_id = vc_group_member.group_id AND vc_group_role.role = 2
                  INNER JOIN vc_profile group_admin
                  ON group_admin.id = vc_group_role.profile_id
                  INNER JOIN vc_profile page_admin
                  ON page_admin.admin % 8 > 3
                  WHERE vc_group_member.created_at < DATE_SUB(NOW(),INTERVAL 14 DAY)
                  AND group_admin.last_login < DATE_SUB(NOW(), INTERVAL 30 DAY)
                  AND vc_group_member.confirmed_at IS NULL ';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $groupId,
            $adminId
        );
        $groupAdminList = array();
        while ($statement->fetch()) {
            $groupAdminList[] = array(
                $groupId,
                $adminId
            );
        }
        $statement->close();

        foreach ($groupAdminList as $groupAdmin) {
            if ($this->isTestMode()) {
                echo 'Adding new admin ' . $groupAdmin[1] . ' to  group ' . $groupAdmin[0] . "\n";
            } else {
                $groupMemberModel = $this->getDbModel('GroupMember');
                $groupMemberCount = $groupMemberModel->getCount(
                    array(
                        'group_id' => $groupId,
                        'profile_id' => $adminId
                    )
                );
                if ($groupMemberCount === 0) {
                    $groupMemberObject = new \vc\object\GroupMember();
                    $groupMemberObject->groupId = $groupAdmin[0];
                    $groupMemberObject->profileId = $groupAdmin[1];
                    $groupMemberObject->confirmedBy = $groupAdmin[1];
                    $groupMemberObject->confirmedAt = date('Y-m-d H:i:s');
                    $groupMemberModel->insertObject(null, $groupMemberObject);
                }

                $groupRoleModel = $this->getDbModel('GroupRole');
                $groupRoleCount = $groupRoleModel->getCount(
                    array(
                        'group_id' => $groupId,
                        'profile_id' => $adminId
                    )
                );
                if ($groupRoleCount === 0) {
                    $groupRoleObject = new \vc\object\GroupRole();
                    $groupRoleObject->groupId = $groupAdmin[0];
                    $groupRoleObject->profileId = $groupAdmin[1];
                    $groupRoleObject->role = \vc\object\GroupRole::ROLE_ADMIN;
                    $groupRoleObject->grantedBy = $groupAdmin[1];
                    $groupRoleModel->insertObject(null, $groupRoleObject);
                }
            }
        }
    }
}
