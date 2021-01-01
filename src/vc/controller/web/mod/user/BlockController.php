<?php
namespace vc\controller\web\mod\user;

class BlockController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $days = $request->getInt('days');
        if (empty($days)) {
            $notification = $this->setNotification(
                self::NOTIFICATION_WARNING,
                'Days are empty'
            );
        } else {
            $datetime = new \DateTime();
            $datetime->add(new \DateInterval('P' . $days . 'D'));

            $persistentLoginModel = $this->getDbModel('PersistentLogin');
            $persistentLoginModel->setInactive($request->getInt('profileid'));

            $blockedLogin = new \vc\object\BlockedLogin();
            $blockedLogin->userId = $request->getInt('profileid');
            $blockedLogin->blockedTill = $datetime->format('Y-m-d H:i:s');
            $blockedLogin->reason = $request->getText('reason');
            $blockedLogin->blockedBy = $this->getSession()->getUserId();

            $blockedLoginModel = $this->getDbModel('BlockedLogin');
            $blockedLoginModel->insertObject(null, $blockedLogin);

            $notification = $this->setNotification(
                self::NOTIFICATION_SUCCESS,
                'User has been temporary blocked'
            );
        }

        throw new \vc\exception\RedirectException(
            $this->path . 'user/view/' . $request->getInt('profileid') . '/mod/?notification=' . $notification
        );
    }
}
