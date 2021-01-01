<?php
namespace vc\controller\web\fb;

class LoginController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if ($this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }

        $this->setTitle(gettext('fb.login.title'));
        $this->getView()->setHeader('robots', 'noindex, follow');
        echo $this->getView()->render('fb/login', true);
    }
}
