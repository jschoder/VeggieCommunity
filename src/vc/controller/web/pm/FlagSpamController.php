<?php
namespace vc\controller\web\pm;

class FlagSpamController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $mails = $_POST["mails"];
        \vc\lib\Assert::assertLongArray("mails", $mails, 1, 2147483647, true);
        $mails = array_map('intval', $mails);

        if (empty($mails)) {
            $success = false;
        } else {
            $pmModel = $this->getDbModel('Pm');
            $success = $pmModel->update(
                array(
                    'id' => $mails,
                    'recipientid' => $this->getSession()->getUserId()
                ),
                array(
                    'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_SPAM_SUSPECT
                ),
                false
            );
        }

        $pmThreadModel = $this->getDbModel('PmThread');
        $pmThreadModel->updateThreadByMessageIds($mails);

        if ($success) {
            // No message needed the visual is feedback enough
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.spam.failed'));
        }
    }
}
