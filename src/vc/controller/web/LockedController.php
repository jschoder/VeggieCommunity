<?php
namespace vc\controller\web;

class LockedController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        $this->getSession()->removeLoginCookie();
        $this->getSession()->killSession();
        
        header('HTTP/1.0 423 Locked');
        $this->setTitle(gettext('locked.title'));
        $this->getView()->setHeader('robots', 'noindex, follow');
        echo $this->getView()->render('locked', true);
    }
}
