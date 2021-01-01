<?php
namespace vc\controller\web\debug;

class OnController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        setcookie('debug', 'true', time() + 3600, "/");

        $notification = $this->setNotification(
            self::NOTIFICATION_SUCCESS,
            gettext('debug.on')
        );
        if ($this->getSession()->hasActiveSession()) {
            $url = $this->path . 'mysite';
        } else {
            $url = $this->path;
        }
        throw new \vc\exception\RedirectException(
            $url . '?notification=' . $notification
        );
    }
}
