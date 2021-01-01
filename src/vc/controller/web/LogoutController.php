<?php
namespace vc\controller\web;

class LogoutController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        // Don't init view before killing the session
        $userId = $this->getSession()->getUserId();
        $this->getSession()->removeLoginCookie();
        $this->getSession()->killSession();

        if (!empty($userId)) {
            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations($userId);
        }

        $notification = $this->setNotification(
            self::NOTIFICATION_SUCCESS,
            gettext('logout.done')
        );
        throw new \vc\exception\RedirectException(
            $this->path . 'login/?notification=' . $notification
        );
    }
}
