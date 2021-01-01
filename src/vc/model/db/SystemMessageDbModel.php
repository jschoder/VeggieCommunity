<?php
namespace vc\model\db;

class SystemMessageDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_system_message';
    const OBJECT_CLASS = '\\vc\object\\SystemMessage';

    public function add($recipientEmail, $subject, $body, $attachments = array(), $mailConfig = \vc\object\SystemMessage::MAIL_CONFIG_NOTIFY)
    {
        $systemMessage = new \vc\object\SystemMessage();
        $systemMessage->recipient = $recipientEmail;
        $systemMessage->subject = $subject;
        $systemMessage->body = $body;
        if (!empty($attachments)) {
            $systemMessage->attachments = json_encode($attachments);
        }

        $systemMessage->mailConfig = intval($mailConfig);

        $objectSaved = $this->insertObject(null, $systemMessage);
        return $objectSaved !== false;
    }

    public function informModerators($subject, $body)
    {
        $query = 'SELECT email FROM vc_profile WHERE admin % 8 > 3';
        $result = $this->getDb()->select($query);
        while ($row = $result->fetch_row()) {
            $systemMessageModel = $this->getDbModel('SystemMessage');
            $systemMessageModel->add(
                $row[0],
                '[MOD] ' . $subject,
                $body
            );
        }
        $result->free();
    }
}
