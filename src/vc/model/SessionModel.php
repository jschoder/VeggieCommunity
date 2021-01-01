<?php
namespace vc\model;

class SessionModel extends AbstractModel
{
    const PERSISTENT_LOGIN_COOKIE_ID = 'pli';
    const PERSISTENT_LOGIN_COOKIE_TOKEN = 'plt';
    const PERSISTENT_LOGIN_COOKIE_EXPIRATION = 604800;

    const LOGIN_STATUS_SUCCESS = 1;
    const LOGIN_STATUS_INACTIVE = 10;
    const LOGIN_STATUS_TEMP_BLOCKED = 20;
    const LOGIN_STATUS_FAILED = 99;

    public function hasCookieLogin()
    {
        return !empty($_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID]) &&
               $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID] !== 'deleted' &&
               !empty($_COOKIE[self::PERSISTENT_LOGIN_COOKIE_TOKEN]) &&
               $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_TOKEN] !== 'deleted';
    }

    public function performLogin($locale, $ip)
    {
        // Delete the old cookies from the system
        if (array_key_exists('cookie_login', $_COOKIE)) {
            setcookie('cookie_login', "", time() - 86400, "/");
        }
        if (array_key_exists('cookie_identify', $_COOKIE)) {
            setcookie('cookie_identify', '', time() - 86400, "/");
        }

        if (array_key_exists('login-email', $_POST) &&
            array_key_exists('login-password', $_POST)) {
            $profileid = $this->checkLogin($ip);

            if ($profileid !== null) {
                $blockedLoginModel = $this->getDbModel('BlockedLogin');
                $loginTempBlock = $blockedLoginModel->isBlocked($profileid);
                if ($loginTempBlock) {
                    return array(self::LOGIN_STATUS_TEMP_BLOCKED, $loginTempBlock);
                }

                $this->createSession($locale, $profileid, $ip);
                if (array_key_exists('login-autologin', $_POST)) {
                    $persistentLoginModel = $this->getDbModel('PersistentLogin');
                    if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
                        $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    } else {
                        $userAgent = '';
                    }
                    $expiresAt = time() + self::PERSISTENT_LOGIN_COOKIE_EXPIRATION;
                    $token = $persistentLoginModel->createPersistentLogin($profileid, $userAgent, $expiresAt);
                    setcookie(self::PERSISTENT_LOGIN_COOKIE_ID, $profileid, $expiresAt, "/");
                    setcookie(self::PERSISTENT_LOGIN_COOKIE_TOKEN, $token, $expiresAt, "/");
                }
                return array(self::LOGIN_STATUS_SUCCESS);
            } else {
                if ($this->checkLogin($ip, true)) {
                    return array(self::LOGIN_STATUS_INACTIVE);
                } else {
                    return array(self::LOGIN_STATUS_FAILED);
                }
            }
        } elseif ($this->hasCookieLogin()) {
            $blockedLoginModel = $this->getDbModel('BlockedLogin');
            $loginTempBlock = $blockedLoginModel->isBlocked($_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID]);
            if ($loginTempBlock) {
                return array(self::LOGIN_STATUS_TEMP_BLOCKED, $loginTempBlock);
            }

            if ($this->isValidPersistentLogin($ip)) {
                $this->createSession($locale, intval($_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID]), $ip);
                return array(self::LOGIN_STATUS_SUCCESS);
            } else {
                $this->removeLoginCookie();
                return array(self::LOGIN_STATUS_FAILED);
            }
        } else {
            return array(self::LOGIN_STATUS_FAILED);
        }
    }

    private function isValidPersistentLogin($ip)
    {
        $persistentLoginModel = $this->getDbModel('PersistentLogin');
        $persistentLogin = $persistentLoginModel->getPersistentLogin(
            $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID],
            $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_TOKEN]
        );
        if (array_key_exists("HTTP_USER_AGENT", $_SERVER)) {
            $userAgent = $_SERVER["HTTP_USER_AGENT"];
        } else {
            $userAgent = '';
        }
        $suspicionInfo = array(
            'cookieId' => $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID],
            'cookieToken' => $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_TOKEN],
            'userAgent' => $userAgent,
            'persistentToken' => $persistentLogin,
            'TZoff' => (array_key_exists('TZoff', $_COOKIE) ? $_COOKIE['TZoff'] : '')
        );

        if ($persistentLogin === null) {
            // Providing a login which isn't available in the database. Probably somebody trying to hack our system
            $suspicionModel = $this->getDbModel('Suspicion');
            $suspicionModel->addSuspicion(
                $this->getUserId(),
                \vc\model\db\SuspicionDbModel::TYPE_COOKIE_LOGIN_NOT_FOUND,
                $ip,
                $suspicionInfo
            );
            return false;
        } else {
            // We found an entry in the database which might be expired or inactive

            // Forcefully deactivated. Probably by a logout
            if ($persistentLogin['active'] == 0) {
                $suspicionInfo['reason'] = 'Inactive persistent login';
                $suspicionModel = $this->getDbModel('Suspicion');
                $suspicionModel->addSuspicion(
                    $this->getUserId(),
                    \vc\model\db\SuspicionDbModel::TYPE_COOKIE_LOGIN_EXPIRED_INACTIVE,
                    $ip,
                    $suspicionInfo
                );
                return false;
            }

            // Token is expired. The cookie should have been deleted at this point.
            if (strtotime($persistentLogin['expires_at']) < time()) {
                $suspicionInfo['reason'] = 'Expired persistent login';
                $suspicionModel = $this->getDbModel('Suspicion');
                $suspicionModel->addSuspicion(
                    $this->getUserId(),
                    \vc\model\db\SuspicionDbModel::TYPE_COOKIE_LOGIN_EXPIRED_INACTIVE,
                    $ip,
                    $suspicionInfo
                );
                return false;
            }

            // Different user agent. Probably just a browser update.
            if ($userAgent != $persistentLogin['user_agent']) {
                $suspicionInfo['reason'] = 'Different user agent';
                $suspicionModel = $this->getDbModel('Suspicion');
                $suspicionModel->addSuspicion(
                    $this->getUserId(),
                    \vc\model\db\SuspicionDbModel::TYPE_COOKIE_LOGIN_EXPIRED_INACTIVE,
                    $ip,
                    $suspicionInfo
                );
                return false;
            }

            // Extending the expires at by XXX days
            $newExpiresAt = time() + self::PERSISTENT_LOGIN_COOKIE_EXPIRATION;
            $persistentLoginModel->setExpiresAt(
                $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID],
                $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_TOKEN],
                $newExpiresAt
            );
            setcookie(
                self::PERSISTENT_LOGIN_COOKIE_ID,
                $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID],
                $newExpiresAt,
                "/"
            );
            setcookie(
                self::PERSISTENT_LOGIN_COOKIE_TOKEN,
                $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_TOKEN],
                $newExpiresAt,
                "/"
            );
            return true;
        }
    }

    private function checkLogin($ip, $inactive = false)
    {
        if ($inactive) {
            $activeFilter = array(0);
        } else {
            $activeFilter = array(1, 2);
        }

        if (array_key_exists('login-email', $_POST) &&
            array_key_exists('login-password', $_POST)) {
            $user = $_POST['login-email'];
            $password = $_POST['login-password'];

            $profileModel = $this->getDbModel('Profile');
            $profileId = $profileModel->getProfileIdByUserAndPassword($user, $password, $activeFilter);
            if (empty($profileId)) {
                // Check if the user was forcefully deleted
                $deletedProfileId = $profileModel->getProfileIdByUserAndPassword(
                    $user,
                    $password,
                    array(-21, -50, -51, -52, -53, -54, -55, -56, -57, -58,- 59, -60),
                    false
                );
                if (!empty($deletedProfileId)) {
                    $suspicionModel = $this->getDbModel('Suspicion');
                    $suspicionModel->addSuspicion(
                        null,
                        \vc\model\db\SuspicionDbModel::TYPE_SPAM_BLOCKED_USER_LOGIN_ATTEMPT,
                        $ip,
                        array(
                            'blockedProfileId' => $deletedProfileId
                        )
                    );
                }
                return null;
            } else {
                return $profileId;
            }
        } else {
            return null;
        }
    }

    public function createSession($locale, $profileid, $ip, $updateLastLogin = true)
    {
        @session_regenerate_id(true);

        $profileModel = $this->getDbModel('Profile');
        if ($updateLastLogin) {
            $profileModel->update(
                array(
                    'id' => $profileid
                ),
                array(
                    'last_login' => date('Y-m-d H:i:s'),
                    'reminder_date' => null,
                    'active' => 1
                )
            );
            $this->updateOnlinestatus();
        }

        $profiles = $profileModel->getProfiles($locale, array(intval($profileid)));
        $currentProfile = $profiles[0];
        $this->updateSession($currentProfile);
        $this->insertSessionIp($ip);

        $_SESSION['session-start'] = time();
    }

    public function updateSession($currentProfile)
    {
        $_SESSION['currentProfile'] = $currentProfile;
        $_SESSION['currentProfile.ID'] = $currentProfile->id;

        $plusModel = $this->getDbModel('Plus');
        $_SESSION['plusLevel'] = $plusModel->getPlusLevel($currentProfile->id);

        $websocketUserModel = $this->getDbModel('WebsocketUser');
        $_SESSION['websocket.key'] = $websocketUserModel->add($currentProfile->id);
    }

    public function killSession()
    {
        $onlineModel = $this->getDbModel('Online');
        if ($this->hasActiveSession()) {
            $onlineModel->delete(array('profile_id' => $this->getUserId()));
        }

        if (!empty($_SESSION['websocket.key'])) {
            $query = 'DELETE FROM vc_websocket_user WHERE websocket_key = ?';
            $onlineModel->getDb()->executePrepared($query, array($_SESSION['websocket.key']));
        }
        unset($_SESSION['currentProfile']);
        unset($_SESSION['currentProfile.ID']);
        unset($_SESSION['plusLevel']);
        unset($_SESSION['websocket.key']);
        unset($_SESSION['session-start']);
    }

    public function removeLoginCookie()
    {
        if (array_key_exists(self::PERSISTENT_LOGIN_COOKIE_ID, $_COOKIE) &&
           array_key_exists(self::PERSISTENT_LOGIN_COOKIE_TOKEN, $_COOKIE)) {
            $persistentLoginModel = $this->getDbModel('PersistentLogin');
            $persistentLoginModel->setInactive(
                $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_ID],
                $_COOKIE[self::PERSISTENT_LOGIN_COOKIE_TOKEN]
            );
        }
        setcookie(self::PERSISTENT_LOGIN_COOKIE_ID, "", time() - 86400, "/");
        setcookie(self::PERSISTENT_LOGIN_COOKIE_TOKEN, "", time() - 86400, "/");
    }

    public function hasActiveSession()
    {
        if (empty($_SESSION['currentProfile'])) {
            return false;
        } else {
            return true;
        }
    }

    public function insertSessionIp($ip)
    {
        if ($this->hasActiveSession() && !empty($ip)) {
            $userIpLogModel = $this->getDbModel('UserIpLog');
            $query = 'INSERT INTO vc_user_ip_log SET ip = ?, profile_id = ?, access = ?';
            $statement = $userIpLogModel->getDb()->prepare($query);
            $profileId = $this->getUserId();
            $access = date('Y-m-d H:i:s');
            $statement->bind_param('sis', $ip, $profileId, $access);
            $executed = $statement->execute();
            if (!$executed) {
                \vc\lib\ErrorHandler::error(
                    'Error while inserting Session IP: ' . $statement->errno . ' / ' . $statement->error,
                    __FILE__,
                    __LINE__,
                    array('ip' => $ip,
                          'profileId' => $profileId,
                          'access' => $access)
                );
            }
            $statement->close();
        }
    }

    public function updateOnlinestatus()
    {
        if ($this->hasActiveSession() && $this->getSetting(\vc\object\Settings::VISIBLE_ONLINE)) {
            $onlineModel = $this->getDbModel('Online');
            $onlineModel->insertOnline($this->getUserId());
        }
    }

    public function getUserId()
    {
        if (empty($_SESSION['currentProfile'])) {
            return 0;
        } else {
            return $_SESSION['currentProfile']->id;
        }
    }

    public function getProfile()
    {
        if (empty($_SESSION['currentProfile'])) {
            return null;
        } else {
            return $_SESSION['currentProfile'];
        }
    }

    public function getSetting($key, $default = null)
    {
        $cacheModel = $this->getModel('Cache');
        $settings = $cacheModel->getSettings($this->getUserId());
        return $settings->getValue($key, $default);
    }

    /**
     *
     * @return \vc\object\Settings
     */
    public function getSettings()
    {
        $cacheModel = $this->getModel('Cache');
        return $cacheModel->getSettings($this->getUserId());
    }

    public function getPlusLevel()
    {
        if (empty($_SESSION['plusLevel'])) {
            return null;
        } else {
            return $_SESSION['plusLevel'];
        }
    }

    public function getWebsocketKey()
    {
        if (empty($_SESSION['websocket.key'])) {
            if (empty($_SESSION['currentProfile'])) {
                return null;
            } else {
                $websocketUserModel = $this->getDbModel('WebsocketUser');
                $key = $websocketUserModel->add($_SESSION['currentProfile']->id);
                $_SESSION['websocket.key'] = $key;
                return $key;
            }
        } else {
            return $_SESSION['websocket.key'];
        }
    }

    public function getAllowedSpecialPasswordCharacters()
    {
        return array(
            '~',
            '@',
            '#',
            '$',
            '%',
            '^',
            '&',
            '*',
            '(',
            ')',
            '-',
            '_',
            '=',
            '+',
            '[',
            ']',
            '{',
            '}',
            '\\',
            '|',
            ';',
            ':',
            '\'',
            '"',
            ',',
            '.',
            '<',
            '>',
            '/',
            '!',
            '?'
        );
    }

    public function isPasswordValid($password)
    {
        if (!empty($password)) {
            $normal_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789 ';
            $valid_chars = array_merge($this->getAllowedSpecialPasswordCharacters(), str_split($normal_chars));
            $password_chars = str_split($password);
            foreach ($password_chars as $char) {
                if (!in_array($char, $valid_chars)) {
                    return false;
                }
            }
        }
        return true;
    }

    // vc_profile.admin (bitmask):
    // 0 = default user
    // 1 = chat-moderator    $value % 2 > 0
    // 2 = chat-admin        $value % 4 > 1
    // 4 = site-moderator    $value % 8 > 3
    // 8 = siteadmin         $value % 16 > 7
    public function isAdmin()
    {
        if (empty($_SESSION['currentProfile'])) {
            return false;
        } else {
            return !empty($_SESSION['currentProfile']->admin) &&
                   is_numeric($_SESSION['currentProfile']->admin) &&
                   ($_SESSION['currentProfile']->admin % 16 > 7);
        }
    }

    public function encryptPassword($password, $salt, $firstEntry)
    {
        return sha1($salt . $password . $salt);
    }
}
