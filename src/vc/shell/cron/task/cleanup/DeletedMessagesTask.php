<?php
namespace vc\shell\cron\task\cleanup;

class DeletedMessagesTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $queryPostfix = 'WHERE senderstatus > 0 AND ' .
                        'senderid IN (SELECT id FROM vc_profile WHERE delete_date IS NOT NULL AND ' .
                        'delete_date < DATE_SUB(NOW(), INTERVAL 30 DAY) AND active < 0 AND active != -21)';
        $query = 'SELECT count(*) FROM vc_message ' . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('updatedMessagesDeletedProfiles.Sender', $row[0]);

        $query2 = 'UPDATE vc_message SET senderstatus=' . \vc\object\Mail::SENDER_STATUS_PROFILE_DELETED .
                  ' ' . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->update($query2);
        }

        $queryPostfix = 'WHERE recipientstatus >= 0 AND ' .
                        'recipientid IN (SELECT id FROM vc_profile WHERE delete_date IS NOT NULL AND ' .
                        'delete_date < DATE_SUB(NOW(), INTERVAL 30 DAY) AND active < 0 AND active != -21)';
        $query = 'SELECT count(*) FROM vc_message ' . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('updatedMessagesDeletedProfiles.Recipient', $row[0]);

        $query2 = 'UPDATE LOW_PRIORITY vc_message SET recipientstatus=' .
                  \vc\object\Mail::RECIPIENT_STATUS_PROFILE_DELETED .
                  ', recipient_delete_date = DATE_SUB(NOW(), INTERVAL 30 DAY)' . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->update($query2);
        }

        $queryPostfix = ' WHERE ' .
                        'senderstatus < 0 AND ' .
                        'recipient_delete_date IS NOT NULL AND ' .
                        'recipient_delete_date < DATE_SUB(NOW(), INTERVAL 29 DAY)';
        $query = 'SELECT count(*) FROM vc_message ' . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('deleted', $row[0]);

        $query2 = 'DELETE LOW_PRIORITY  FROM vc_message ' . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query2);
        }
    }
}
