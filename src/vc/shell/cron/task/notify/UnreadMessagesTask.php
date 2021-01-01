<?php
namespace vc\shell\cron\task\notify;

class UnreadMessagesTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'SELECT DISTINCT
                      sender.id, sender.nickname,
                      recipient.id, recipient.nickname, recipient.email, vc_setting.value
                  FROM vc_message
                  INNER JOIN vc_profile sender ON sender.id = vc_message.senderid
                  INNER JOIN vc_profile recipient ON recipient.id = vc_message.recipientid
                  INNER JOIN vc_setting
                  ON vc_setting.profileid = recipient.id
                  AND vc_setting.field = ' . \vc\object\Settings::USER_LANGUAGE . '
                  WHERE
                     vc_message.recipientstatus = 1 AND
                     recipient.active > 0 AND
                     vc_message.created > recipient.last_login AND
                     recipient.last_login
                     < DATE_SUB(NOW(), INTERVAL ' . \vc\config\Globals::UNREAD_MESSAGE_REMINDER_LOGIN_DAYS . ' DAY) AND
                     vc_message.created
                     < DATE_SUB(NOW(), INTERVAL ' . \vc\config\Globals::UNREAD_MESSAGE_REMINDER_MESSAGE_AGE . ' DAY) AND
                     MOD(
                         DATEDIFF(now(), recipient.last_login),
                         ' . \vc\config\Globals::UNREAD_MESSAGE_REMINDER_INTERVAL . '
                     )  = 0';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $senderId,
            $senderNickname,
            $recipientId,
            $recipientNickname,
            $recipientEmail,
            $recipientLanguage
        );
        $messages = array();
        while ($statement->fetch()) {
            if (!array_key_exists($recipientId, $messages)) {
                $messages[$recipientId] = array(
                    'nickname' => $recipientNickname,
                    'email' => $recipientEmail,
                    'locale' => $recipientLanguage,
                    'sender' => array(),
                    'attachments' => array()
                );
            }
            $messages[$recipientId]['sender'][$senderId] = $senderNickname;
            $messages[$recipientId]['attachments']['user-' . $senderId] = array('user', $senderId);

        }
        $statement->close();

        $this->setDebugInfo('messages', $messages);

        $i14nComponent = $this->getComponent('I14n');
        $mailComponent = $this->getComponent('Mail');
        if ($this->isTestMode()) {
            var_export($messages);
        } else {
            foreach ($messages as $message) {
                $i14nComponent->loadLocale($message['locale']);
                $mailComponent->sendMail(
                    $message['nickname'],
                    $message['email'],
                    'pm.reminder.title',
                    $message['locale'],
                    'message-reminder',
                    array('sender' => $message['sender']),
                    $message['attachments']
                );
            }
        }
    }
}
