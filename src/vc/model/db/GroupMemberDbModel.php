<?php
namespace vc\model\db;

class GroupMemberDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_group_member';
    const OBJECT_CLASS = '\\vc\object\\GroupMember';

    public function getMemberCount($groupId)
    {
        $query = 'SELECT count(profile_id) FROM vc_group_member WHERE group_id = ? AND confirmed_at IS NOT NULL';
        $statement = $this->getDb()->queryPrepared($query, array(intval($groupId)));
        $statement->bind_result($memberCount);
        $statement->fetch();
        $statement->close();
        return $memberCount;
    }

    public function isMember($groupId, $userId)
    {
        if (empty($groupId) || empty($userId)) {
            return false;
        }

        $query = 'SELECT confirmed_at FROM vc_group_member WHERE group_id = ? AND profile_id = ? LIMIT 1';
        $statement = $this->getDb()->queryPrepared($query, array(intval($groupId), intval($userId)));
        $statement->store_result();

        if ($statement->num_rows == 0) {
            $statement->close();
            return false;
        } else {
            $statement->bind_result($confirmedAt);
            $statement->fetch();
            $statement->close();
            return $confirmedAt;
        }
    }

    public function getUnconfirmedMembersByGroup($groupId)
    {
        $unconfirmedMemberIds = array();
        $query = 'SELECT profile_id FROM vc_group_member
                  WHERE group_id = ? AND confirmed_at IS NULL';
        $statement = $this->getDb()->queryPrepared($query, array(intval($groupId)));
        $statement->bind_result($profileId);
        while ($statement->fetch()) {
            $unconfirmedMemberIds[] = $profileId;
        }
        $statement->close();

        return $unconfirmedMemberIds;
    }

    public function getUnconfirmedMembersByModerator($profileId)
    {
        $unconfirmedMembers = array();
        $query = 'SELECT
                  g.hash_id, g.name,
                  p.id, p.nickname, p.real_marker, p.plus_marker,
                  gm.created_at
                  FROM vc_group_role gr
                  INNER JOIN vc_group g ON g.id = gr.group_id
                  INNER JOIN vc_group_member gm ON gm.group_id = gr.group_id AND gm.confirmed_at IS NULL
                  INNER JOIN vc_profile p ON p.id = gm.profile_id
                  WHERE gr.profile_id = ?
                  ORDER BY g.name, p.nickname';
        $statement = $this->getDb()->queryPrepared($query, array(intval($profileId)));
        $statement->bind_result(
            $groupHashId,
            $groupName,
            $profileId,
            $profileNickname,
            $profileRealMarker,
            $profilePlusMarker,
            $groupMemberCreatedAt
        );
        while ($statement->fetch()) {
            $unconfirmedMembers[] = array(
                'groupHashId' => $groupHashId,
                'groupName' => $groupName,
                'profileId' => $profileId,
                'profileNickname' => $profileNickname,
                'profileRealMarker' => $profileRealMarker,
                'profilePlusMarker' => $profilePlusMarker,
                'groupMemberCreatedAt' => strtotime($groupMemberCreatedAt)
            );
        }
        $statement->close();

        return $unconfirmedMembers;
    }

    public function accept($groupId, $profileId, $confirmedBy)
    {
        return $this->update(
            array(
                'group_id' => $groupId,
                'profile_id' => $profileId
            ),
            array(
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => date('Y-m-d H:i:s')
            )
        );
    }

    public function deny($groupId, $profileId)
    {
        return $this->deleteMember($groupId, $profileId);
    }

    public function deleteMember($groupId, $profileId)
    {
        $groupId = intval($groupId);
        $profileId = intval($profileId);

        $query = 'DELETE FROM vc_group_member WHERE group_id = ? AND profile_id = ? LIMIT 1';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('ii', $groupId, $profileId);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while deleting group member: ' . $statement->errno . ' / ' . $statement->error,
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

        if ($affectedRows == 1) {
            // Delete Forum-Subscriptions
            $query = 'DELETE vc_subscription
                      FROM vc_subscription
                      INNER JOIN vc_group_forum gf ON gf.id = vc_subscription.entity_id
                      WHERE vc_subscription.entity_type = 20 AND gf.group_id = ? AND vc_subscription.profile_id = ?';
            $this->getDb()->executePrepared($query, array($groupId, $profileId));

            // Delete ForumThread-Subscriptions
            $query = 'DELETE vc_subscription
                      FROM vc_subscription
                      INNER JOIN vc_forum_thread ft ON ft.id = vc_subscription.entity_id
                      INNER JOIN vc_group_forum gf ON ft.context_type = 20 AND gf.id = ft.context_id
                      WHERE vc_subscription.entity_type = 21 AND gf.group_id = ? AND vc_subscription.profile_id = ?';
            $this->getDb()->executePrepared($query, array($groupId, $profileId));
            return true;
        } else {
            return false;
        }
    }

    public function getMemberIds($groupId)
    {
        $profileIds = array();
        $query = 'SELECT profile_id FROM vc_group_member WHERE group_id = ?';
        $statement = $this->getDb()->queryPrepared($query, array(intval($groupId)));
        $statement->bind_result(
            $profileId
        );
        while ($statement->fetch()) {
            $profileIds[] = $profileId;
        }
        $statement->close();

        return $profileIds;
    }
}
