<?php
namespace vc\controller\web\pm;

class DeleteAllController extends \vc\controller\web\AbstractWebController
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

        $formValues = array_merge($_POST);
        if (!array_key_exists('profileId', $formValues)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('pm.deleteConversation.failed'));
            return;
        }

        $pmModel = $this->getDbModel('Pm');
        $updatedRecipient = $pmModel->update(
            array(
                'senderid' => intval($formValues['profileId']),
                'recipientid' => $this->getSession()->getUserId(),
                'recipientstatus >' => \vc\object\Mail::RECIPIENT_STATUS_DELETED,
            ),
            array(
                'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_DELETED,
                'recipient_delete_date' => date('Y-m-d H:i:s')
            ),
            false
        );
        $updatedSender = $pmModel->update(
            array(
                'senderid' => $this->getSession()->getUserId(),
                'recipientid' => intval($formValues['profileId']),
                'senderstatus >' => \vc\object\Mail::SENDER_STATUS_DELETED,
            ),
            array(
                'senderstatus' => \vc\object\Mail::SENDER_STATUS_DELETED
            ),
            false
        );
        $pmThreadModel = $this->getDbModel('PmThread');
        // User deleting self talk
        if ($this->getSession()->getUserId() == intval($formValues['profileId'])) {
            $pmThreadModel->update(
                array(
                    'min_user_id' => $this->getSession()->getUserId(),
                    'max_user_id' => intval($formValues['profileId'])
                ),
                array(
                    'min_user_last_pm_id' => null,
                    'max_user_last_pm_id' => null
                )
            );
        } elseif ($this->getSession()->getUserId() < intval($formValues['profileId'])) {
            $pmThreadModel->update(
                array(
                    'min_user_id' => $this->getSession()->getUserId(),
                    'max_user_id' => intval($formValues['profileId'])
                ),
                array(
                    'min_user_last_pm_id' => null
                )
            );
        } else {
            $pmThreadModel->update(
                array(
                    'min_user_id' => intval($formValues['profileId']),
                    'max_user_id' => $this->getSession()->getUserId()
                ),
                array(
                    'max_user_last_pm_id' => null
                )
            );
        }

        $pmDraftModel = $this->getDbModel('PmDraft');
        $pmDraftModel->delete(array(
            'sender_id' => $this->getSession()->getUserId(),
            'recipient_id' => intval($formValues['profileId'])
        ));

        echo \vc\view\json\View::renderStatus($updatedRecipient && $updatedSender);
    }
}
