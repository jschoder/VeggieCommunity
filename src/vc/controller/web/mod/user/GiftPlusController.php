<?php
namespace vc\controller\web\mod\user;

class GiftPlusController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $profileId = $request->getInt('profileid');
        $amount = $request->getInt('amount');
        if (!empty($profileId) && !empty($amount)) {
            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                $this->getSession()->getUserId(),
                $this->getIp(),
                'MOD :: Giving away plus to ' . $profileId . ' for ' . $amount . ' month'
            );


            $plusModel = $this->getDbModel('Plus');
            $saved = $plusModel->create(
                $profileId,
                \vc\object\Plus::PLUS_TYPE_STANDARD,
                $amount,
                \vc\object\Plus::PAYMENT_TYPE_GIFT,
                null
            );

            $settingsModel = $this->getDbModel('Settings');
            $profileModel = $this->getDbModel('Profile');
            $profileModel->updatePlusMarker(
                $profileId,
                $settingsModel->getSettings($profileId)->getValue(\vc\object\Settings::PLUS_MARKER)
            );

            // Updating the plus information in the current session (Allows immediate access to plus features)
            $profiles = $profileModel->getProfiles($this->locale, array($this->getSession()->getUserId()));
            $this->getSession()->updateSession($profiles[0]);

            if ($saved) {
                $notification = $this->setNotification(
                    self::NOTIFICATION_SUCCESS,
                    'Plus has been gifted to the user.'
                );

            } else {
                $notification = $this->setNotification(
                    self::NOTIFICATION_ERROR,
                    'Plus could\'t be gifted to the user. '
                );
            }
        } else {
            $notification = $this->setNotification(
                self::NOTIFICATION_WARNING,
                'At least one parameter is empty'
            );
        }
        throw new \vc\exception\RedirectException(
            $this->path . 'user/view/' . $request->getInt('profileid') . '/mod/?notification=' . $notification
        );
    }
}


