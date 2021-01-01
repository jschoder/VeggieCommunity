<?php
namespace vc\controller\web\account;

class ActionController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }



        echo $this->getView()->render('account/actions', true);
    }
}
