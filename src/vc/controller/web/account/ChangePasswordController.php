<?php
namespace vc\controller\web\account;

class ChangePasswordController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (count($this->siteParams) > 1) {
            $this->getView()->set('profileId', $this->siteParams[0]);
            $this->getView()->set('token', $this->siteParams[1]);
        } elseif (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->view();
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $tokenMethod = (array_key_exists("profile_id", $_POST) && array_key_exists("token", $_POST));

        if ($this->getSession()->hasActiveSession() || $tokenMethod) {
            $changePwTokenModel = $this->getDbModel('ChangePwToken');
            if ($tokenMethod) {
                $oldPwValid = $changePwTokenModel->isTokenValid($_POST['profile_id'], $_POST['token']);
            } else {
                $profileModel = $this->getDbModel('Profile');
                $oldPassword = $profileModel->getField('password', 'id', $this->getSession()->getUserId());
                if ($oldPassword === null) {
                    $oldPwValid = true;
                } else {
                    $oldPwValid = $profileModel->isPasswordValid($this->getSession()->getUserId(), $_POST["old"]);
                }
            }

            $oldPasswordMessage = null;
            $newPasswordMessage = null;
            if (!$oldPwValid) {
                if ($tokenMethod) {
                    $this->addSuspicion(\vc\model\db\SuspicionDbModel::TYPE_INVALID_CHANGE_PW_URL);

                    $notification = $this->setNotification(
                        self::NOTIFICATION_WARNING,
                        gettext("password.lost.missing")
                    );
                    throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
                } else {
                    $oldPasswordMessage = gettext("password.old.wrong");
                }
            }
            if ($_POST["new1"] != $_POST["new2"]) {
                $newPasswordMessage = gettext("password.new.different");
            } elseif ($_POST["new1"] == "") {
                $newPasswordMessage = gettext("password.new.empty");
            } elseif (!$this->getSession()->isPasswordValid($_POST["new1"])) {
                $newPasswordMessage = gettext("password.new.invalid.characters");
            }

            if ($oldPasswordMessage !== null || $newPasswordMessage !== null) {
                if ($tokenMethod) {
                    $this->getView()->set('profileId', $_POST["profile_id"]);
                    $this->getView()->set('token', $_POST['token']);
                }
                $this->view($oldPasswordMessage, $newPasswordMessage);
            } else {
                $profileModel = $this->getDbModel('Profile');
                if ($tokenMethod) {
                    $saltedPassword = $profileModel->setSaltedPassword(
                        intval($_POST["profile_id"]),
                        $_POST["new1"],
                        $this->getIp()
                    );
                    $changePwTokenModel->setTokenUsed($_POST["profile_id"], $_POST['token'], time());
                } else {
                    $saltedPassword = $profileModel->setSaltedPassword(
                        $this->getSession()->getUserId(),
                        $_POST["new1"],
                        $this->getIp()
                    );
                }

                if ($saltedPassword) {
                    $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext("password.saved"));
                } else {
                    $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("password.failed"));
                }
                if ($this->getSession()->hasActiveSession()) {
                    throw new \vc\exception\RedirectException($this->path . 'mysite/?notification=' . $notification);
                } else {
                    throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
                }
            }
        } else {
            throw new \vc\exception\RedirectException($this->path . 'login/');
        }
    }

    private function view($oldPasswordMessage = null, $newPasswordMessage = null)
    {
        $this->setTitle(gettext('password.sitetitle'));
        $this->getView()->set('activeMenuitem', 'mysite');
        $this->getView()->setHeader('robots', 'noindex, follow');
        $this->getView()->set('allowedSpecialCharacters', $this->getSession()->getAllowedSpecialPasswordCharacters());

        if ($this->getSession()->hasActiveSession()) {
            $profileModel = $this->getDbModel('Profile');
            $password = $profileModel->getField('password', 'id', $this->getSession()->getUserId());
            $this->getView()->set('hasOldPassword', ($password !== null));
        }

        if (!empty($oldPasswordMessage)) {
            $this->getView()->set('oldPasswordMessage', $oldPasswordMessage);
        }
        if (!empty($newPasswordMessage)) {
            $this->getView()->set('newPasswordMessage', $newPasswordMessage);
        }
        echo $this->getView()->render('account/changePassword', true);
    }
}
