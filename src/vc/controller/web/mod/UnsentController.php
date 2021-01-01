<?php
namespace vc\controller\web\mod;

class UnsentController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

//        $this->loadHashes();

        $pmModel = $this->getDbModel('Pm');
        $this->view($pmModel);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        $mailComponent = $this->getComponent('Mail');

        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }
        $pmModel = $this->getDbModel('Pm');
        $pmThreadModel = $this->getDbModel('PmThread');

        $unsentMessages = $pmModel->getAllUnsentMessages();

        $sent = 0;
        $skipped = 0;

        foreach ($unsentMessages as $message) {
            if ($message['notification'] == 1) {
                $mailSent = $mailComponent->sendMailToUser(
                    $this->locale,
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
                    false,
                    false
                );

                if ($mailSent) {
                    $pmModel->setMessageSent($message['id']);
                    $pmThreadModel->updateThread($message['sender']['id'], $message['recipient']['id'], null, true);
                }
                $sent++;
            } else {
                $pmModel->setMessageSent($message['id']);
                $pmThreadModel->updateThread($message['sender']['id'], $message['recipient']['id'], null, true);
                $skipped++;
            }
        }

        $this->getComponent('I14n')->loadLocale($this->locale);

        $this->getView()->set('sent', $sent);
        $this->getView()->set('skipped', $skipped);
        $this->view($pmModel);
    }

    private function view($pmModel)
    {
        $unsentMessages = $pmModel->getAllUnsentMessages();

        $groupedMessages = array();
        foreach ($unsentMessages as $message) {
            $senderId = $message['sender']['id'];
            $recipientId = $message['recipient']['id'];
            $subject = $message['subject'];
            $body = $message['body'];
            $sha = sha1($senderId . '###' . $subject . '###' . $body);

            if (array_key_exists($sha, $groupedMessages)) {
                $groupedMessages[$sha]['count'] = $groupedMessages[$sha]['count'] + 1;
            } else {
                $filter = array();
                if ($pmModel->containsSpamBlacklisting($subject)) {
                    $filter[] = 'spam.subject';
                }
                if ($pmModel->containsSpamBlacklisting($body)) {
                    $filter[] = 'spam.body';
                }
                if ($pmModel->mayContainSpamPotential($subject)) {
                    $filter[] = 'maybeSpam.subject';
                }
                if ($pmModel->mayContainSpamPotential($body)) {
                    $filter[] = 'maybeSpam.body';
                }
                if ($pmModel->isTextBlacklisted($body)) {
                    $filter[] = 'hardBlacklist.textBlacklisted';
                }
                if ($pmModel->isSuspicionBlock($this->getIp(), $senderId)) {
                    $filter[] = 'hardBlacklist.suspicionBlock';
                }
                if ($pmModel->hasBannedCountryLogin($senderId)) {
                    $filter[] = 'hardBlacklist.bannedCountryLogin';
                }

                $groupedMessages[$sha] = array(
                    'sender' => $message['sender'],
                    'length' => strlen($message['body']),
                    'count' => 1,
                    'filter' => $filter
                );
            }
        }

        $this->setTitle('Unsent messages');

        $this->getView()->set('groupedMessages', $groupedMessages);
        echo $this->getView()->render('mod/unsent', true);
    }
}
