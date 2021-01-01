<?php
namespace vc\model\db;

class ModMessageDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_mod_message';
    const OBJECT_CLASS = '\\vc\object\\ModMessage';

    public function addMessage($userId, $ip, $message)
    {
        $modMessage = new \vc\object\ModMessage();
        $modMessage->userId = $userId;
        $modMessage->ip = $ip;
        $modMessage->message = $message;
        $this->insertObject(null, $modMessage);
    }

    public function getRecentMessages()
    {
        $messages = array();

        $query = 'SELECT DISTINCT vc_mod_message.*, vc_profile.nickname, vc_user_ip_log.profile_id
                  FROM vc_mod_message
                  LEFT JOIN vc_profile ON vc_profile.id = vc_mod_message.user_id
                  LEFT JOIN vc_user_ip_log ON vc_user_ip_log.ip = vc_mod_message.ip AND
                  DATE_SUB(vc_mod_message.created_at, INTERVAL 1 DAY) <= vc_user_ip_log.access AND
                  DATE_ADD(vc_mod_message.created_at, INTERVAL 1 DAY) >= vc_user_ip_log.access
                  WHERE vc_mod_message.created_at > DATE_SUB(NOW(), INTERVAL 72 HOUR)
                  ORDER BY vc_mod_message.id DESC';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($id, $userId, $ip, $message, $createdAt, $userNickname, $loginUserId);
        while ($statement->fetch()) {
            if (!array_key_exists($id, $messages)) {
                $messages[$id] = array(
                    'userId' => $userId,
                    'userNickname' => $userNickname,
                    'message' => $message,
                    'createdAt' => $createdAt,
                    'logins' => array()
                );
            }
            if (!empty($loginUserId)) {
                $messages[$id]['logins'][] = $loginUserId;
            }
        }
        $statement->close();

        return $messages;
    }
}
