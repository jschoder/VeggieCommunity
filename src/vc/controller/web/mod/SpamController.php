<?php
namespace vc\controller\web\mod;

class SpamController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle('Spam');

        $pmModel = $this->getDbModel('Pm');
        $messages = $pmModel->getSpamMessages();
        $this->getView()->set('messages', $messages);
        echo $this->getView()->render('mod/spam', true);
    }
}
