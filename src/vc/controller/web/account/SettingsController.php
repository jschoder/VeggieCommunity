<?php
namespace vc\controller\web\account;

class SettingsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $termsModel = $this->getDbModel('Terms');
        if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
            throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
        }

        $this->setTitle(gettext('mysite.settings.title'));
        $this->getView()->setHeader('robots', 'noindex, follow');
        $this->getView()->set('designs', array('matcha', 'lemongras'));

        $customDesignModel = $this->getDbModel('CustomDesign');
        $customDesignObject = $customDesignModel->loadObject(array('profile_id' => $this->getSession()->getUserId()));
        $this->getView()->set('customDesign', $customDesignObject);
        echo $this->getView()->render('account/settings', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }
        \vc\lib\Assert::assertValueInArray(
            'design',
            $_POST['design'],
            array('lemongras', 'matcha'),
            false
        );
        \vc\lib\Assert::assertValueInArray(
            'distanceunit',
            $_POST['distanceunit'],
            array(\vc\object\Settings::DISTANCE_UNIT_KILOMETER, \vc\object\Settings::DISTANCE_UNIT_MILE),
            false
        );
        \vc\lib\Assert::assertValueInArray(
            'userlanguage',
            $_POST['userlanguage'],
            array('de', 'en'),
            false
        );
        $settingsModel = $this->getDbModel('Settings');
        $profileModel = $this->getDbModel('Profile');

        $oldSettings = $this->getSession()->getSettings();

        $success = true;
        $success = $success && $this->setBooleanValue(
            $request,
            'newmailnotification',
            $oldSettings,
            \vc\object\Settings::NEW_MAIL_NOTIFICATION,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'newfriendnotification',
            $oldSettings,
            \vc\object\Settings::NEW_FRIEND_NOTIFICATION,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'friendchangednotification',
            $oldSettings,
            \vc\object\Settings::FRIEND_CHANGED_NOTIFICATION,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'groupmembernotification',
            $oldSettings,
            \vc\object\Settings::GROUP_MEMBER_NOTIFICATION,
            $settingsModel
        );
        $success = $success && $this->setStringValue(
            $request,
            'design',
            $oldSettings,
            \vc\object\Settings::DESIGN,
            $settingsModel
        );
        $success = $success && $this->setStringValue(
            $request,
            'distanceunit',
            $oldSettings,
            \vc\object\Settings::DISTANCE_UNIT,
            $settingsModel
        );
        $success = $success && $this->setStringValue(
            $request,
            'savedSearchDisplay',
            $oldSettings,
            \vc\object\Settings::SAVEDSEARCH_DISPLAY,
            $settingsModel
        );
        $success = $success && $this->setStringValue(
            $request,
            'savedSearchCount',
            $oldSettings,
            \vc\object\Settings::SAVEDSEARCH_COUNT,
            $settingsModel
        );

        $success = $success && $this->setBooleanValue(
            $request,
            'searchengine',
            $oldSettings,
            \vc\object\Settings::SEARCHENGINE,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'visibleonline',
            $oldSettings,
            \vc\object\Settings::VISIBLE_ONLINE,
            $settingsModel
        );
        // Delete from online visibility when deactivating
        if ($oldSettings->getValue(\vc\object\Settings::VISIBLE_ONLINE) &&
            !$request->getBoolean('visibleonline')) {
            $onlineModel = $this->getDbModel('Online');
            $onlineModel->delete(array('profile_id' => $this->getSession()->getUserId()));
        }
        $success = $success && $this->setBooleanValue(
            $request,
            'visiblelastvisitor',
            $oldSettings,
            \vc\object\Settings::VISIBLE_LAST_VISITOR,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'tracking',
            $oldSettings,
            \vc\object\Settings::TRACKING,
            $settingsModel
        );

        $profileModel->update(
            array('id' => $this->getSession()->getUserId()),
            array(
                'tabQuestionaire1Hide' => empty($_POST['questionaire1Hide']) ? 0 : 1,
                'tabQuestionaire2Hide' => empty($_POST['questionaire2Hide']) ? 0 : 1,
                'tabQuestionaire3Hide' => empty($_POST['questionaire3Hide']) ? 0 : 1,
                'tabQuestionaire4Hide' => empty($_POST['questionaire4Hide']) ? 0 : 1,
                'tabQuestionaire5Hide' => empty($_POST['questionaire5Hide']) ? 0 : 1,
                'tabQuestionaire5Hide' => empty($_POST['questionaire5Hide']) ? 0 : 1
            )
        );

        $success = $success && $this->setBooleanValue(
            $request,
            'rotatePics',
            $oldSettings,
            \vc\object\Settings::ROTATE_PICS,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'profileWatermark',
            $oldSettings,
            \vc\object\Settings::PROFILE_WATERMARK,
            $settingsModel
        );

        $success = $success && $this->setBooleanValue(
            $request,
            'plusmarker',
            $oldSettings,
            \vc\object\Settings::PLUS_MARKER,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'ageRangeFilter',
            $oldSettings,
            \vc\object\Settings::AGE_RANGE_FILTER,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'pmFilterIncoming',
            $oldSettings,
            \vc\object\Settings::PM_FILTER_INCOMING,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'pmFilterOutgoing',
            $oldSettings,
            \vc\object\Settings::PM_FILTER_OUTGOING,
            $settingsModel
        );
        $success = $success && $this->setStringValue(
            $request,
            'userlanguage',
            $oldSettings,
            \vc\object\Settings::USER_LANGUAGE,
            $settingsModel
        );

        $success = $success && $this->setBooleanValue(
            $request,
            'pressinterviewpartner',
            $oldSettings,
            \vc\object\Settings::PRESS_INTERVIEW_PARTNER,
            $settingsModel
        );
        $success = $success && $this->setBooleanValue(
            $request,
            'beta_user',
            $oldSettings,
            \vc\object\Settings::BETA_USER,
            $settingsModel
        );


        // Don't change anything on lemongras
        if ($this->design !== 'lemongras') {
            $customColors = array();
            if (!empty($_POST['colors'])) {
                foreach ($_POST['colors'] as $colorId => $colorValue) {
                    if (!empty($colorValue)) {
                        $customColors[$colorId] = $colorValue;
                    }
                }
            }

            $customDesignModel = $this->getDbModel('CustomDesign');
            if (empty($customColors) && empty($_POST['custom_css'])) {
                $success = $success && $customDesignModel->delete(
                    array('profile_id' => $this->getSession()->getUserId())
                );

                // Delete the cookie to reset the design to default
                setcookie('CUSTOM_DESIGN', '', time() - 86400, '/');
            } else {
                $customColorString = json_encode($customColors);
                $success = $success && $customDesignModel->set(
                    $this->getSession()->getUserId(),
                    $customColorString,
                    $_POST['custom_css']
                );

                // Set a custom design cookie to identiy the unique design
                // It will only be used to trigger the browser cache correctly
                setcookie(
                    'CUSTOM_DESIGN',
                    sha1($customColorString . $_POST['custom_css']),
                    time() + 31536000, // 1 year
                    '/'
                );
            }
        }

        $profiles = $profileModel->getProfiles($this->locale, array($this->getSession()->getUserId()));
        $this->getSession()->updateSession($profiles[0]);

        $cacheModel = $this->getModel('Cache');
        $cacheModel->resetSettingsCache($this->getSession()->getUserId());

        $profileModel->updatePlusMarker(
            $this->getSession()->getUserId(),
            $this->getSession()->getSetting(\vc\object\Settings::PLUS_MARKER)
        );

        if ($success) {
            $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('mysite.settings.success'));
        } else {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('mysite.settings.failed'));
        }
        throw new \vc\exception\RedirectException($this->path . 'account/settings/?notification=' . $notification);
    }

    private function setBooleanValue(
        \vc\controller\Request $request,
        $variableName,
        $settings,
        $settingsId,
        $settingsModel) {

        // If the old value isn't set at all force a reset by setting to null
        $oldValue = $settings->hasValue($settingsId) ? boolval($settings->getValue($settingsId))  : null;
        $newValue = $request->getBoolean($variableName);

        if ($oldValue === $newValue) {
            // Nothing to update
            return true;
        } else {
            if ($settingsId === \vc\object\Settings::PROFILE_WATERMARK && $newValue === FALSE) {
                $pictureModel = $this->getDbModel('Picture');
                $pictureModel->removeWatermarkPictures($this->getSession()->getUserId());
            }
            return $settingsModel->setBooleanValue(
                $this->getSession()->getUserId(),
                $settingsId,
                $newValue ? 1 :0
            );
        }
    }

    private function setStringValue(
        \vc\controller\Request $request,
        $variableName,
        $settings,
        $settingsId,
        $settingsModel) {

        // If the old value isn't set at all force a reset by setting to null
        $oldValue = $settings->hasValue($settingsId) ? $settings->getValue($settingsId)  : null;
        $newValue = $request->getText($variableName);

        if ($oldValue === $newValue) {
            // Nothing to update
            return true;
        } else {
            return $settingsModel->setStringValue(
                $this->getSession()->getUserId(),
                $settingsId,
                $newValue
            );
        }
    }
}
