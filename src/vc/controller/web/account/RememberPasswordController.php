<?php
namespace vc\controller\web\account;

class RememberPasswordController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return false;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if ($request->hasParameter('email')) {
            $this->getView()->set('email', $request->getEmail('email'));
        }
        $this->view();
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (empty($_POST["email"])) {
            $this->view();
            return;
        }

        $suspicionLevel = $this->addSuspicion(
            \vc\model\db\SuspicionDbModel::TYPE_REMIND_PASSWORD,
            array('email' => $_POST["email"])
        );
        if ($suspicionLevel >= \vc\config\Globals::SUSPICION_BLOCK_LEVEL) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $profileModel = $this->getDbModel('Profile');
        $profileId = $profileModel->getActiveProfileIdByEMail($_POST["email"]);
        if ($profileId !== null) {
            $changePwTokenModel = $this->getDbModel('ChangePwToken');
            $token = $changePwTokenModel->addUniqueToken($profileId, time(), $this->getIp());

            $mailComponent = $this->getComponent('Mail');
            $mailSent = $mailComponent->sendMailToUser(
                $this->locale,
                $profileId,
                'rememberpassword.mail.subject',
                'passwordlost',
                array(
                    'LINK' => 'account/changepassword/' . urlencode($profileId) . '/' . urlencode($token) . '/'
                ),
                array(),
                \vc\object\SystemMessage::MAIL_CONFIG_NOTIFY,
                true
            );

            if ($mailSent) {
                $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext("rememberpassword.sent"));
                throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
            } else {
                $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("rememberpassword.notsent"));
                throw new \vc\exception\RedirectException(
                    $this->path . 'account/rememberPassword/?notification=' . $notification
                );
            }
        } else {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("rememberpassword.noprofile"));
            throw new \vc\exception\RedirectException(
                $this->path . 'account/rememberPassword/?notification=' . $notification
            );
        }
    }

    private function view()
    {
        $this->setTitle(gettext("rememberpassword.sitetitle"));
        $this->getView()->set('activeMenuitem', 'login');
        $this->getView()->setHeader('robots', 'noindex, follow');
        echo $this->getView()->render('account/rememberPassword', true);
    }
}
