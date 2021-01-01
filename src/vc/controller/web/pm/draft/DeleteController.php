<?php
namespace vc\controller\web\pm\draft;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.noactivesession'));
            return;
        }

        if (!$request->hasParameter('id')) {
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $pmDraftModel = $this->getDbModel('PmDraft');
        $deleted = $pmDraftModel->delete(array(
            'id' => $request->getInt('id'),
            'sender_id' => $this->getSession()->getUserId()
        ));

        echo \vc\view\json\View::renderStatus($deleted);
    }
}
