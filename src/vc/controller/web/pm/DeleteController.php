<?php
namespace vc\controller\web\pm;

class DeleteController extends \vc\controller\web\AbstractWebController
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

        if (array_key_exists('mails', $_POST) && count($_POST["mails"]) > 0) {
            $mails = $_POST["mails"];
            \vc\lib\Assert::assertLongArray("mails", $mails, 1, 2147483647, false);
            $mails = array_map('intval', $mails);

            $pmModel = $this->getDbModel('Pm');

            // :TODO: Get rid of this option when removing the old messaging system
            if (array_key_exists('group', $_POST)) {
                $group = $_POST["group"];
                \vc\lib\Assert::assertValueInArray("group", $group, array("inbox", "outbox"), false);

                if ($group == "inbox") {
                    $success = $pmModel->update(
                        array(
                            'id' => $mails,
                            'recipientid' => $this->getSession()->getUserId()
                        ),
                        array(
                            'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_DELETED,
                            'recipient_delete_date' => date('Y-m-d H:i:s')
                        ),
                        false
                    );
                } elseif ($group == "outbox") {
                    $success = $pmModel->update(
                        array(
                            'id' => $mails,
                            'senderid' => $this->getSession()->getUserId()
                        ),
                        array(
                            'senderstatus' => \vc\object\Mail::SENDER_STATUS_DELETED
                        ),
                        false
                    );
                } else {
                    \vc\lib\ErrorHandler::warning(
                        "Invalid group " + $group,
                        __FILE__,
                        __LINE__
                    );
                    // No message needed the visual is feedback enough
                    echo \vc\view\json\View::renderStatus(false);
                    return;
                }
            } else {
                $recipientSuccess = $pmModel->update(
                    array(
                        'id' => $mails,
                        'recipientid' => $this->getSession()->getUserId()
                    ),
                    array(
                        'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_DELETED,
                        'recipient_delete_date' => date('Y-m-d H:i:s')
                    ),
                    false
                );
                $senderSuccess = $pmModel->update(
                    array(
                        'id' => $mails,
                        'senderid' => $this->getSession()->getUserId()
                    ),
                    array(
                        'senderstatus' => \vc\object\Mail::SENDER_STATUS_DELETED
                    ),
                    false
                );
                $success = $recipientSuccess && $senderSuccess;
            }
            $pmThreadModel = $this->getDbModel('PmThread');
            $pmThreadModel->updateThreadByMessageIds($mails);
            if ($success) {
                echo \vc\view\json\View::renderStatus(true);
            } else {
                echo \vc\view\json\View::renderStatus(false, gettext('mailbox.trash.failed'));
            }
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.trash.failed'));
        }
    }
}
