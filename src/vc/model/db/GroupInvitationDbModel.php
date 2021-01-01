<?php
namespace vc\model\db;

class GroupInvitationDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_group_invitation';
    const OBJECT_CLASS = '\\vc\object\\GroupInvitation';

    public function getInvitedUsers($groupId, $createdBy)
    {
        $query = 'SELECT profile_id FROM vc_group_invitation ' .
                 'WHERE group_id = ? AND created_by = ?';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($groupId), intval($createdBy))
        );

        $invited = array();
        $statement->bind_result(
            $profileId
        );
        while ($statement->fetch()) {
            $invited[] = $profileId;
        }
        $statement->close();
        return $invited;
    }

    public function getOpenInvitations($profileId)
    {
        $query = 'SELECT g.hash_id, g.name, p.id, p.nickname, p.real_marker, p.plus_marker, gi.comment, gi.created_at
                  FROM vc_group_invitation gi
                  INNER JOIN vc_group g ON g.id = gi.group_id AND g.deleted_at IS NULL
                  INNER JOIN vc_profile p ON p.id = gi.created_by
                  LEFT JOIN vc_group_member gm ON gm.group_id = gi.group_id AND gm.profile_id = gi.profile_id
                  WHERE gm.created_at IS NULL AND gi.profile_id = ? AND gi.updated_at IS NULL';
        $statement = $this->getDb()->queryPrepared($query, array(intval($profileId)));
        $invitations = array();
        $statement->bind_result(
            $groupHashId,
            $groupName,
            $profileId,
            $profileNickname,
            $profileRealMarker,
            $profilePlusMarker,
            $invitationComment,
            $createdAt
        );
        while ($statement->fetch()) {
            $invitations[] = array(
                'groupHashId' => $groupHashId,
                'groupName' => $groupName,
                'profileId' => $profileId,
                'profileNickname' => $profileNickname,
                'profileRealMarker' => $profileRealMarker,
                'profilePlusMarker' => $profilePlusMarker,
                'comment' => $invitationComment,
                'createdAt' => $createdAt
            );
        }
        $statement->close();
        return $invitations;
    }

    public function getOpenInvitationsForGroup($profileId, $groupId)
    {
        $query = 'SELECT p.id, p.nickname, p.real_marker, p.plus_marker, gi.comment, gi.created_at
                  FROM vc_group_invitation gi
                  INNER JOIN vc_profile p ON p.id = gi.created_by
                  LEFT JOIN vc_group_member gm ON gm.group_id = gi.group_id AND gm.profile_id = gi.profile_id
                  WHERE  gm.created_at IS NULL AND gi.profile_id = ? AND gi.group_id = ? AND gi.updated_at IS NULL';
        $statement = $this->getDb()->queryPrepared($query, array(intval($profileId), intval($groupId)));
        $invitations = array();
        $statement->bind_result(
            $profileId,
            $profileNickname,
            $profileRealMarker,
            $profilePlusMarker,
            $invitationComment,
            $createdAt
        );
        while ($statement->fetch()) {
            $invitations[] = array(
                'profileId' => $profileId,
                'profileNickname' => $profileNickname,
                'profileRealMarker' => $profileRealMarker,
                'profilePlusMarker' => $profilePlusMarker,
                'comment' => $invitationComment,
                'createdAt' => $createdAt
            );
        }
        $statement->close();
        return $invitations;
    }
}
