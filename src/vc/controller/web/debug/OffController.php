<?php
namespace vc\controller\web\debug;

class OffController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        setcookie('debug', '', 0, "/");

        $notification = $this->setNotification(
            self::NOTIFICATION_SUCCESS,
            gettext('debug.off')
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
