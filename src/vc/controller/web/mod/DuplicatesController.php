<?php
namespace vc\controller\web\mod;

class DuplicatesController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $userIpLogModel = $this->getDbModel('UserIpLog');
        $duplicates = $userIpLogModel->getDuplicates();

        $this->setTitle('Duplicate Logins');

        $this->getView()->set('duplicates', $duplicates);
        echo $this->getView()->render('mod/duplicates', true);
    }
}
