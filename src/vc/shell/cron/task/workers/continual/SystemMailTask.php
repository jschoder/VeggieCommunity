<?php
namespace vc\shell\cron\task\workers\continual;

class SystemMailTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $mailComponent = $this->getComponent('Mail');
        $messages = array();
        $successMessages = array();
        $failedMessages = array();

        $result = $this->getDb()->select(
            "SELECT id, recipient, subject, body, attachments, mail_config " .
            "FROM vc_system_message ORDER BY id ASC LIMIT 200"
        );
        $this->setDebugInfo('systemMessageCount', $result->num_rows);

        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $id = $row[0];
            $recipient = $row[1];
            $subject = $row[2];
            $body = $row[3];
            $attachmentString = $row[4];
            $mailConfig = $row[5];

            $messages[] = array('id' => $id,
                                'recipient' => $recipient,
                                'subject' => $subject);

            if (empty($attachmentString)) {
                $attachments = array();
            } else {
                $attachments = json_decode($attachmentString);
            }

            if (!$this->isTestMode()) {
                $mailSent = $mailComponent->pushMail($recipient, $subject, $body, $attachments, $mailConfig);
                if ($mailSent) {
                    $query3 = "DELETE LOW_PRIORITY FROM vc_system_message WHERE id=" . $id;
                    // :TODO: JOE - deprecated
                    $this->getDb()->delete($query3);

                    $successMessages[] = $id;
                } else {
                    $failedMessages[] = $id;
                }
            }
        }

        $this->setDebugInfo('messages', $messages);
        if (!$this->isTestMode()) {
            $this->setDebugInfo('successMessages', $successMessages);
            $this->setDebugInfo('failedMessages', $failedMessages);
        }
    }
}
