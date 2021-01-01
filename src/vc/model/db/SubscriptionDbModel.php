<?php
namespace vc\model\db;

class SubscriptionDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_subscription';
    const OBJECT_CLASS = '\\vc\object\\Subscription';

    public function add($profileId, $entityType, $entityId)
    {
        $query = 'INSERT INTO vc_subscription SET ' .
                 'profile_id = ?, entity_type = ?, entity_id = ? ' .
                 'ON DUPLICATE KEY UPDATE entity_id = entity_id';

        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('iii', $profileId, $entityType, $entityId);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while adding group subscription: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('profileId' => $profileId,
                      'entityType' => $entityType,
                      'entityId' => $entityId)
            );
            return false;
        }

        return !$this->getDb()->hasError();
    }

    public function deleteSubscription($profileId, $entityType, $entityId)
    {
        $query = 'DELETE FROM vc_subscription WHERE profile_id = ? AND entity_type = ? AND entity_id = ? LIMIT 1';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('iii', $profileId, $entityType, $entityId);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while deleting group subscription: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('profileId' => $profileId,
                      'entityType' => $entityType,
                      'entityId' => $entityId)
            );
            return false;
        }
        $statement->store_result();
        $statement->close();
        return !$this->getDb()->hasError();
    }

    public function getSubscriptions($profileId, $entityType)
    {
        $query = 'SELECT entity_id
                  FROM vc_subscription
                  WHERE profile_id = ? AND entity_type = ?';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($profileId), intval($entityType))
        );

        $entityIds = array();
        $statement->bind_result($entityId);
        while ($statement->fetch()) {
            $entityIds[] = $entityId;
        }
        $statement->close();

        return $entityIds;
    }

    public function subscribeToAllGroupForums($groupId, $userId)
    {
        $this->getDb()->executePrepared(
            'INSERT INTO vc_subscription (profile_id, entity_type, entity_id)
            SELECT ? as profile_id, ' . \vc\config\EntityTypes::GROUP_FORUM . ' as entity_type, vc_group_forum.id
            FROM vc_group
            INNER JOIN vc_group_forum ON vc_group_forum.group_id = vc_group.id AND vc_group_forum.deleted_at IS NULL
            WHERE vc_group.id = ?',
            array(
                $groupId,
                $userId
            )
        );
    }

    public function unsubscribeAllGroupForums($groupId, $userId)
    {
        $this->getDb()->executePrepared(
            'DELETE vc_subscription FROM vc_subscription
            INNER JOIN vc_group_forum
            ON vc_subscription.entity_type = ' . \vc\config\EntityTypes::GROUP_FORUM . '
            AND vc_group_forum.id = vc_subscription.entity_id
            WHERE vc_group_forum.group_id = ? AND vc_subscription.profile_id = ?',
            array(
                $groupId,
                $userId
            )
        );
    }

    public function subscribeAllGroupMembers($groupId, $forumId)
    {
        $this->getDb()->executePrepared(
            'INSERT INTO vc_subscription (profile_id, entity_type, entity_id)
            SELECT
                vc_group_member.profile_id,
                ' . \vc\config\EntityTypes::GROUP_FORUM . ' as entity_type,
                vc_group_forum.id
            FROM vc_group_member
            INNER JOIN vc_group_forum
            ON vc_group_forum.group_id = vc_group_member.group_id AND vc_group_forum.deleted_at IS NULL
            WHERE vc_group_member.group_id = ? AND vc_group_forum.id = ?',
            array(
                $groupId,
                $forumId
            )
        );
    }

    public function unsubscribeAllGroupMembers($groupId, $forumId = null)
    {
        $query = 'DELETE vc_subscription FROM vc_subscription
                 INNER JOIN vc_group_forum
                 ON vc_subscription.entity_type = ' . \vc\config\EntityTypes::GROUP_FORUM . '
                 AND vc_group_forum.id = vc_subscription.entity_id
                 WHERE vc_group_forum.group_id = ?';
        $queryParams = array($groupId);

        if (!empty($forumId)) {
            $query .= ' AND vc_group_forum.id = ?';
            $queryParams[] = $forumId;
        }
        $this->getDb()->executePrepared($query, $queryParams);
    }
}
