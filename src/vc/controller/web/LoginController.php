<?php
namespace vc\controller\web;

class LoginController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if ($this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }

        $this->setTitle(gettext('menu.login'));
        $this->getView()->set('activeMenuitem', 'login');

        $this->getView()->setHeader('robots', 'noindex, follow');

        if (count($this->siteParams) > 0) {
            $saveId = $this->siteParams[0];
            if (array_key_exists($saveId . '_TARGET_URL', $_SESSION) &&
                array_key_exists($saveId . '_POST', $_SESSION)) {
                $loginTargetUrl = $_SESSION[$saveId . '_TARGET_URL'];
                $post = $_SESSION[$saveId . '_POST'];
            } else {
                $loginTargetUrl = $this->path . 'mysite';
                $post = array();
            }
        } else {
            if (array_key_exists('HTTP_REFERER', $_SERVER)) {
                $loginTargetUrl = $_SERVER['HTTP_REFERER'];
                if ($loginTargetUrl == $this->path . 'logout') {
                    $loginTargetUrl = $this->path . 'mysite';
                }
            } else {
                $loginTargetUrl = $this->path . 'mysite';
            }
            $post = array();
        }
        $this->getView()->set('loginTargetUrl', $loginTargetUrl);
        $this->getView()->set('post', $post);

        $forumThreadModel = $this->getDbModel('ForumThread');
        $news = $forumThreadModel->getNews($this->locale);
        $this->getView()->set('news', $news);

        echo $this->getView()->render('login', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if ($this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        if (!$request->hasParameter('login-email') ||
            in_array($request->getText('login-email'), \vc\config\Globals::$blockedCountryLoginWhitelist)) {
            // Function sends email. No further action required
            $geoComponent = $this->getComponent('Geo');
            $geoComponent->isIpBlocked($this->getIp(), true, 'Login');
        }

        if (!empty($_POST['login-email']) && !empty($_POST['login-password'])) {
            $loginResult = $this->getSession()->performLogin($this->locale, $this->getIp());
            if ($loginResult[0] === \vc\model\SessionModel::LOGIN_STATUS_SUCCESS) {
                $path = '/' . $this->getSession()->getSetting(\vc\object\Settings::USER_LANGUAGE) . '/';

                if (array_key_exists('loginTargetUrl', $_POST) &&
                   !empty($_POST['loginTargetUrl']) &&
                   $_POST['loginTargetUrl'] != '/') {
                    throw new \vc\exception\RedirectException(
                        str_replace($this->path, $path, $_POST['loginTargetUrl'])
                    );
                } else {
                    $cacheModel = $this->getModel('Cache');
                    $cacheModel->resetProfileRelations($this->getSession()->getUserId());
                    throw new \vc\exception\RedirectException($path . 'mysite/');
                }
            } elseif ($loginResult[0] == \vc\model\SessionModel::LOGIN_STATUS_INACTIVE) {
                $notification = $this->setNotification(self::NOTIFICATION_WARNING, gettext('login.failed.inactive'));
                throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
            } elseif ($loginResult[0] == \vc\model\SessionModel::LOGIN_STATUS_TEMP_BLOCKED) {
                set_time_limit(0);
                sleep(2);

                $blockedTimestamp = strtotime($loginResult[1]);
                $notification = $this->setNotification(
                    self::NOTIFICATION_ERROR,
                    str_replace(
                        array('%DATE%', '%TIME%'),
                        array(
                            date(gettext('login.failed.blocked.format.date'), $blockedTimestamp),
                            date(gettext('login.failed.blocked.format.time'), $blockedTimestamp)
                        ),
                        gettext('login.failed.blocked')
                    )
                );
                throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
            } else {
                set_time_limit(0);
                sleep(2);

                $loginDebugData = array();
                if (array_key_exists('login-email', $_POST) &&
                    array_key_exists('login-password', $_POST)) {
                    $loginDebugData['method'] = 'form';
                    $loginDebugData['email'] = trim($_POST['login-email']);
                    $loginDebugData['password'] = sha1(
                        'ThisIsAReallyReallyReallyReallyBigSaltToPreventReversingPassword' .
                        $_POST['login-password']
                    );
                } elseif (array_key_exists(
                    \vc\model\SessionModel::PERSISTENT_LOGIN_COOKIE_ID,
                    $_COOKIE
                ) &&
                array_key_exists(
                    \vc\model\SessionModel::PERSISTENT_LOGIN_COOKIE_TOKEN,
                    $_COOKIE
                )) {
                    $loginDebugData['method'] = 'cookie';
                    $loginDebugData['id'] = $_COOKIE[\vc\model\SessionModel::PERSISTENT_LOGIN_COOKIE_ID];
                    $loginDebugData['token'] = $_COOKIE[\vc\model\SessionModel::PERSISTENT_LOGIN_COOKIE_TOKEN];
                }
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_LOGIN,
                    $loginDebugData
                );

                $notificationText = gettext('login.failed');
                if (empty($_COOKIE)) {
                    $notificationText .= ' ' . gettext('login.failed.cookies');
                }
                $notification = $this->setNotification(self::NOTIFICATION_ERROR, $notificationText);
                throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
            }
        } else {
            throw new \vc\exception\RedirectException($this->path . 'login/');
        }
    }
}
