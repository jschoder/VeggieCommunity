<?php
namespace vc\controller\web\plus;

class HistoryController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('plus.history.title'));
        $plusModel = $this->getDbModel('Plus');
        $plusObjects = $plusModel->loadObjects(array('user_id' => $this->getSession()->getUserId()));
        $this->getView()->set('plusObjects', $plusObjects);
        echo $this->getView()->render('plus/history', true);
    }
}
