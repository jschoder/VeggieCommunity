<?php
namespace vc\shell\cron\task\workers\continual;

class AutoSendMailsTask extends \vc\shell\cron\task\AbstractCronTask
{
    const MINIMUM_AGE_HOURS = 4;

    public function execute()
    {
        $mailComponent = $this->getComponent('Mail');

        $pmModel = $this->getDbModel('Pm');
        $pmThreadModel = $this->getDbModel('PmThread');

        $unsentMessages = $pmModel->getAllUnsentMessages();

        $groupedMessages = array();
        foreach ($unsentMessages as $message) {
            $senderId = $message['sender']['id'];
            if (!array_key_exists($senderId, $groupedMessages)) {
                $groupedMessages[$senderId] = array();
            }

            $length = strlen($message['subject'] . $message['body']);
            if (!array_key_exists($length, $groupedMessages[$senderId])) {
                $groupedMessages[$senderId][$length] = 1;
            } else {
                $groupedMessages[$senderId][$length] = $groupedMessages[$senderId][$length] + 1;
            }
        }

        $sent = 0;
        $skipped = 0;
        $blocked = 0;

        foreach ($unsentMessages as $message) {
            $senderId = $message['sender']['id'];
            $length = strlen($message['subject'] . $message['body']);
            $multipleSameMessage = $groupedMessages[$senderId][$length] > 1;
            $youngMessage = (time() - strtotime($message['created'])) < (self::MINIMUM_AGE_HOURS * 3600);
            $spam = $pmModel->containsSpamBlacklisting($message['subject']) ||
                    $pmModel->containsSpamBlacklisting($message['body']);
            $maybeSpam = $pmModel->mayContainSpamPotential($message['subject']) ||
                         $pmModel->mayContainSpamPotential($message['body']);
            $autoSendCountry = in_array($message['sender']['ip_iso'], array('DE', 'AT', 'CH'));
            $hardBlacklist = $pmModel->isUserHardBlacklisted(
                null,
                $message['sender']['id'],
                $message['body'],
                $message['recipient']['id']
            );

            if ($this->isTestMode()) {
                echo $message['id'] . ' => ' .
                     $message['sender']['ip_iso'] . ' / ' .
                     ($multipleSameMessage ? 'true' : 'false') . ' / ' .
                     ($youngMessage ? 'true' : 'false') . ' / ' .
                     ($spam ? 'true' : 'false') . ' / ' .
                     ($maybeSpam ? 'true' : 'false') . ' / ' .
                     ($hardBlacklist ? 'true' : 'false') . "\n";

                if ($multipleSameMessage ||
                    $youngMessage ||
                    $spam ||
                    $maybeSpam ||
                    !$autoSendCountry ||
                    $hardBlacklist
                ) {
                    $blocked++;
                } else {
                    if ($message['notification'] == 1) {
                        $sent++;
                    } else {
                        $skipped++;
                    }
                }
            } else {
                if ($multipleSameMessage ||
                    $youngMessage ||
                    $spam ||
                    $maybeSpam ||
                    !$autoSendCountry ||
                    $hardBlacklist
                ) {
                    $blocked++;
                } else {
                    if ($message['notification'] == 1) {
                        $this->getComponent('I14n')->loadLocale($message['language']);
                        $mailSent = $mailComponent->sendMailToUser(
                            $message['language'],
                            intval($message['recipient']['id']),
                            'compose.mail.subject',
                            'message',
                            array(
                                'userId' => $message['sender']['id'],
                                'username' => $message['sender']['nickname'],
                                'userLink' => 'user/view/' . $message['sender']['id'] . '/',
                                'pmLink' => 'pm/#' . $message['sender']['id'],
                                'message' => $message['body']
                            ),
                            array(
                                'user-' . $message['sender']['id'] => array('user', $message['sender']['id'])
                            ),
                            \vc\object\SystemMessage::MAIL_CONFIG_NOTIFY,
                            true,
                            false
                        );
                        if ($mailSent) {
                            $pmModel->setMessageSent($message['id']);
                            $pmThreadModel->updateThread(
                                $message['sender']['id'],
                                $message['recipient']['id'],
                                null,
                                true
                            );
                        }
                        $sent++;
                    } else {
                        $pmModel->setMessageSent($message['id']);
                        $pmThreadModel->updateThread(
                            $message['sender']['id'],
                            $message['recipient']['id'],
                            null,
                            true
                        );
                        $skipped++;
                    }
                }
            }
        }
        $this->setDebugInfo('sent', $sent);
        $this->setDebugInfo('skipped', $skipped);
        $this->setDebugInfo('blocked', $blocked);
        if ($this->isTestMode()) {
            echo 'SENT: ' . $sent . "\n";
            echo 'SKIPPED: ' . $skipped . "\n";
            echo 'BLOCKED: ' . $blocked . "\n";
        }
    }
}
