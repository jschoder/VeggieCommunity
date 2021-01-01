<?php
namespace vc\controller\web\account\real;

class CreateController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'account/signup/');
        }

        $realCheckModel = $this->getDbModel('RealCheck');
        $realCheckObject = new \vc\object\RealCheck();
        $realCheckObject->code = date('md') . '-' . strtoupper($realCheckModel->createToken(6));
        $success = $realCheckModel->insertObject($this->getSession()->getProfile(), $realCheckObject);
        if ($success) {
            $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('real.create.success'));
        } else {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('real.create.failed'));
        }
        throw new \vc\exception\RedirectException($this->path . 'account/real/?notification=' . $notification);
    }
}
