<?php
namespace vc\controller\web\account;

class ActivateController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (count($this->siteParams)> 1) {
            $profileId = $this->siteParams[0];
            $token = $this->siteParams[1];

            // Checking the active-status of the the given user
            $profileModel = $this->getDbModel('Profile');
            $active = $profileModel->getField('active', 'id', intval($profileId));
            // No user found for that id
            if ($active === null) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_UNLOCK_URL,
                    array('profileId' => $profileId, 'token' => $token)
                );
                $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("account.activate.failed"));
                throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
            } else {
                if ($active > 0) {
                    // The user is already active
                    // Don't auto login if you you haven't checked the token first.
                    $notification = $this->setNotification(
                        self::NOTIFICATION_SUCCESS,
                        gettext("account.activate.done")
                    );
                    throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
                } elseif ($active < 0) {
                    // The user is already deleted. Maybe the created inative user has expired already
                    $notification = $this->setNotification(
                        self::NOTIFICATION_ERROR,
                        gettext("account.activate.deleted")
                    );
                    throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
                }
            }

            $activationTokenModel = $this->getDbModel('ActivationToken');
            if ($activationTokenModel->isTokenValid($profileId, $token)) {
                $activationTokenModel->setTokenUsed($profileId, $token, time());

                $profileModel->activateProfile($profileId);

                $profileActive = $profileModel->getField('active', 'id', $profileId);
                if (!empty($profileActive) && $profileActive > 0) {
                    // Auto-Login after the activation (only possible once per account)
                    $this->getSession()->createSession($this->locale, $profileId, $this->getIp());

                    $newsModel = $this->getDbModel('News');
                    $newsModel->add(
                        $profileId,
                        'WARNUNG!!! Es treiben sich immer wieder Spamer und Betrüger auf der Seite herum. Bitte ' .
                        'gebe deine E-Mail-Adresse und Telefonnummer nicht an Leute weiter, die du nicht persönlich ' .
                        'getroffen hast. Und BITTE BITTE BITTE überweise keinem anderen Benutzer Geld, egal welche ' .
                        'Geschichte er/sie dir erzählt oder wie glaubwürdig er/sie für dich klingt. Melde uns ' .
                        'solche Geschichten sofort und wir werden überprüfen ob es sich um ein Spamprofil handelt.',
                        'WARNING!!! From time to time we have spamers and fraudsters on the page. Please don\'t send ' .
                        'your e-mail-address or phone number to anyone you haven\'t met in person yet. And PLEASE ' .
                        'PLEASE PLEASE don\'t give money to ANY other user, no matter the story they are telling you ' .
                        'or how believable you think they are. Please report any such story immediately and we will ' .
                        'check whether it is a spam profile.'
                    );

                    $notification = $this->setNotification(
                        self::NOTIFICATION_SUCCESS,
                        gettext("account.activate.done")
                    );
                    throw new \vc\exception\RedirectException(
                        $this->path . 'user/share/' . intval($profileId) . '/?notification=' . $notification
                    );
                } else {
                    $notification = $this->setNotification(
                        self::NOTIFICATION_ERROR,
                        gettext("account.activate.failed")
                    );
                    throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
                }
            } else {
                $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("account.activate.failed"));
                throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
            }
        } else {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_UNLOCK_URL,
                array('params' => $this->siteParams)
            );
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("account.activate.failed"));
            throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
        }
    }
}
