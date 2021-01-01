<?php
namespace vc\controller\web\pm\draft;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.noactivesession'));
            return;
        }

        if (!$request->hasParameter('contact') ||
            !$request->hasParameter('message')) {
            echo \vc\view\json\View::renderStatus(false, gettext('pm.draft.add.error'));
            return;
        }

        $pmDraftModel = $this->getDbModel('PmDraft');

        $pmDraft = new \vc\object\PmDraft();
        $pmDraft->senderId = $this->getSession()->getUserId();
        $pmDraft->recipientId = $request->getInt('contact');
        $pmDraft->body = $request->getText('message');
        $pmDraftId = $pmDraftModel->insertObject($this->getSession()->getProfile(), $pmDraft);

        if ($pmDraftId === false) {
            echo \vc\view\json\View::renderStatus(false, gettext('pm.draft.add.error'));
        } else {
            echo \vc\view\json\View::render(array(
                'success' => true,
                'pmDraftId' => $pmDraftId
            ));
        }
    }
}
