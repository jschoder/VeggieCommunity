<?php
namespace vc\model\db;

class AjaxChatMessagesDbModel extends AbstractDbModel
{
    public function getMessages($channel, $userId)
    {
        $queryParams = array();
        if (!empty($userId)) {
            $query = 'SELECT DISTINCT ajax_chat_messages.id, ajax_chat_messages.userID, ajax_chat_messages.userName,
                                      ajax_chat_messages.channel, ajax_chat_messages.dateTime, ajax_chat_messages.text
                      FROM ajax_chat_messages
                      INNER JOIN ajax_chat_messages user_messages
                      ON user_messages.channel = ajax_chat_messages.channel AND user_messages.userID = ?
                      WHERE
                      ajax_chat_messages.dateTime >= DATE_SUB(user_messages.dateTime, INTERVAL 10 MINUTE) AND
                      ajax_chat_messages.dateTime <= DATE_ADD(user_messages.dateTime, INTERVAL 10 MINUTE)
                      ORDER BY ajax_chat_messages.id ASC';
            $queryParams[] = $userId;
        } else {
            $queryWhere = '';
            if (!empty($channel)) {
                $queryWhere .= ' WHERE channel = ?';
                $queryParams[] = $channel;
                $queryParams[] = $channel;
            }
            $query = '(SELECT id, userID, userName, channel, dateTime, text FROM ajax_chat_messages' . $queryWhere . ')
                      UNION
                      (SELECT id, userID, userName, channel, dateTime, text FROM ajax_chat_messages_archive' . $queryWhere . ' ORDER BY id DESC LIMIT 10000)
                      ORDER BY id ASC';
        }
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $id,
            $userId,
            $userName,
            $channel,
            $dateTime,
            $text
        );

        $messages = array();
        while ($statement->fetch()) {
            $messages[] = array(
                'dateTime' => strtotime($dateTime),
                'channel' => $channel,
                'userId' => $userId,
                'userName' => $userName,
                'text' => $text
            );
        }
        $statement->close();

        return $messages;
    }
}


