<?php
namespace vc\controller\web\pm;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        $mailComponent = $this->getComponent('Mail');
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        // Check status of profile. Block if sending user has been deleted.
        if ($this->autoLogout()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.noactivesession'));
            return;
        }

        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getProfiles($this->locale, array(intval($_POST['contact'])));
        if (count($profiles) == 1) {
            $recipient = $profiles[0];
        } else {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_MESSAGE_TO_DELETED_USER,
                array('Recipient' => $_POST['contact'],
                      'MessageBody' => $_POST['message'])
            );
            echo \vc\view\json\View::renderStatus(false, gettext('pm.userdeleted'));
            return;
        }

        $pmThreadModel = $this->getDbModel('PmThread');
        $contactUserBlock = $pmThreadModel->getContactUserBlock(
            $this->getBlocked(),
            $this->getSession()->getProfile(),
            $recipient
        );
        if (!empty($contactUserBlock)) {
            echo \vc\view\json\View::renderStatus(false, $contactUserBlock);
            return;
        }

        $success = $mailComponent->createPM(
            $this->locale,
            $this->getIp(),
            $recipient,
            empty($_POST['message']) ? '' : $_POST['message'],
            null
        );
        if ($success !== false) {
            $return = array('success' => true,
                            'message' => gettext('compose.sent.success'),
                            'add' => array());
            $pmModel = $this->getDbModel('Pm');
            $pmModel->markAllMessagesRead($this->getSession()->getUserId(), $recipient->id);

            $pmThreadModel = $this->getDbModel('PmThread');
            $pmThreadModel->updateThread($this->getSession()->getUserId(), $recipient->id, false, true);

            if (array_key_exists('maxThread', $_POST)) {
                $threads = $pmThreadModel->getThreads(null, null, $_POST['maxThread']);
                foreach ($threads as $i => $thread) {
                    $threads[$i]['lastMessage'] = prepareHTML($thread['lastMessage']);
                }
                $return['add']['threads'] = $threads;
            }
            if (array_key_exists('maxMessage', $_POST)) {
                $textFilter = array();
                $fromFilter = null;
                $toFilter = null;
                if (!empty($_POST['f']) && $this->getSession()->getPlusLevel() >= \vc\object\Plus::PLUS_TYPE_STANDARD) {
                    if (!empty($_POST['f']['text'])) {
                        $textFilter = explode(' ', $_POST['f']['text']);
                    }
                    if (!empty($_GET['f']['from'])) {
                        $fromFilter = $_POST['f']['from'];
                    }
                    if (!empty($_GET['f']['to'])) {
                        $toFilter = $_POST['f']['to'];
                    }
                }
                $messages = $pmModel->getMessages(
                    $this->getSession()->getUserId(),
                    $recipient->id,
                    $this->getSession()->getSetting(\vc\object\Settings::PM_FILTER_INCOMING),
                    null,
                    null,
                    $_POST['maxMessage'],
                    $textFilter,
                    $fromFilter,
                    $toFilter
                );
                foreach ($messages as $i => $message) {
                    $messages[$i]['subject'] = prepareHTML($message['subject']);
                    $messages[$i]['body'] = prepareHTML($message['body'], true);
                }
                $return['add']['messages'] = $messages;
            }
            echo \vc\view\json\View::render($return);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('compose.sent.failed'));
        }
    }
}
