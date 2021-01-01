<?php
namespace vc\model\db;

class GroupNotificationDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_group_notification';
    const OBJECT_CLASS = '\\vc\object\\GroupNotification';

    public function addBySubscription($entityType, $entityId, $userId)
    {
        $query = 'INSERT LOW_PRIORITY INTO vc_group_notification
                  (profile_id, notification_Type, entity_id, user_id, last_update)
                  SELECT profile_id, CASE
                  entity_type ' .
                     ' WHEN ' . \vc\config\EntityTypes::GROUP_FORUM .
                     ' THEN ' . \vc\object\GroupNotification::TYPE_FORUM_NEW_THREAD .
                     ' WHEN ' . \vc\config\EntityTypes::FORUM_THREAD .
                     ' THEN ' . \vc\object\GroupNotification::TYPE_FORUM_NEW_COMMENT .
                     ' WHEN ' . \vc\config\EntityTypes::GROUP_MEMBER_REQUEST .
                     ' THEN ' . \vc\object\GroupNotification::TYPE_GROUP_MEMBER_REQUESTS .
                     ' WHEN ' . \vc\config\EntityTypes::GROUP_INVITATION .
                     ' THEN ' . \vc\object\GroupNotification::TYPE_GROUP_INVITATION .
                     ' ELSE NULL
                  END as notification_type, entity_id, ? AS user_id, now()
                  FROM vc_subscription WHERE entity_type = ? AND entity_id = ? AND profile_id != ?
                  ON DUPLICATE KEY UPDATE user_id = ?, last_update = NOW(), seen_at = NULL';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('iiiii', $userId, $entityType, $entityId, $userId, $userId);
        $executed = $statement->execute();
        $statement->close();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while adding group notification: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array(
                    'entityType' => $entityType,
                    'entityId' => $entityId,
                    'userId' => $userId
                )
            );
            return false;
        }

        // Update websocket notification
        $query = 'INSERT LOW_PRIORITY INTO vc_websocket_message (context_type, context_id)
                  SELECT DISTINCT ' . \vc\config\EntityTypes::STATUS . ' as context_type, profile_id as context_id
                  FROM vc_subscription WHERE entity_type = ? AND entity_id = ? AND profile_id != ?
                  ON DUPLICATE KEY UPDATE vc_websocket_message.context_id = vc_websocket_message.context_id';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('iii', $entityType, $entityId, $userId);
        $executed = $statement->execute();
        $statement->close();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while adding websocket message: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('entityType' => $entityType,
                      'entityId' => $entityId)
            );
        }

        return true;
    }

    public function add($profileId, $notificationType, $entityId, $userId)
    {
        $query = 'INSERT INTO vc_group_notification SET
                  profile_id = ?, notification_type = ?, entity_id = ?, user_id = ?, last_update = NOW()
                  ON DUPLICATE KEY UPDATE user_id = ?, seen_at = NULL, last_update = NOW()';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('iiiii', $profileId, $notificationType, $entityId, $userId, $userId);
        $executed = $statement->execute();
        $statement->close();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while adding group notification: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('profileId' => $profileId,
                      'notificationType' => $notificationType,
                      'entityId' => $entityId,
                      'userId' => $userId)
            );
            return false;
        }

        $websocketMessageModel = $this->getDbModel('WebsocketMessage');
        $websocketMessageModel->trigger(\vc\config\EntityTypes::STATUS, $profileId);

        return true;
    }

    public function deleteNotification($profileId, $notificationType = null, $entityId = null)
    {
        if ($notificationType === null) {
            $query = 'DELETE FROM vc_group_notification WHERE profile_id = ?';
            $statement = $this->getDb()->prepare($query);
            $statement->bind_param('i', $profileId);
        } else {
            $query = 'DELETE FROM vc_group_notification ' .
                     'WHERE profile_id = ? AND notification_type = ? AND entity_id = ? LIMIT 1';
            $statement = $this->getDb()->prepare($query);
            $statement->bind_param('iii', $profileId, $notificationType, $entityId);
        }
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while deleting group notification: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('profileId' => $profileId,
                      'notificationType' => $notificationType,
                      'entityId' => $entityId)
            );
            return false;
        }
        $statement->store_result();
        $statement->close();
        return !$this->getDb()->hasError();
    }

    public function getNotificationsCount($profileId)
    {
        $query = 'SELECT count(*) FROM vc_group_notification WHERE profile_id = ? AND seen_at IS NULL';
        $queryParams = array($profileId);
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result($notificationCount);
        $statement->fetch();
        $statement->close();
        return $notificationCount;
    }
}
