<?php
namespace vc\shell\cron\task\cleanup;

class ChatArchiveTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'DELETE LOW_PRIORITY FROM ajax_chat_messages_archive WHERE dateTime < DATE_SUB(NOW(), INTERVAL 6 MONTH)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }
    }
}
