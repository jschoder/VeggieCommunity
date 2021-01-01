<?php
namespace vc\shell\cron\task\cleanup;

class ArchiveChatMessagesTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'SELECT max(id) FROM ajax_chat_messages WHERE dateTime < DATE_SUB(NOW(),INTERVAL 2 DAY)';

        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $lastMessage = $row[0]; // - 25000;
        $this->setDebugInfo('ClearMessageBefore', $lastMessage);

        if (!empty($lastMessage)) {
            if (!$this->isTestMode()) {
                $this->getDb()->executePrepared(
                    'INSERT INTO ajax_chat_messages_archive SELECT * FROM ajax_chat_messages
                     WHERE ajax_chat_messages.id < ?',
                    array(
                        $lastMessage
                    )
                );
            }

            if (!$this->isTestMode()) {
                $this->getDb()->executePrepared(
                    'DELETE LOW_PRIORITY LOW_PRIORITY FROM ajax_chat_messages WHERE id < ?',
                    array(
                        $lastMessage
                    )
                );
            }
        }
    }
}
