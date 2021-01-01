<?php
namespace vc\controller\web\account;

class SignupController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        //return true;
        return false;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if ($this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }

        $form = $this->createForm();
        $geoIpModel = $this->getDbModel('GeoIp');
        $defaultCountryId = $geoIpModel->getCountryByIp($this->getIp());
        if ($defaultCountryId !== null) {
            $form->setDefaultValues(array('country' => $defaultCountryId));
        }
        $this->view($form);
    }

    private function createForm($fbUserId = null, $fbAccessToken = null)
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'Signup',
            $this->path,
            $this->locale,
            'account/signup/',
            1.0
        );
        if ($fbUserId !== null && $fbAccessToken !== null) {
            $form->add(new \vc\form\Hidden(
                null,
                'fb_user_id',
                $fbUserId
            ));
            $form->add(new \vc\form\Hidden(
                null,
                'fb_access_token',
                $fbAccessToken
            ));
        }

        $form->add(new \vc\form\Text(
            'nickname',
            'nickname',
            gettext('profile.nickname'),
            20,
            trim(gettext('profile.nickname.edithelp'))
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\MinLengthValidator(2))
          ->addValidator(new \vc\form\validation\UsernameValidator())
          ->addValidator(new \vc\form\validation\UniqueFieldValidator(
              'vc_profile',
              'nickname',
              gettext('edit.validation.nickname.doubleentry'),
              'active >= 0'
          ));

        if ($fbUserId === null) {
            $form->add(new \vc\form\Text(
                'email',
                'email',
                gettext('profile.mail'),
                255,
                trim(gettext('profile.mail.edithelp'))
            ))->setMandatory(true)
              ->addValidator(new \vc\form\validation\MinLengthValidator(6))
              ->addValidator(new \vc\form\validation\EMailValidator())
              ->addValidator(new \vc\form\validation\UniqueFieldValidator(
                  'vc_profile',
                  'email',
                  gettext('edit.field.mail.doubleentry'),
                  'active >= 0'
              ));

            $form->add(new \vc\form\Password(
                'password',
                'password',
                gettext('profile.password1'),
                gettext('profile.password2'),
                100,
                $this->getSession()->getAllowedSpecialPasswordCharacters()
            ))->setMandatory(true)
              ->addValidator(new \vc\form\validation\MinLengthValidator(5));

            $form->add(new \vc\form\Select(
                'gender',
                'gender',
                gettext('profile.gender'),
                \vc\config\Fields::getGenderFields(),
                trim(gettext('profile.gender.edithelp'))
            ))->setMandatory(true);

            $form->add(new \vc\form\Date(
                'birth',
                'birth',
                gettext('profile.birth'),
                strtotime('1920-01-01 00:00:00'),
                strtotime(\vc\config\Globals::MINIMUM_BIRTH_YEAR . '-01-01 00:00:00'),
                false,
                gettext('profile.birth.edithelp'),
                strtotime((\vc\config\Globals::MINIMUM_BIRTH_YEAR + 1) . '-01-01 00:00:00')
            ))->setMandatory(true);
        }

        $form->add(new \vc\form\Select(
            'nutrition',
            'nutrition',
            gettext('profile.nutrition'),
            \vc\config\Fields::getNutritionFields(),
            trim(gettext('profile.nutrition.edithelp'))
        ))->setMandatory(true);

        $form->add(new \vc\form\Text(
            'city',
            'city',
            gettext('profile.city'),
            30,
            trim(gettext('profile.city.edithelp'))
        ))->setMandatory(false);

        $cacheModel = $this->getModel('Cache');
        $countries = $cacheModel->getCountries($this->locale);
        $indexedCountries = array();
        foreach ($countries as $country) {
            $indexedCountries[$country[0]] = $country[1];
        }
        $form->add(new \vc\form\Select(
            'country',
            'country',
            gettext('profile.country'),
            $indexedCountries,
            trim(gettext('profile.country.edithelp'))
        ))->setMandatory(true);

        $form->add(new \vc\form\InfoText(
            self::NOTIFICATION_WARNING,
            str_replace(
                array(
                    '%BUTTON%',
                    '%TERMS_START%',
                    '%TERMS_END%',
                    '%PRIVACY_START%',
                    '%PRIVACY_END%'
                ),
                array(
                    gettext('edit.submit'),
                    '<a class="link" rel="nofollow" href="' . $this->path . 'account/termsofservice/" target="_blank">',
                    '</a>',
                    '<a class="link" rel="nofollow" href="' . $this->path . 'account/privacypolicy/" target="_blank">',
                    '</a>'
                ),
                gettext('confirmterms.termsofuse')
            )
        ));

        $form->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        return $form;
    }

    public function handlePost(\vc\controller\Request $request)
    {
        throw new \vc\exception\RedirectException($this->path . 'account/signup/');
            
            
        if ($this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }

        $formValues = $_POST;
        if (empty($formValues['formid'])) {
            // FB Registration is inactive
            if (false && !empty($formValues['fb_user_id']) && !empty($formValues['fb_access_token'])) {
                $fbGraphObject = $this->getFacebookMe(
                    $formValues['fb_access_token'],
                    'name,email'
                );

                // Can't read the graph object
                if ($fbGraphObject === false) {
                    $notification = $this->setNotification(
                        self::NOTIFICATION_WARNING,
                        gettext('signup.fb.failed')
                    );
                    throw new \vc\exception\RedirectException(
                        $this->path . 'account/signup/?notification=' . $notification
                    );
                }

                // The email address is already taken by an active or unconfirmed profile
                $profileModel = $this->getDbModel('Profile');
                $emailCount = $profileModel->getCount(array(
                    'email' => $fbGraphObject->getField('email'),
                    'active >='  => 0
                ));
                if ($emailCount > 0) {
                    $notification = $this->setNotification(
                        self::NOTIFICATION_WARNING,
                        gettext('signup.fb.duplicateEmail')
                    );
                    throw new \vc\exception\RedirectException(
                        $this->path . 'account/signup/?notification=' . $notification
                    );
                }

                $form = $this->createForm($formValues['fb_user_id'], $formValues['fb_access_token']);
                $defaults = array('nickname' => $fbGraphObject->getField('name'));
                $geoIpModel = $this->getDbModel('GeoIp');
                $defaultCountryId = $geoIpModel->getCountryByIp($this->getIp());
                if ($defaultCountryId !== null) {
                    $defaults['country'] = $defaultCountryId;
                }
                $form->setDefaultValues($defaults);
                $this->view($form);
            } else {
                throw new \vc\exception\RedirectException($this->path . 'account/signup/');
            }
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }
                    
                    // Fake inform the user that his registration was successful if it he is banned
                    if ($this->isBlockedFromRegistration() ||
                        $this->isSuspicionBlocked()) {
                        $notification = $this->setNotification(self::NOTIFICATION_WARNING, gettext("edit.locked.info"));
                        throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
                    }

                    if ($this->saveData($form, $formValues)) {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_WARNING,
                            gettext('edit.locked.info')
                        );
                        throw new \vc\exception\RedirectException(
                            $this->path . 'login/?notification=' . $notification
                        );
                    } else {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('edit.save.failed')
                        );
                        throw new \vc\exception\RedirectException(
                            $this->path . 'account/signup/?notification=' . $notification
                        );
                    }
                } else {
                    $this->getView()->set(
                        'notification',
                        array('type' => self::NOTIFICATION_WARNING, 'message' => gettext('form.validationFailed'))
                    );
                    $form->setDefaultValues($formValues);
                    $this->view($form);
                }
            } else {
                throw new \vc\exception\RedirectException($this->path . 'account/signup/');
            }
        }
    }

    private function isBlockedFromRegistration()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ||
            (!empty($_SERVER['HTTP_USER_AGENT']) && stripos('tor', $_SERVER['HTTP_USER_AGENT']) !== FALSE)) {
            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                0,
                $this->getIp(),
                'User MIGHT try to use a proxy to sign in:' . "\n" .
                "SERVER: " . var_export($_SERVER, true) . "\n" .
                "POST: " . var_export($_POST, true) . "\n" .
                "GET: " . var_export($_GET, true)
            );
        }
    
        if (!empty($_POST['gender']) &&
            $_POST['gender'] == '1' &&
            !empty($_POST['email'])) {
            $bannedEmailModel = $this->getDbModel('BannedEmail');
            $bannedEmailModel->add($_POST['email']);
            return true;
        }
        
        if ($this->isTrashEmailDomain($_POST['email'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_TEMP_EMAIL
            );
            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                0,
                $this->getIp(),
                "User is using temp email to create account \n" .
                "IP: " . $this->getIp() . "\n" .
                "POST: " . var_export($_POST, true)
            );
            return true;
        }
        
        if (!empty($_POST['email']) && str_replace('.', '', $_POST['email']) === 'grigorevale1979@gmailcom') {
            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                0,
                $this->getIp(),
                'Blocking registration due to ukraine spam mail:' . "\n" .
                "IP: " . $this->getIp() . "\n" .
                var_export($_POST, true)
            );
            return true;
        }

        if (!empty($_POST['password'][0]) && $_POST['password'][0] === 'qwerty19') {
            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                0,
                $this->getIp(),
                'Blocking registration due to spam password:' . "\n" .
                "IP: " . $this->getIp() . "\n" .
                var_export($_POST, true)
            );
            return true;
        }
        
        $geoComponent = $this->getComponent('Geo');
        if ($geoComponent->isIpBlocked($this->getIp(), true, 'Registration')) {
            return true;
        }

        if (!empty($_POST['email'])) {
            $profileModel = $this->getDbModel('Profile');
            $blockedCount = $profileModel->getBlockedCountByEmail($_POST['email']);
            if ($blockedCount > 0) {
                $parameters = array_merge($_POST);
                unset($parameters['password1']);
                unset($parameters['password2']);
                $modMessageModel = $this->getDbModel('ModMessage'); 
                $modMessageModel->addMessage(
                    $this->getSession()->getUserId(),
                    $this->getIp(),
                    "Reregistration with banned user: \n" .
                    var_export($parameters, true)
                );
                return true;
            }

            $bannedEmailModel = $this->getDbModel('BannedEmail');
            $bannedCount = $bannedEmailModel->getCount(array('email' => $_POST['email']));
            if ($bannedCount > 0) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_BLOCKED_EMAIL,
                    $loginDebugData
                );
                $parameters = array_merge($_POST);
                unset($parameters['password1']);
                unset($parameters['password2']);
                $modMessageModel = $this->getDbModel('ModMessage');
                $modMessageModel->addMessage(
                    $this->getSession()->getUserId(),
                    $this->getIp(),
                    "Reregistration with banned e-mail: \n" .
                    var_export($parameters, true)
                );
                return true;
            }
        }
        
        $suspicionModel = $this->getDbModel('Suspicion');
        $suspicions = $suspicionModel->getFieldList(
            'debug_data',
            array(
                'ip' => $this->getIp(),
                'type' => array(
                    \vc\model\db\SuspicionDbModel::TYPE_SPAM_BLOCKED_USER_LOGIN_ATTEMPT
                )
            )
        );
        if (count($suspicions) > 0) {
            $parameters = array_merge($_POST);
            unset($parameters['password1']);
            unset($parameters['password2']);
            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                $this->getSession()->getUserId(),
                $this->getIp(),
                "Reregistration after login with banned user. \n" .
                var_export(
                    array(
                        'suspicions' => $suspicions,
                        'parameters' => $parameters
                    ),
                    true
                )
            );
            return true;
        }
        
        $suspicionModel = $this->getDbModel('Suspicion');
        $suspicions = $suspicionModel->getFieldList(
            'debug_data',
            array(
                'ip' => $this->getIp(),
                'type' => array(
                    \vc\model\db\SuspicionDbModel::TYPE_SPAM_ILLEGAL_COUNTRY_LOGIN,
                    \vc\model\db\SuspicionDbModel::TYPE_BLOCKED_EMAIL,
                    \vc\model\db\SuspicionDbModel::TYPE_TEMP_EMAIL
                )
            )
        );
        if (count($suspicions) > 0) {
            $parameters = array_merge($_POST);
            unset($parameters['password1']);
            unset($parameters['password2']);
            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                $this->getSession()->getUserId(),
                $this->getIp(),
                "Reregistration after banned activity. \n" .
                var_export(
                    array(
                        'parameters' => $parameters,
                        'suspicions' => $suspicions
                    ),
                    true
                )
            );
            return true;
        }

        return false;
    }

    private function saveData($form, $formValues)
    {
        $object = $form->getObject(new \vc\object\Profile(), $formValues);

        $isFacebookSignup = !empty($formValues['fb_user_id']) && !empty($formValues['fb_access_token']);
        if ($isFacebookSignup) {
            $fbGraphObject = $this->getFacebookMe(
                $formValues['fb_access_token'],
                'birthday,email,gender'
            );

            if ($fbGraphObject === null) {
                $notification = $this->setNotification(self::NOTIFICATION_WARNING, gettext('signup.fb.failed'));
                throw new \vc\exception\RedirectException(
                    $this->path . 'account/signup/?notification=' . $notification
                );
            }

            $object->birth = date('Y-m-d', $fbGraphObject->getField('birthday')->getTimestamp());
            $object->email = $fbGraphObject->getField('email');
            $object->salt = null;
            $object->password = null;

            $fbGender = $fbGraphObject->getField('gender');
            if ($fbGender == 'male') {
                $object->gender = 2;
            } elseif ($fbGender == 'female') {
                $object->gender = 4;
            } else {
                $object->gender = 8;
            }

            $object->facebookId = $formValues['fb_user_id'];
            $object->active = 1;
        } else {
            // Creating a new salt every time a password is changed.
            $activationTokenModel = $this->getDbModel('ActivationToken');
            $object->salt = $activationTokenModel->createToken(25);
            $object->password = sha1($object->salt . $object->password . $object->salt);
            $object->active = 0;
        }

        $geoComponent = $this->getComponent('Geo');
        $coordinates = $geoComponent->getCoordinates(
            $this->locale,
            '',
            $object->city,
            '',
            $object->country
        );

        $birth = new \DateTime($object->birth);
        $object->birth = date('Y-m-d', $birth->getTimestamp());
        $object->age = $birth->diff(new \DateTime('now'))->y;


        $object->latitude = $coordinates[0];
        $object->longitude = $coordinates[1];
        $object->sinLatitude = sin(pi() * $coordinates[0] / 180);
        $object->cosLatitude = cos(pi() * $coordinates[0] / 180);
        $object->longitudeRadius = pi() * $coordinates[1] / 180;

        $object->firstEntry = date('Y-m-d H:i:s');
        $object->lastUpdate = date('Y-m-d H:i:s');

        $profileModel = $this->getDbModel('Profile');
        $profileId = $profileModel->insertObject(null, $object);
        if ($profileId === false) {
            return false;
        }

        if (!empty($_SESSION['REGISTRATION_REFERER'])) {
            $profileModel->addReferer($_SESSION['REGISTRATION_REFERER']);
        }

        if (!$isFacebookSignup) {
            $activationTokenModel = $this->getDbModel('ActivationToken');
            $token = $activationTokenModel->addUniqueToken($profileId, time());

            $mailComponent = $this->getComponent('Mail');
            $mailComponent->sendMailToUser(
                $this->locale,
                $profileId,
                'edit.locked.subject',
                'account-activate',
                array(
                    'LINK' => 'account/activate/' . urlencode($profileId) . '/' . urlencode($token) . '/'
                ),
                array(),
                \vc\object\SystemMessage::MAIL_CONFIG_NOTIFY,
                true
            );
        }

        $termsModel = $this->getDbModel('Terms');

        $termsOfService = $termsModel->getLatestVersion(\vc\object\Terms::TYPE_TERMS_OF_USE, $this->locale);
        $termsModel->saveTerms(
            \vc\object\Terms::TYPE_TERMS_OF_USE,
            $termsOfService->id,
            $profileId,
            $this->getIp()
        );
        $privacyPolicy = $termsModel->getLatestVersion(\vc\object\Terms::TYPE_PRIVACY_POLICY, $this->locale);
        $termsModel->saveTerms(
            \vc\object\Terms::TYPE_PRIVACY_POLICY,
            $privacyPolicy->id,
            $profileId,
            $this->getIp()
        );

        $settingsModel = $this->getDbModel('Settings');
        $settingsModel->setStringValue($profileId, \vc\object\Settings::USER_LANGUAGE, $this->locale);

        $searchstringModel = $this->getDbModel('Searchstring');
        $searchstringModel->updateIndex($profileId);

        if ($isFacebookSignup) {
            $profileModel->update(
                array(
                    'id' => $profileId
                ),
                array(
                    'active' => 1
                )
            );

            // Auto Login Facebook
            $this->getSession()->createSession($this->locale, $profileId, $this->getIp());
        } else {
            $profilePasswordLogModel = $this->getDbModel('ProfilePasswordLog');
            $profilePasswordLogModel->addLog($profileId, $object->password, $object->salt, $this->getIp());
        }
        $profileEmailLogModel = $this->getDbModel('ProfileEmailLog');
        $profileEmailLogModel->addLog($profileId, $object->email, $this->getIp());

        return true;
    }

    private function view($form)
    {
        $this->setTitle(gettext('signup.title'));
        $this->getView()->set('activeMenuitem', 'signup');

        $this->setForm($form);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('account/signup', true);
    }

    private function isTrashEmailDomain($email)
    {
        if (
            stripos($email, '@0-00.usa.cc') !== false  ||
            stripos($email, '@0-mail.com') !== false  ||
            stripos($email, '@0box.eu') !== false  ||
            stripos($email, '@0clickemail.com') !== false  ||
            stripos($email, '@0mel.com') !== false  ||
            stripos($email, '@0v.ro') !== false  ||
            stripos($email, '@0w.ro') !== false  ||
            stripos($email, '@0wnd.net') !== false  ||
            stripos($email, '@0wnd.org') !== false  ||
            stripos($email, '@0x01.gq') !== false  ||
            stripos($email, '@0x01.tk') !== false  ||
            stripos($email, '@0x207.info') !== false  ||
            stripos($email, '@001.igg.biz') !== false  ||
            stripos($email, '@027168.com') !== false  ||
            stripos($email, '@0815.ru') !== false  ||
            stripos($email, '@0815.ry') !== false  ||
            stripos($email, '@0815.su') !== false  ||
            stripos($email, '@0845.ru') !== false  ||
            stripos($email, '@1-8.biz') !== false  ||
            stripos($email, '@1ce.us') !== false  ||
            stripos($email, '@1chuan.com') !== false  ||
            stripos($email, '@1clck2.com') !== false  ||
            stripos($email, '@1fsdfdsfsdf.tk') !== false  ||
            stripos($email, '@1mail.ml') !== false  ||
            stripos($email, '@1pad.de') !== false  ||
            stripos($email, '@1st-forms.com') !== false  ||
            stripos($email, '@1to1mail.org') !== false  ||
            stripos($email, '@1usemail.com') !== false  ||
            stripos($email, '@1webmail.info') !== false  ||
            stripos($email, '@1zhuan.com') !== false  ||
            stripos($email, '@2.0-00.usa.cc') !== false  ||
            stripos($email, '@2anom.com') !== false  ||
            stripos($email, '@2ether.net') !== false  ||
            stripos($email, '@2fdgdfgdfgdf.tk') !== false  ||
            stripos($email, '@2nd-mail.xyz') !== false  ||
            stripos($email, '@2odem.com') !== false  ||
            stripos($email, '@2prong.com') !== false  ||
            stripos($email, '@2wc.info') !== false  ||
            stripos($email, '@3d-painting.com') !== false  ||
            stripos($email, '@3l6.com') !== false  ||
            stripos($email, '@3mail.ga') !== false  ||
            stripos($email, '@3trtretgfrfe.tk') !== false  ||
            stripos($email, '@4-n.us') !== false  ||
            stripos($email, '@4gfdsgfdgfd.tk') !== false  ||
            stripos($email, '@4mail.cf') !== false  ||
            stripos($email, '@4mail.ga') !== false  ||
            stripos($email, '@4tb.host') !== false  ||
            stripos($email, '@4warding.com') !== false  ||
            stripos($email, '@4warding.net') !== false  ||
            stripos($email, '@4warding.org') !== false  ||
            stripos($email, '@5ghgfhfghfgh.tk') !== false  ||
            stripos($email, '@5gramos.com') !== false  ||
            stripos($email, '@5july.org') !== false  ||
            stripos($email, '@5mail.cf') !== false  ||
            stripos($email, '@5mail.ga') !== false  ||
            stripos($email, '@5oz.ru') !== false  ||
            stripos($email, '@5x25.com') !== false  ||
            stripos($email, '@5ymail.com') !== false  ||
            stripos($email, '@6-6-6.igg.biz') !== false  ||
            stripos($email, '@6-6-6.nut.cc') !== false  ||
            stripos($email, '@6-6-6.usa.cc') !== false  ||
            stripos($email, '@6hjgjhgkilkj.tk') !== false  ||
            stripos($email, '@6ip.us') !== false  ||
            stripos($email, '@6mail.cf') !== false  ||
            stripos($email, '@6mail.ga') !== false  ||
            stripos($email, '@6mail.ml') !== false  ||
            stripos($email, '@6paq.com') !== false  ||
            stripos($email, '@6url.com') !== false  ||
            stripos($email, '@7days-printing.com') !== false  ||
            stripos($email, '@7mail.ga') !== false  ||
            stripos($email, '@7mail.ml') !== false  ||
            stripos($email, '@7tags.com') !== false  ||
            stripos($email, '@8mail.cf') !== false  ||
            stripos($email, '@8mail.ga') !== false  ||
            stripos($email, '@8mail.ml') !== false  ||
            stripos($email, '@9mail.cf') !== false  ||
            stripos($email, '@9me.site') !== false  ||
            stripos($email, '@9ox.net') !== false  ||
            stripos($email, '@9q.ro') !== false  ||
            stripos($email, '@10mail.com') !== false  ||
            stripos($email, '@10mail.org') !== false  ||
            stripos($email, '@10minut.com.pl') !== false  ||
            stripos($email, '@10minut.xyz') !== false  ||
            stripos($email, '@10minutemail.be') !== false  ||
            stripos($email, '@10minutemail.cf') !== false  ||
            stripos($email, '@10minutemail.co.uk') !== false  ||
            stripos($email, '@10minutemail.co.za') !== false  ||
            stripos($email, '@10minutemail.com') !== false  ||
            stripos($email, '@10minutemail.de') !== false  ||
            stripos($email, '@10minutemail.ga') !== false  ||
            stripos($email, '@10minutemail.gq') !== false  ||
            stripos($email, '@10minutemail.ml') !== false  ||
            stripos($email, '@10minutemail.net') !== false  ||
            stripos($email, '@10minutemail.nl') !== false  ||
            stripos($email, '@10minutemail.pro') !== false  ||
            stripos($email, '@10minutemail.us') !== false  ||
            stripos($email, '@10minutemailbox.com') !== false  ||
            stripos($email, '@10minutemails.in') !== false  ||
            stripos($email, '@10minutenemail.de') !== false  ||
            stripos($email, '@10minutesmail.com') !== false  ||
            stripos($email, '@10minutesmail.fr') !== false  ||
            stripos($email, '@10minutmail.pl') !== false  ||
            stripos($email, '@10vpn.info') !== false  ||
            stripos($email, '@10x9.com') !== false  ||
            stripos($email, '@12hosting.net') !== false  ||
            stripos($email, '@12houremail.com') !== false  ||
            stripos($email, '@12minutemail.com') !== false  ||
            stripos($email, '@12minutemail.net') !== false  ||
            stripos($email, '@12storage.com') !== false  ||
            stripos($email, '@14n.co.uk') !== false  ||
            stripos($email, '@15qm.com') !== false  ||
            stripos($email, '@20email.eu') !== false  ||
            stripos($email, '@20email.it') !== false  ||
            stripos($email, '@20mail.eu') !== false  ||
            stripos($email, '@20mail.in') !== false  ||
            stripos($email, '@20mail.it') !== false  ||
            stripos($email, '@20minutemail.com') !== false  ||
            stripos($email, '@20mm.eu') !== false  ||
            stripos($email, '@21cn.com') !== false  ||
            stripos($email, '@24hourmail.com') !== false  ||
            stripos($email, '@24hourmail.net') !== false  ||
            stripos($email, '@30mail.ir') !== false  ||
            stripos($email, '@30minutemail.com') !== false  ||
            stripos($email, '@30minutenmail.eu') !== false  ||
            stripos($email, '@30wave.com') !== false  ||
            stripos($email, '@33mail.com') !== false  ||
            stripos($email, '@36ru.com') !== false  ||
            stripos($email, '@42o.org') !== false  ||
            stripos($email, '@50mb.ml') !== false  ||
            stripos($email, '@50sale.club') !== false  ||
            stripos($email, '@55hosting.net') !== false  ||
            stripos($email, '@60minutemail.com') !== false  ||
            stripos($email, '@75hosting.com') !== false  ||
            stripos($email, '@75hosting.net') !== false  ||
            stripos($email, '@75hosting.org') !== false  ||
            stripos($email, '@99.com') !== false  ||
            stripos($email, '@99experts.com') !== false  ||
            stripos($email, '@100likers.com') !== false  ||
            stripos($email, '@123-m.com') !== false  ||
            stripos($email, '@140unichars.com') !== false  ||
            stripos($email, '@147.cl') !== false  ||
            stripos($email, '@163.com') !== false  ||
            stripos($email, '@300book.info') !== false  ||
            stripos($email, '@333.igg.biz') !== false  ||
            stripos($email, '@418.dk') !== false  ||
            stripos($email, '@675hosting.com') !== false  ||
            stripos($email, '@675hosting.net') !== false  ||
            stripos($email, '@675hosting.org') !== false  ||
            stripos($email, '@1000rebates.stream') !== false  ||
            stripos($email, '@3202.com') !== false  ||
            stripos($email, '@4057.com') !== false  ||
            stripos($email, '@8127ep.com') !== false  ||
            stripos($email, '@11163.com') !== false  ||
            stripos($email, '@80665.com') !== false  ||
            stripos($email, '@672643.net') !== false  ||
            stripos($email, '@2120001.net') !== false  ||
            stripos($email, '@@0815.ru') !== false  ||
            stripos($email, '@a45.in') !== false  ||
            stripos($email, '@a7996.com') !== false  ||
            stripos($email, '@a-bc.net') !== false  ||
            stripos($email, '@a.safe-mail.gq') !== false  ||
            stripos($email, '@aa5zy64.com') !== false  ||
            stripos($email, '@ab0.igg.biz') !== false  ||
            stripos($email, '@abacuswe.us') !== false  ||
            stripos($email, '@abakiss.com') !== false  ||
            stripos($email, '@abcmail.email') !== false  ||
            stripos($email, '@abilitywe.us') !== false  ||
            stripos($email, '@abnamro.usa.cc') !== false  ||
            stripos($email, '@abovewe.us') !== false  ||
            stripos($email, '@absolutewe.us') !== false  ||
            stripos($email, '@abundantwe.us') !== false  ||
            stripos($email, '@abusemail.de') !== false  ||
            stripos($email, '@abuser.eu') !== false  ||
            stripos($email, '@abyssmail.com') !== false  ||
            stripos($email, '@ac20mail.in') !== false  ||
            stripos($email, '@academiccommunity.com') !== false  ||
            stripos($email, '@academywe.us') !== false  ||
            stripos($email, '@acceleratewe.us') !== false  ||
            stripos($email, '@accentwe.us') !== false  ||
            stripos($email, '@acceptwe.us') !== false  ||
            stripos($email, '@acclaimwe.us') !== false  ||
            stripos($email, '@accordwe.us') !== false  ||
            stripos($email, '@accreditedwe.us') !== false  ||
            stripos($email, '@acentri.com') !== false  ||
            stripos($email, '@achievementwe.us') !== false  ||
            stripos($email, '@achievewe.us') !== false  ||
            stripos($email, '@acornwe.us') !== false  ||
            stripos($email, '@acrylicwe.us') !== false  ||
            stripos($email, '@activatewe.us') !== false  ||
            stripos($email, '@activitywe.us') !== false  ||
            stripos($email, '@acuitywe.us') !== false  ||
            stripos($email, '@acumenwe.us') !== false  ||
            stripos($email, '@adaptivewe.us') !== false  ||
            stripos($email, '@adaptwe.us') !== false  ||
            stripos($email, '@add3000.pp.ua') !== false  ||
            stripos($email, '@addictingtrailers.com') !== false  ||
            stripos($email, '@adeptwe.us') !== false  ||
            stripos($email, '@adiq.eu') !== false  ||
            stripos($email, '@aditus.info') !== false  ||
            stripos($email, '@admiralwe.us') !== false  ||
            stripos($email, '@ado888.biz') !== false  ||
            stripos($email, '@adobeccepdm.com') !== false  ||
            stripos($email, '@adoniswe.us') !== false  ||
            stripos($email, '@adpugh.org') !== false  ||
            stripos($email, '@adresseemailtemporaire.com') !== false  ||
            stripos($email, '@adsd.org') !== false  ||
            stripos($email, '@adubiz.info') !== false  ||
            stripos($email, '@advantagewe.us') !== false  ||
            stripos($email, '@advantimo.com') !== false  ||
            stripos($email, '@adventurewe.us') !== false  ||
            stripos($email, '@adventwe.us') !== false  ||
            stripos($email, '@advisorwe.us') !== false  ||
            stripos($email, '@advocatewe.us') !== false  ||
            stripos($email, '@adwaterandstir.com') !== false  ||
            stripos($email, '@aegde.com') !== false  ||
            stripos($email, '@aegia.net') !== false  ||
            stripos($email, '@aegiscorp.net') !== false  ||
            stripos($email, '@aegiswe.us') !== false  ||
            stripos($email, '@aelo.es') !== false  ||
            stripos($email, '@aeonpsi.com') !== false  ||
            stripos($email, '@affiliate-nebenjob.info') !== false  ||
            stripos($email, '@affiliatedwe.us') !== false  ||
            stripos($email, '@affilikingz.de') !== false  ||
            stripos($email, '@affinitywe.us') !== false  ||
            stripos($email, '@affluentwe.us') !== false  ||
            stripos($email, '@affordablewe.us') !== false  ||
            stripos($email, '@afrobacon.com') !== false  ||
            stripos($email, '@afterhourswe.us') !== false  ||
            stripos($email, '@agedmail.com') !== false  ||
            stripos($email, '@agendawe.us') !== false  ||
            stripos($email, '@agger.ro') !== false  ||
            stripos($email, '@agilewe.us') !== false  ||
            stripos($email, '@agorawe.us') !== false  ||
            stripos($email, '@agtx.net') !== false  ||
            stripos($email, '@aheadwe.us') !== false  ||
            stripos($email, '@ahk.jp') !== false  ||
            stripos($email, '@air2token.com') !== false  ||
            stripos($email, '@airsi.de') !== false  ||
            stripos($email, '@ajaxapp.net') !== false  ||
            stripos($email, '@akapost.com') !== false  ||
            stripos($email, '@akerd.com') !== false  ||
            stripos($email, '@akgq701.com') !== false  ||
            stripos($email, '@al-qaeda.us') !== false  ||
            stripos($email, '@albionwe.us') !== false  ||
            stripos($email, '@alchemywe.us') !== false  ||
            stripos($email, '@alfaromeo.igg.biz') !== false  ||
            stripos($email, '@aliaswe.us') !== false  ||
            stripos($email, '@alienware13.com') !== false  ||
            stripos($email, '@aligamel.com') !== false  ||
            stripos($email, '@alisongamel.com') !== false  ||
            stripos($email, '@alivance.com') !== false  ||
            stripos($email, '@alivewe.us') !== false  ||
            stripos($email, '@allaccesswe.us') !== false  ||
            stripos($email, '@allamericanwe.us') !== false  ||
            stripos($email, '@allaroundwe.us') !== false  ||
            stripos($email, '@alldirectbuy.com') !== false  ||
            stripos($email, '@allegiancewe.us') !== false  ||
            stripos($email, '@allegrowe.us') !== false  ||
            stripos($email, '@allgoodwe.us') !== false  ||
            stripos($email, '@alliancewe.us') !== false  ||
            stripos($email, '@allinonewe.us') !== false  ||
            stripos($email, '@allofthem.net') !== false  ||
            stripos($email, '@alloutwe.us') !== false  ||
            stripos($email, '@allowed.org') !== false  ||
            stripos($email, '@alloywe.us') !== false  ||
            stripos($email, '@allprowe.us') !== false  ||
            stripos($email, '@allseasonswe.us') !== false  ||
            stripos($email, '@allstarwe.us') !== false  ||
            stripos($email, '@allthegoodnamesaretaken.org') !== false  ||
            stripos($email, '@allurewe.us') !== false  ||
            stripos($email, '@almondwe.us') !== false  ||
            stripos($email, '@alph.wtf') !== false  ||
            stripos($email, '@alphaomegawe.us') !== false  ||
            stripos($email, '@alpinewe.us') !== false  ||
            stripos($email, '@altairwe.us') !== false  ||
            stripos($email, '@altitudewe.us') !== false  ||
            stripos($email, '@altuswe.us') !== false  ||
            stripos($email, '@ama-trade.de') !== false  ||
            stripos($email, '@ama-trans.de') !== false  ||
            stripos($email, '@amadeuswe.us') !== false  ||
            stripos($email, '@amail4.me') !== false  ||
            stripos($email, '@amail.club') !== false  ||
            stripos($email, '@amail.com') !== false  ||
            stripos($email, '@amazon-aws.org') !== false  ||
            stripos($email, '@amberwe.us') !== false  ||
            stripos($email, '@ambiancewe.us') !== false  ||
            stripos($email, '@ambitiouswe.us') !== false  ||
            stripos($email, '@amelabs.com') !== false  ||
            stripos($email, '@americanawe.us') !== false  ||
            stripos($email, '@americasbestwe.us') !== false  ||
            stripos($email, '@americaswe.us') !== false  ||
            stripos($email, '@amicuswe.us') !== false  ||
            stripos($email, '@amilegit.com') !== false  ||
            stripos($email, '@amiri.net') !== false  ||
            stripos($email, '@amiriindustries.com') !== false  ||
            stripos($email, '@amplewe.us') !== false  ||
            stripos($email, '@amplifiedwe.us') !== false  ||
            stripos($email, '@amplifywe.us') !== false  ||
            stripos($email, '@ampsylike.com') !== false  ||
            stripos($email, '@analogwe.us') !== false  ||
            stripos($email, '@analysiswe.us') !== false  ||
            stripos($email, '@analyticalwe.us') !== false  ||
            stripos($email, '@analyticswe.us') !== false  ||
            stripos($email, '@analyticwe.us') !== false  ||
            stripos($email, '@anappfor.com') !== false  ||
            stripos($email, '@anappthat.com') !== false  ||
            stripos($email, '@andreihusanu.ro') !== false  ||
            stripos($email, '@andthen.us') !== false  ||
            stripos($email, '@animesos.com') !== false  ||
            stripos($email, '@anit.ro') !== false  ||
            stripos($email, '@ano-mail.net') !== false  ||
            stripos($email, '@anon-mail.de') !== false  ||
            stripos($email, '@anonbox.net') !== false  ||
            stripos($email, '@anonmail.top') !== false  ||
            stripos($email, '@anonmails.de') !== false  ||
            stripos($email, '@anonymail.dk') !== false  ||
            stripos($email, '@anonymbox.com') !== false  ||
            stripos($email, '@anonymized.org') !== false  ||
            stripos($email, '@anonymousness.com') !== false  ||
            stripos($email, '@ansibleemail.com') !== false  ||
            stripos($email, '@anthony-junkmail.com') !== false  ||
            stripos($email, '@antichef.com') !== false  ||
            stripos($email, '@antichef.net') !== false  ||
            stripos($email, '@antireg.com') !== false  ||
            stripos($email, '@antireg.ru') !== false  ||
            stripos($email, '@antispam24.de') !== false  ||
            stripos($email, '@antispam.de') !== false  ||
            stripos($email, '@antispammail.de') !== false  ||
            stripos($email, '@anyalias.com') !== false  ||
            stripos($email, '@aoeuhtns.com') !== false  ||
            stripos($email, '@apfelkorps.de') !== false  ||
            stripos($email, '@aphlog.com') !== false  ||
            stripos($email, '@apkmd.com') !== false  ||
            stripos($email, '@appc.se') !== false  ||
            stripos($email, '@appinventor.nl') !== false  ||
            stripos($email, '@appixie.com') !== false  ||
            stripos($email, '@apps.dj') !== false  ||
            stripos($email, '@apssdc.ml') !== false  ||
            stripos($email, '@arduino.hk') !== false  ||
            stripos($email, '@ariaz.jetzt') !== false  ||
            stripos($email, '@armyspy.com') !== false  ||
            stripos($email, '@aron.us') !== false  ||
            stripos($email, '@arroisijewellery.com') !== false  ||
            stripos($email, '@art-en-ligne.pro') !== false  ||
            stripos($email, '@artman-conception.com') !== false  ||
            stripos($email, '@arurgitu.gq') !== false  ||
            stripos($email, '@arvato-community.de') !== false  ||
            stripos($email, '@aschenbrandt.net') !== false  ||
            stripos($email, '@asdasd.nl') !== false  ||
            stripos($email, '@asdasd.ru') !== false  ||
            stripos($email, '@asdhgsad.com') !== false  ||
            stripos($email, '@ashleyandrew.com') !== false  ||
            stripos($email, '@asiarap.usa.cc') !== false  ||
            stripos($email, '@asoes.tk') !== false  ||
            stripos($email, '@asorent.com') !== false  ||
            stripos($email, '@ass.pp.ua') !== false  ||
            stripos($email, '@astonut.tk') !== false  ||
            stripos($email, '@astroempires.info') !== false  ||
            stripos($email, '@asu.mx') !== false  ||
            stripos($email, '@asu.su') !== false  ||
            stripos($email, '@at0mik.org') !== false  ||
            stripos($email, '@audi.igg.biz') !== false  ||
            stripos($email, '@augmentationtechnology.com') !== false  ||
            stripos($email, '@ausgefallen.info') !== false  ||
            stripos($email, '@auti.st') !== false  ||
            stripos($email, '@autorobotica.com') !== false  ||
            stripos($email, '@autotwollow.com') !== false  ||
            stripos($email, '@aver.com') !== false  ||
            stripos($email, '@averdov.com') !== false  ||
            stripos($email, '@avia-tonic.fr') !== false  ||
            stripos($email, '@avls.pt') !== false  ||
            stripos($email, '@awatum.de') !== false  ||
            stripos($email, '@awiki.org') !== false  ||
            stripos($email, '@axiz.org') !== false  ||
            stripos($email, '@axon7zte.com') !== false  ||
            stripos($email, '@axsup.net') !== false  ||
            stripos($email, '@azcomputerworks.com') !== false  ||
            stripos($email, '@azmeil.tk') !== false  ||
            stripos($email, '@b1of96u.com') !== false  ||
            stripos($email, '@b2bx.net') !== false  ||
            stripos($email, '@b2cmail.de') !== false  ||
            stripos($email, '@badgerland.eu') !== false  ||
            stripos($email, '@badoop.com') !== false  ||
            stripos($email, '@badpotato.tk') !== false  ||
            stripos($email, '@banit.club') !== false  ||
            stripos($email, '@banit.me') !== false  ||
            stripos($email, '@bareed.ws') !== false  ||
            stripos($email, '@barryogorman.com') !== false  ||
            stripos($email, '@bartdevos.be') !== false  ||
            stripos($email, '@basscode.org') !== false  ||
            stripos($email, '@bauwerke-online.com') !== false  ||
            stripos($email, '@baxomale.ht.cx') !== false  ||
            stripos($email, '@bazaaboom.com') !== false  ||
            stripos($email, '@bbbbyyzz.info') !== false  ||
            stripos($email, '@bbhost.us') !== false  ||
            stripos($email, '@bcaoo.com') !== false  ||
            stripos($email, '@bcast.ws') !== false  ||
            stripos($email, '@bcb.ro') !== false  ||
            stripos($email, '@bccto.me') !== false  ||
            stripos($email, '@bdmuzic.pw') !== false  ||
            stripos($email, '@bearsarefuzzy.com') !== false  ||
            stripos($email, '@beddly.com') !== false  ||
            stripos($email, '@beefmilk.com') !== false  ||
            stripos($email, '@belamail.org') !== false  ||
            stripos($email, '@belljonestax.com') !== false  ||
            stripos($email, '@benipaula.org') !== false  ||
            stripos($email, '@bershka-terim.space') !== false  ||
            stripos($email, '@bestchannelstv.info') !== false  ||
            stripos($email, '@bestchoiceusedcar.com') !== false  ||
            stripos($email, '@bestoption25.club') !== false  ||
            stripos($email, '@besttempmail.com') !== false  ||
            stripos($email, '@bestvpn.top') !== false  ||
            stripos($email, '@betr.co') !== false  ||
            stripos($email, '@bgtmail.com') !== false  ||
            stripos($email, '@bgx.ro') !== false  ||
            stripos($email, '@bidourlnks.com') !== false  ||
            stripos($email, '@big1.us') !== false  ||
            stripos($email, '@bigprofessor.so') !== false  ||
            stripos($email, '@bigstring.com') !== false  ||
            stripos($email, '@bigwhoop.co.za') !== false  ||
            stripos($email, '@bij.pl') !== false  ||
            stripos($email, '@binka.me') !== false  ||
            stripos($email, '@binkmail.com') !== false  ||
            stripos($email, '@binnary.com') !== false  ||
            stripos($email, '@bio-muesli.info') !== false  ||
            stripos($email, '@bio-muesli.net') !== false  ||
            stripos($email, '@bitwhites.top') !== false  ||
            stripos($email, '@bitymails.us') !== false  ||
            stripos($email, '@blackmarket.to') !== false  ||
            stripos($email, '@bladesmail.net') !== false  ||
            stripos($email, '@blan.tech') !== false  ||
            stripos($email, '@blip.ch') !== false  ||
            stripos($email, '@blnkt.net') !== false  ||
            stripos($email, '@blogmyway.org') !== false  ||
            stripos($email, '@blogspam.ro') !== false  ||
            stripos($email, '@blow-job.nut.cc') !== false  ||
            stripos($email, '@bluedumpling.info') !== false  ||
            stripos($email, '@bluewerks.com') !== false  ||
            stripos($email, '@boatmail.us') !== false  ||
            stripos($email, '@bobmail.info') !== false  ||
            stripos($email, '@bobmurchison.com') !== false  ||
            stripos($email, '@bodhi.lawlita.com') !== false  ||
            stripos($email, '@bofthew.com') !== false  ||
            stripos($email, '@bonobo.email') !== false  ||
            stripos($email, '@bookthemmore.com') !== false  ||
            stripos($email, '@bootybay.de') !== false  ||
            stripos($email, '@borged.com') !== false  ||
            stripos($email, '@borged.net') !== false  ||
            stripos($email, '@borged.org') !== false  ||
            stripos($email, '@bot.nu') !== false  ||
            stripos($email, '@boun.cr') !== false  ||
            stripos($email, '@bouncr.com') !== false  ||
            stripos($email, '@boxformail.in') !== false  ||
            stripos($email, '@boximail.com') !== false  ||
            stripos($email, '@boxtemp.com.br') !== false  ||
            stripos($email, '@brandallday.net') !== false  ||
            stripos($email, '@brasx.org') !== false  ||
            stripos($email, '@breakthru.com') !== false  ||
            stripos($email, '@brefmail.com') !== false  ||
            stripos($email, '@brennendesreich.de') !== false  ||
            stripos($email, '@briggsmarcus.com') !== false  ||
            stripos($email, '@broadbandninja.com') !== false  ||
            stripos($email, '@bsnow.net') !== false  ||
            stripos($email, '@bspamfree.org') !== false  ||
            stripos($email, '@bspooky.com') !== false  ||
            stripos($email, '@bst-72.com') !== false  ||
            stripos($email, '@btb-notes.com') !== false  ||
            stripos($email, '@btc.email') !== false  ||
            stripos($email, '@btcmail.pw') !== false  ||
            stripos($email, '@btcmail.pwguerrillamail.net') !== false  ||
            stripos($email, '@btizet.pl') !== false  ||
            stripos($email, '@buffemail.com') !== false  ||
            stripos($email, '@bugmenever.com') !== false  ||
            stripos($email, '@bugmenot.com') !== false  ||
            stripos($email, '@bulrushpress.com') !== false  ||
            stripos($email, '@bum.net') !== false  ||
            stripos($email, '@bumpymail.com') !== false  ||
            stripos($email, '@bunchofidiots.com') !== false  ||
            stripos($email, '@bund.us') !== false  ||
            stripos($email, '@bundes-li.ga') !== false  ||
            stripos($email, '@bunsenhoneydew.com') !== false  ||
            stripos($email, '@burnthespam.info') !== false  ||
            stripos($email, '@burstmail.info') !== false  ||
            stripos($email, '@businessbackend.com') !== false  ||
            stripos($email, '@businesssuccessislifesuccess.com') !== false  ||
            stripos($email, '@buspad.org') !== false  ||
            stripos($email, '@bussitussi.com') !== false  ||
            stripos($email, '@buy003.com') !== false  ||
            stripos($email, '@buymoreplays.com') !== false  ||
            stripos($email, '@buyordie.info') !== false  ||
            stripos($email, '@buyusedlibrarybooks.org') !== false  ||
            stripos($email, '@buzzcluby.com') !== false  ||
            stripos($email, '@byebyemail.com') !== false  ||
            stripos($email, '@byespm.com') !== false  ||
            stripos($email, '@byom.de') !== false  ||
            stripos($email, '@c51vsgq.com') !== false  ||
            stripos($email, '@cachedot.net') !== false  ||
            stripos($email, '@californiafitnessdeals.com') !== false  ||
            stripos($email, '@cam4you.cc') !== false  ||
            stripos($email, '@camping-grill.info') !== false  ||
            stripos($email, '@candymail.de') !== false  ||
            stripos($email, '@cane.pw') !== false  ||
            stripos($email, '@canitta.icu') !== false  ||
            stripos($email, '@car101.pro') !== false  ||
            stripos($email, '@carbtc.net') !== false  ||
            stripos($email, '@card.zp.ua') !== false  ||
            stripos($email, '@cars2.club') !== false  ||
            stripos($email, '@carsencyclopedia.com') !== false  ||
            stripos($email, '@caseedu.tk') !== false  ||
            stripos($email, '@casualdx.com') !== false  ||
            stripos($email, '@cavi.mx') !== false  ||
            stripos($email, '@cbair.com') !== false  ||
            stripos($email, '@cc-cc.usa.cc') !== false  ||
            stripos($email, '@cc.liamria') !== false  ||
            stripos($email, '@cd.mintemail.com') !== false  ||
            stripos($email, '@cdpa.cc') !== false  ||
            stripos($email, '@ceed.se') !== false  ||
            stripos($email, '@cek.pm') !== false  ||
            stripos($email, '@cellurl.com') !== false  ||
            stripos($email, '@centermail.com') !== false  ||
            stripos($email, '@centermail.net') !== false  ||
            stripos($email, '@cetpass.com') !== false  ||
            stripos($email, '@cfo2go.ro') !== false  ||
            stripos($email, '@ch.tc') !== false  ||
            stripos($email, '@chacuo.net') !== false  ||
            stripos($email, '@chaichuang.com') !== false  ||
            stripos($email, '@chalupaurybnicku.cz') !== false  ||
            stripos($email, '@chammy.info') !== false  ||
            stripos($email, '@cheaphub.net') !== false  ||
            stripos($email, '@cheatmail.de') !== false  ||
            stripos($email, '@chibakenma.ml') !== false  ||
            stripos($email, '@chickenkiller.com') !== false  ||
            stripos($email, '@chielo.com') !== false  ||
            stripos($email, '@childsavetrust.org') !== false  ||
            stripos($email, '@chilkat.com') !== false  ||
            stripos($email, '@chithinh.com') !== false  ||
            stripos($email, '@choco.la') !== false  ||
            stripos($email, '@chogmail.com') !== false  ||
            stripos($email, '@choicemail1.com') !== false  ||
            stripos($email, '@chong-mail.com') !== false  ||
            stripos($email, '@chong-mail.net') !== false  ||
            stripos($email, '@chong-mail.org') !== false  ||
            stripos($email, '@chumpstakingdumps.com') !== false  ||
            stripos($email, '@cigar-auctions.com') !== false  ||
            stripos($email, '@civx.org') !== false  ||
            stripos($email, '@cjpeg.com') !== false  ||
            stripos($email, '@ckiso.com') !== false  ||
            stripos($email, '@cl0ne.net') !== false  ||
            stripos($email, '@cl-cl.org') !== false  ||
            stripos($email, '@clandest.in') !== false  ||
            stripos($email, '@clearwatermail.info') !== false  ||
            stripos($email, '@clgt.wtf') !== false  ||
            stripos($email, '@clickdeal.co') !== false  ||
            stripos($email, '@clipmail.eu') !== false  ||
            stripos($email, '@clixser.com') !== false  ||
            stripos($email, '@clrmail.com') !== false  ||
            stripos($email, '@cmail.club') !== false  ||
            stripos($email, '@cmail.com') !== false  ||
            stripos($email, '@cmail.net') !== false  ||
            stripos($email, '@cmail.org') !== false  ||
            stripos($email, '@cnamed.com') !== false  ||
            stripos($email, '@cnew.ir') !== false  ||
            stripos($email, '@cnmsg.net') !== false  ||
            stripos($email, '@cnsds.de') !== false  ||
            stripos($email, '@cobarekyo1.ml') !== false  ||
            stripos($email, '@cocovpn.com') !== false  ||
            stripos($email, '@codeandscotch.com') !== false  ||
            stripos($email, '@codivide.com') !== false  ||
            stripos($email, '@coieo.com') !== false  ||
            stripos($email, '@coldemail.info') !== false  ||
            stripos($email, '@compareshippingrates.org') !== false  ||
            stripos($email, '@completegolfswing.com') !== false  ||
            stripos($email, '@comwest.de') !== false  ||
            stripos($email, '@consumerriot.com') !== false  ||
            stripos($email, '@contbay.com') !== false  ||
            stripos($email, '@cool.fr.nf') !== false  ||
            stripos($email, '@coolandwacky.us') !== false  ||
            stripos($email, '@coolimpool.org') !== false  ||
            stripos($email, '@coreclip.com') !== false  ||
            stripos($email, '@correo.blogos.net') !== false  ||
            stripos($email, '@cosmorph.com') !== false  ||
            stripos($email, '@courriel.fr.nf') !== false  ||
            stripos($email, '@courrieltemporaire.com') !== false  ||
            stripos($email, '@coza.ro') !== false  ||
            stripos($email, '@crankhole.com') !== false  ||
            stripos($email, '@crapmail.org') !== false  ||
            stripos($email, '@crastination.de') !== false  ||
            stripos($email, '@crazespaces.pw') !== false  ||
            stripos($email, '@crazymailing.com') !== false  ||
            stripos($email, '@cream.pink') !== false  ||
            stripos($email, '@cross-law.ga') !== false  ||
            stripos($email, '@cross-law.gq') !== false  ||
            stripos($email, '@crossroadsmail.com') !== false  ||
            stripos($email, '@crusthost.com') !== false  ||
            stripos($email, '@csh.ro') !== false  ||
            stripos($email, '@cszbl.com') !== false  ||
            stripos($email, '@ctmailing.us') !== false  ||
            stripos($email, '@ctos.ch') !== false  ||
            stripos($email, '@cu.cc') !== false  ||
            stripos($email, '@cubiclink.com') !== false  ||
            stripos($email, '@cuirushi.org') !== false  ||
            stripos($email, '@curlhph.tk') !== false  ||
            stripos($email, '@curryworld.de') !== false  ||
            stripos($email, '@cust.in') !== false  ||
            stripos($email, '@cutout.club') !== false  ||
            stripos($email, '@cuvox.de') !== false  ||
            stripos($email, '@cyber-innovation.club') !== false  ||
            stripos($email, '@cyber-phone.eu') !== false  ||
            stripos($email, '@cylab.org') !== false  ||
            stripos($email, '@d1yun.com') !== false  ||
            stripos($email, '@d3p.dk') !== false  ||
            stripos($email, '@dab.ro') !== false  ||
            stripos($email, '@dacoolest.com') !== false  ||
            stripos($email, '@daemsteam.com') !== false  ||
            stripos($email, '@daibond.info') !== false  ||
            stripos($email, '@daintly.com') !== false  ||
            stripos($email, '@damai.webcam') !== false  ||
            stripos($email, '@dammexe.net') !== false  ||
            stripos($email, '@damnthespam.com') !== false  ||
            stripos($email, '@dandikmail.com') !== false  ||
            stripos($email, '@darkharvestfilms.com') !== false  ||
            stripos($email, '@daryxfox.net') !== false  ||
            stripos($email, '@dash-pads.com') !== false  ||
            stripos($email, '@dataarca.com') !== false  ||
            stripos($email, '@datarca.com') !== false  ||
            stripos($email, '@datazo.ca') !== false  ||
            stripos($email, '@datum2.com') !== false  ||
            stripos($email, '@davidkoh.net') !== false  ||
            stripos($email, '@davidlcreative.com') !== false  ||
            stripos($email, '@dawin.com') !== false  ||
            stripos($email, '@dayrep.com') !== false  ||
            stripos($email, '@dbunker.com') !== false  ||
            stripos($email, '@dbz5mchild.com') !== false  ||
            stripos($email, '@dcemail.com') !== false  ||
            stripos($email, '@ddcrew.com') !== false  ||
            stripos($email, '@de-a.org') !== false  ||
            stripos($email, '@deadaddress.com') !== false  ||
            stripos($email, '@deadchildren.org') !== false  ||
            stripos($email, '@deadfake.cf') !== false  ||
            stripos($email, '@deadfake.ga') !== false  ||
            stripos($email, '@deadfake.ml') !== false  ||
            stripos($email, '@deadfake.tk') !== false  ||
            stripos($email, '@deadspam.com') !== false  ||
            stripos($email, '@deagot.com') !== false  ||
            stripos($email, '@dealja.com') !== false  ||
            stripos($email, '@dealrek.com') !== false  ||
            stripos($email, '@deekayen.us') !== false  ||
            stripos($email, '@defomail.com') !== false  ||
            stripos($email, '@degradedfun.net') !== false  ||
            stripos($email, '@delayload.com') !== false  ||
            stripos($email, '@delayload.net') !== false  ||
            stripos($email, '@delikkt.de') !== false  ||
            stripos($email, '@demen.ml') !== false  ||
            stripos($email, '@dengekibunko.ga') !== false  ||
            stripos($email, '@dengekibunko.gq') !== false  ||
            stripos($email, '@dengekibunko.ml') !== false  ||
            stripos($email, '@der-kombi.de') !== false  ||
            stripos($email, '@derkombi.de') !== false  ||
            stripos($email, '@derluxuswagen.de') !== false  ||
            stripos($email, '@desoz.com') !== false  ||
            stripos($email, '@despam.it') !== false  ||
            stripos($email, '@despammed.com') !== false  ||
            stripos($email, '@dev-null.cf') !== false  ||
            stripos($email, '@dev-null.ga') !== false  ||
            stripos($email, '@dev-null.gq') !== false  ||
            stripos($email, '@dev-null.ml') !== false  ||
            stripos($email, '@devnullmail.com') !== false  ||
            stripos($email, '@deyom.com') !== false  ||
            stripos($email, '@dfgh.net') !== false  ||
            stripos($email, '@dharmatel.net') !== false  ||
            stripos($email, '@dhm.ro') !== false  ||
            stripos($email, '@dhy.cc') !== false  ||
            stripos($email, '@dialogus.com') !== false  ||
            stripos($email, '@diapaulpainting.com') !== false  ||
            stripos($email, '@digitalesbusiness.info') !== false  ||
            stripos($email, '@digitalmariachis.com') !== false  ||
            stripos($email, '@digitalsanctuary.com') !== false  ||
            stripos($email, '@dikriemangasu.tk') !== false  ||
            stripos($email, '@dildosfromspace.com') !== false  ||
            stripos($email, '@dingbone.com') !== false  ||
            stripos($email, '@disaq.com') !== false  ||
            stripos($email, '@disbox.net') !== false  ||
            stripos($email, '@disbox.org') !== false  ||
            stripos($email, '@discard.cf') !== false  ||
            stripos($email, '@discard.email') !== false  ||
            stripos($email, '@discard.ga') !== false  ||
            stripos($email, '@discard.gq') !== false  ||
            stripos($email, '@discard.ml') !== false  ||
            stripos($email, '@discard.tk') !== false  ||
            stripos($email, '@discardmail.com') !== false  ||
            stripos($email, '@discardmail.de') !== false  ||
            stripos($email, '@disign-concept.eu') !== false  ||
            stripos($email, '@disign-revelation.com') !== false  ||
            stripos($email, '@dispo.in') !== false  ||
            stripos($email, '@dispomail.eu') !== false  ||
            stripos($email, '@disposable-email.ml') !== false  ||
            stripos($email, '@disposable.cf') !== false  ||
            stripos($email, '@disposable.ga') !== false  ||
            stripos($email, '@disposable.ml') !== false  ||
            stripos($email, '@disposableaddress.com') !== false  ||
            stripos($email, '@disposableemailaddresses.com') !== false  ||
            stripos($email, '@Disposableemailaddresses:emailmiser.com') !== false  ||
            stripos($email, '@disposableinbox.com') !== false  ||
            stripos($email, '@disposablemails.com') !== false  ||
            stripos($email, '@dispose.it') !== false  ||
            stripos($email, '@disposeamail.com') !== false  ||
            stripos($email, '@disposemail.com') !== false  ||
            stripos($email, '@dispostable.com') !== false  ||
            stripos($email, '@divad.ga') !== false  ||
            stripos($email, '@divermail.com') !== false  ||
            stripos($email, '@divismail.ru') !== false  ||
            stripos($email, '@diwaq.com') !== false  ||
            stripos($email, '@dlemail.ru') !== false  ||
            stripos($email, '@dlwdudtwlt557.ga') !== false  ||
            stripos($email, '@dm.w3internet.co.ukexample.com') !== false  ||
            stripos($email, '@dmarc.ro') !== false  ||
            stripos($email, '@dndent.com') !== false  ||
            stripos($email, '@dnses.ro') !== false  ||
            stripos($email, '@doanart.com') !== false  ||
            stripos($email, '@dob.jp') !== false  ||
            stripos($email, '@docmail.cz') !== false  ||
            stripos($email, '@dodgeit.com') !== false  ||
            stripos($email, '@dodgemail.de') !== false  ||
            stripos($email, '@dodgit.com') !== false  ||
            stripos($email, '@dodgit.org') !== false  ||
            stripos($email, '@dodsi.com') !== false  ||
            stripos($email, '@doiea.com') !== false  ||
            stripos($email, '@dolphinnet.net') !== false  ||
            stripos($email, '@domainaing.gq') !== false  ||
            stripos($email, '@domainaing.tk') !== false  ||
            stripos($email, '@domforfb1.tk') !== false  ||
            stripos($email, '@domforfb2.tk') !== false  ||
            stripos($email, '@domforfb3.tk') !== false  ||
            stripos($email, '@domforfb4.tk') !== false  ||
            stripos($email, '@domforfb5.tk') !== false  ||
            stripos($email, '@domforfb6.tk') !== false  ||
            stripos($email, '@domforfb7.tk') !== false  ||
            stripos($email, '@domforfb8.tk') !== false  ||
            stripos($email, '@domforfb9.tk') !== false  ||
            stripos($email, '@domforfb18.tk') !== false  ||
            stripos($email, '@domforfb19.tk') !== false  ||
            stripos($email, '@domforfb23.tk') !== false  ||
            stripos($email, '@domforfb27.tk') !== false  ||
            stripos($email, '@domforfb29.tk') !== false  ||
            stripos($email, '@domozmail.com') !== false  ||
            stripos($email, '@donemail.ru') !== false  ||
            stripos($email, '@dongqing365.com') !== false  ||
            stripos($email, '@dontreg.com') !== false  ||
            stripos($email, '@dontsendmespam.de') !== false  ||
            stripos($email, '@doquier.tk') !== false  ||
            stripos($email, '@dotman.de') !== false  ||
            stripos($email, '@dotmsg.com') !== false  ||
            stripos($email, '@dotslashrage.com') !== false  ||
            stripos($email, '@douchelounge.com') !== false  ||
            stripos($email, '@dozvon-spb.ru') !== false  ||
            stripos($email, '@dp76.com') !== false  ||
            stripos($email, '@dqnwara.com') !== false  ||
            stripos($email, '@dr69.site') !== false  ||
            stripos($email, '@drdrb.com') !== false  ||
            stripos($email, '@drdrb.net') !== false  ||
            stripos($email, '@dred.ru') !== false  ||
            stripos($email, '@drevo.si') !== false  ||
            stripos($email, '@drivetagdev.com') !== false  ||
            stripos($email, '@droolingfanboy.de') !== false  ||
            stripos($email, '@dropcake.de') !== false  ||
            stripos($email, '@droplar.com') !== false  ||
            stripos($email, '@dropmail.me') !== false  ||
            stripos($email, '@dsiay.com') !== false  ||
            stripos($email, '@dspwebservices.com') !== false  ||
            stripos($email, '@duam.net') !== false  ||
            stripos($email, '@duck2.club') !== false  ||
            stripos($email, '@ducutuan.cn') !== false  ||
            stripos($email, '@dudmail.com') !== false  ||
            stripos($email, '@duk33.com') !== false  ||
            stripos($email, '@dukedish.com') !== false  ||
            stripos($email, '@dump-email.info') !== false  ||
            stripos($email, '@dumpandjunk.com') !== false  ||
            stripos($email, '@dumpmail.de') !== false  ||
            stripos($email, '@dumpyemail.com') !== false  ||
            stripos($email, '@durandinterstellar.com') !== false  ||
            stripos($email, '@duskmail.com') !== false  ||
            stripos($email, '@dwse.edu.pl') !== false  ||
            stripos($email, '@dyceroprojects.com') !== false  ||
            stripos($email, '@dz17.net') !== false  ||
            stripos($email, '@e3z.de') !== false  ||
            stripos($email, '@e4ward.com') !== false  ||
            stripos($email, '@e-mail.com') !== false  ||
            stripos($email, '@e-mail.igg.biz') !== false  ||
            stripos($email, '@e-mail.org') !== false  ||
            stripos($email, '@e-mailbox.ga') !== false  ||
            stripos($email, '@e-postkasten.com') !== false  ||
            stripos($email, '@e-postkasten.de') !== false  ||
            stripos($email, '@e-postkasten.eu') !== false  ||
            stripos($email, '@e-postkasten.info') !== false  ||
            stripos($email, '@e-tomarigi.com') !== false  ||
            stripos($email, '@easy-trash-mail.com') !== false  ||
            stripos($email, '@easynetwork.info') !== false  ||
            stripos($email, '@easytrashmail.com') !== false  ||
            stripos($email, '@eatmea2z.club') !== false  ||
            stripos($email, '@eay.jp') !== false  ||
            stripos($email, '@ebeschlussbuch.de') !== false  ||
            stripos($email, '@ecallheandi.com') !== false  ||
            stripos($email, '@ecolo-online.fr') !== false  ||
            stripos($email, '@edgex.ru') !== false  ||
            stripos($email, '@edinburgh-airporthotels.com') !== false  ||
            stripos($email, '@edv.to') !== false  ||
            stripos($email, '@ee1.pl') !== false  ||
            stripos($email, '@ee2.pl') !== false  ||
            stripos($email, '@eelmail.com') !== false  ||
            stripos($email, '@efxs.ca') !== false  ||
            stripos($email, '@einmalmail.de') !== false  ||
            stripos($email, '@einrot.com') !== false  ||
            stripos($email, '@einrot.de') !== false  ||
            stripos($email, '@eintagsmail.de') !== false  ||
            stripos($email, '@elearningjournal.org') !== false  ||
            stripos($email, '@electro.mn') !== false  ||
            stripos($email, '@elitevipatlantamodels.com') !== false  ||
            stripos($email, '@email60.com') !== false  ||
            stripos($email, '@email-fake.cf') !== false  ||
            stripos($email, '@email-fake.com') !== false  ||
            stripos($email, '@email-fake.ga') !== false  ||
            stripos($email, '@email-fake.gq') !== false  ||
            stripos($email, '@email-fake.ml') !== false  ||
            stripos($email, '@email-fake.tk') !== false  ||
            stripos($email, '@email-jetable.fr') !== false  ||
            stripos($email, '@email-temp.com') !== false  ||
            stripos($email, '@email.cbes.net') !== false  ||
            stripos($email, '@email.net') !== false  ||
            stripos($email, '@emailage.cf') !== false  ||
            stripos($email, '@emailage.ga') !== false  ||
            stripos($email, '@emailage.gq') !== false  ||
            stripos($email, '@emailage.ml') !== false  ||
            stripos($email, '@emailage.tk') !== false  ||
            stripos($email, '@emailapps.info') !== false  ||
            stripos($email, '@emaildienst.de') !== false  ||
            stripos($email, '@emailfake.ml') !== false  ||
            stripos($email, '@emailfake.nut.cc') !== false  ||
            stripos($email, '@emailfake.usa.cc') !== false  ||
            stripos($email, '@emailfreedom.ml') !== false  ||
            stripos($email, '@emailgo.de') !== false  ||
            stripos($email, '@emailias.com') !== false  ||
            stripos($email, '@emailigo.de') !== false  ||
            stripos($email, '@emailinfive.com') !== false  ||
            stripos($email, '@emailisvalid.com') !== false  ||
            stripos($email, '@emaillime.com') !== false  ||
            stripos($email, '@emailmiser.com') !== false  ||
            stripos($email, '@emailna.co') !== false  ||
            stripos($email, '@emailo.pro') !== false  ||
            stripos($email, '@emailproxsy.com') !== false  ||
            stripos($email, '@emailresort.com') !== false  ||
            stripos($email, '@emails.ga') !== false  ||
            stripos($email, '@emailsecurer.com') !== false  ||
            stripos($email, '@emailsensei.com') !== false  ||
            stripos($email, '@emailsingularity.net') !== false  ||
            stripos($email, '@emailspam.cf') !== false  ||
            stripos($email, '@emailspam.ga') !== false  ||
            stripos($email, '@emailspam.gq') !== false  ||
            stripos($email, '@emailspam.ml') !== false  ||
            stripos($email, '@emailspam.tk') !== false  ||
            stripos($email, '@emailsy.info') !== false  ||
            stripos($email, '@emailtech.info') !== false  ||
            stripos($email, '@emailtemporanea.com') !== false  ||
            stripos($email, '@emailtemporanea.net') !== false  ||
            stripos($email, '@emailtemporar.ro') !== false  ||
            stripos($email, '@emailtemporario.com.br') !== false  ||
            stripos($email, '@emailthe.net') !== false  ||
            stripos($email, '@emailtmp.com') !== false  ||
            stripos($email, '@emailto.de') !== false  ||
            stripos($email, '@emailure.net') !== false  ||
            stripos($email, '@emailwarden.com') !== false  ||
            stripos($email, '@emailx.at.hm') !== false  ||
            stripos($email, '@emailxfer.com') !== false  ||
            stripos($email, '@emailz.cf') !== false  ||
            stripos($email, '@emailz.ga') !== false  ||
            stripos($email, '@emailz.gq') !== false  ||
            stripos($email, '@emailz.ml') !== false  ||
            stripos($email, '@emeil.in') !== false  ||
            stripos($email, '@emeil.ir') !== false  ||
            stripos($email, '@emeraldwebmail.com') !== false  ||
            stripos($email, '@emil.com') !== false  ||
            stripos($email, '@emkei.cf') !== false  ||
            stripos($email, '@emkei.ga') !== false  ||
            stripos($email, '@emkei.gq') !== false  ||
            stripos($email, '@emkei.ml') !== false  ||
            stripos($email, '@emkei.tk') !== false  ||
            stripos($email, '@eml.pp.ua') !== false  ||
            stripos($email, '@emlhub.com') !== false  ||
            stripos($email, '@emlpro.com') !== false  ||
            stripos($email, '@emltmp.com') !== false  ||
            stripos($email, '@empireanime.ga') !== false  ||
            stripos($email, '@emz.net') !== false  ||
            stripos($email, '@enayu.com') !== false  ||
            stripos($email, '@enterto.com') !== false  ||
            stripos($email, '@envy17.com') !== false  ||
            stripos($email, '@epb.ro') !== false  ||
            stripos($email, '@ephemail.net') !== false  ||
            stripos($email, '@ephemeral.email') !== false  ||
            stripos($email, '@eqiluxspam.ga') !== false  ||
            stripos($email, '@ericjohnson.ml') !== false  ||
            stripos($email, '@ero-tube.org') !== false  ||
            stripos($email, '@esc.la') !== false  ||
            stripos($email, '@escapehatchapp.com') !== false  ||
            stripos($email, '@esemay.com') !== false  ||
            stripos($email, '@esgeneri.com') !== false  ||
            stripos($email, '@esprity.com') !== false  ||
            stripos($email, '@estate-invest.fr') !== false  ||
            stripos($email, '@eth2btc.info') !== false  ||
            stripos($email, '@ether123.net') !== false  ||
            stripos($email, '@ethereum1.top') !== false  ||
            stripos($email, '@ethersports.org') !== false  ||
            stripos($email, '@ethersportz.info') !== false  ||
            stripos($email, '@etlgr.com') !== false  ||
            stripos($email, '@etranquil.com') !== false  ||
            stripos($email, '@etranquil.net') !== false  ||
            stripos($email, '@etranquil.org') !== false  ||
            stripos($email, '@euaqa.com') !== false  ||
            stripos($email, '@evanfox.info') !== false  ||
            stripos($email, '@evilcomputer.com') !== false  ||
            stripos($email, '@evopo.com') !== false  ||
            stripos($email, '@evyush.com') !== false  ||
            stripos($email, '@exitstageleft.net') !== false  ||
            stripos($email, '@explodemail.com') !== false  ||
            stripos($email, '@express.net.ua') !== false  ||
            stripos($email, '@extremail.ru') !== false  ||
            stripos($email, '@eyepaste.com') !== false  ||
            stripos($email, '@ez.lv') !== false  ||
            stripos($email, '@ezfill.com') !== false  ||
            stripos($email, '@ezstest.com') !== false  ||
            stripos($email, '@f1kzc0d3.tk') !== false  ||
            stripos($email, '@f4k.es') !== false  ||
            stripos($email, '@f5.si') !== false  ||
            stripos($email, '@facebook-email.cf') !== false  ||
            stripos($email, '@facebook-email.ga') !== false  ||
            stripos($email, '@facebook-email.ml') !== false  ||
            stripos($email, '@facebookmail.gq') !== false  ||
            stripos($email, '@facebookmail.ml') !== false  ||
            stripos($email, '@fackme.gq') !== false  ||
            stripos($email, '@fadingemail.com') !== false  ||
            stripos($email, '@faecesmail.me') !== false  ||
            stripos($email, '@fag.wf') !== false  ||
            stripos($email, '@failbone.com') !== false  ||
            stripos($email, '@faithkills.com') !== false  ||
            stripos($email, '@fake-email.pp.ua') !== false  ||
            stripos($email, '@fake-mail.cf') !== false  ||
            stripos($email, '@fake-mail.ga') !== false  ||
            stripos($email, '@fake-mail.ml') !== false  ||
            stripos($email, '@fakedemail.com') !== false  ||
            stripos($email, '@fakeinbox.cf') !== false  ||
            stripos($email, '@fakeinbox.com') !== false  ||
            stripos($email, '@fakeinbox.ga') !== false  ||
            stripos($email, '@fakeinbox.info') !== false  ||
            stripos($email, '@fakeinbox.ml') !== false  ||
            stripos($email, '@fakeinbox.tk') !== false  ||
            stripos($email, '@fakeinformation.com') !== false  ||
            stripos($email, '@fakemail.fr') !== false  ||
            stripos($email, '@fakemailgenerator.com') !== false  ||
            stripos($email, '@fakemailz.com') !== false  ||
            stripos($email, '@fakemyinbox.com') !== false  ||
            stripos($email, '@fammix.com') !== false  ||
            stripos($email, '@fangoh.com') !== false  ||
            stripos($email, '@fanqiegu.cn') !== false  ||
            stripos($email, '@fansworldwide.de') !== false  ||
            stripos($email, '@fantasymail.de') !== false  ||
            stripos($email, '@farrse.co.uk') !== false  ||
            stripos($email, '@fast-mail.fr') !== false  ||
            stripos($email, '@fastacura.com') !== false  ||
            stripos($email, '@fastchevy.com') !== false  ||
            stripos($email, '@fastchrysler.com') !== false  ||
            stripos($email, '@fasternet.biz') !== false  ||
            stripos($email, '@fastkawasaki.com') !== false  ||
            stripos($email, '@fastmazda.com') !== false  ||
            stripos($email, '@fastmitsubishi.com') !== false  ||
            stripos($email, '@fastnissan.com') !== false  ||
            stripos($email, '@fastsubaru.com') !== false  ||
            stripos($email, '@fastsuzuki.com') !== false  ||
            stripos($email, '@fasttoyota.com') !== false  ||
            stripos($email, '@fastyamaha.com') !== false  ||
            stripos($email, '@fatflap.com') !== false  ||
            stripos($email, '@fbma.tk') !== false  ||
            stripos($email, '@fbmail.usa.cc') !== false  ||
            stripos($email, '@fddns.ml') !== false  ||
            stripos($email, '@fdfdsfds.com') !== false  ||
            stripos($email, '@fer-gabon.org') !== false  ||
            stripos($email, '@fettometern.com') !== false  ||
            stripos($email, '@fictionsite.com') !== false  ||
            stripos($email, '@fightallspam.com') !== false  ||
            stripos($email, '@figjs.com') !== false  ||
            stripos($email, '@figshot.com') !== false  ||
            stripos($email, '@fiifke.de') !== false  ||
            stripos($email, '@filbert4u.com') !== false  ||
            stripos($email, '@filberts4u.com') !== false  ||
            stripos($email, '@film-blog.biz') !== false  ||
            stripos($email, '@filzmail.com') !== false  ||
            stripos($email, '@findu.pl') !== false  ||
            stripos($email, '@fir.hk') !== false  ||
            stripos($email, '@fivemail.de') !== false  ||
            stripos($email, '@fixmail.tk') !== false  ||
            stripos($email, '@fizmail.com') !== false  ||
            stripos($email, '@fleckens.hu') !== false  ||
            stripos($email, '@flemail.ru') !== false  ||
            stripos($email, '@flitafir.de') !== false  ||
            stripos($email, '@flowu.com') !== false  ||
            stripos($email, '@flu-cc.flu.cc') !== false  ||
            stripos($email, '@flucc.flu.cc') !== false  ||
            stripos($email, '@fluidsoft.us') !== false  ||
            stripos($email, '@flurred.com') !== false  ||
            stripos($email, '@fly-ts.de') !== false  ||
            stripos($email, '@flyinggeek.net') !== false  ||
            stripos($email, '@flyspam.com') !== false  ||
            stripos($email, '@foobarbot.net') !== false  ||
            stripos($email, '@footard.com') !== false  ||
            stripos($email, '@forecastertests.com') !== false  ||
            stripos($email, '@foreskin.cf') !== false  ||
            stripos($email, '@foreskin.ga') !== false  ||
            stripos($email, '@foreskin.gq') !== false  ||
            stripos($email, '@foreskin.ml') !== false  ||
            stripos($email, '@foreskin.tk') !== false  ||
            stripos($email, '@forgetmail.com') !== false  ||
            stripos($email, '@fornow.eu') !== false  ||
            stripos($email, '@forspam.net') !== false  ||
            stripos($email, '@forward.cat') !== false  ||
            stripos($email, '@foxja.com') !== false  ||
            stripos($email, '@foxtrotter.info') !== false  ||
            stripos($email, '@fr33mail.info') !== false  ||
            stripos($email, '@fr.nf') !== false  ||
            stripos($email, '@frapmail.com') !== false  ||
            stripos($email, '@free-email.cf') !== false  ||
            stripos($email, '@free-email.ga') !== false  ||
            stripos($email, '@freebabysittercam.com') !== false  ||
            stripos($email, '@freeblackbootytube.com') !== false  ||
            stripos($email, '@freecat.net') !== false  ||
            stripos($email, '@freedom4you.info') !== false  ||
            stripos($email, '@freedompop.us') !== false  ||
            stripos($email, '@freefattymovies.com') !== false  ||
            stripos($email, '@freeforall.site') !== false  ||
            stripos($email, '@freelance-france.eu') !== false  ||
            stripos($email, '@freelance-france.euposta.store') !== false  ||
            stripos($email, '@freeletter.me') !== false  ||
            stripos($email, '@freemail.ms') !== false  ||
            stripos($email, '@freemail.tweakly.net') !== false  ||
            stripos($email, '@freemails.cf') !== false  ||
            stripos($email, '@freemails.ga') !== false  ||
            stripos($email, '@freemails.ml') !== false  ||
            stripos($email, '@freemailzone.com') !== false  ||
            stripos($email, '@freeplumpervideos.com') !== false  ||
            stripos($email, '@freeschoolgirlvids.com') !== false  ||
            stripos($email, '@freesistercam.com') !== false  ||
            stripos($email, '@freeteenbums.com') !== false  ||
            stripos($email, '@freundin.ru') !== false  ||
            stripos($email, '@friendlymail.co.uk') !== false  ||
            stripos($email, '@front14.org') !== false  ||
            stripos($email, '@ftp.sh') !== false  ||
            stripos($email, '@ftpinc.ca') !== false  ||
            stripos($email, '@fuckedupload.com') !== false  ||
            stripos($email, '@fuckingduh.com') !== false  ||
            stripos($email, '@fuckme69.club') !== false  ||
            stripos($email, '@fucknloveme.top') !== false  ||
            stripos($email, '@fuckxxme.top') !== false  ||
            stripos($email, '@fudgerub.com') !== false  ||
            stripos($email, '@fuirio.com') !== false  ||
            stripos($email, '@fulvie.com') !== false  ||
            stripos($email, '@fun64.com') !== false  ||
            stripos($email, '@funnycodesnippets.com') !== false  ||
            stripos($email, '@funnymail.de') !== false  ||
            stripos($email, '@furusato.tokyo') !== false  ||
            stripos($email, '@furzauflunge.de') !== false  ||
            stripos($email, '@fuwamofu.com') !== false  ||
            stripos($email, '@fux0ringduh.com') !== false  ||
            stripos($email, '@fxnxs.com') !== false  ||
            stripos($email, '@fyii.de') !== false  ||
            stripos($email, '@g1xmail.top') !== false  ||
            stripos($email, '@g2xmail.top') !== false  ||
            stripos($email, '@g3xmail.top') !== false  ||
            stripos($email, '@g4hdrop.us') !== false  ||
            stripos($email, '@g14l71lb.com') !== false  ||
            stripos($email, '@gafy.net') !== false  ||
            stripos($email, '@gaggle.net') !== false  ||
            stripos($email, '@galaxy.tv') !== false  ||
            stripos($email, '@gally.jp') !== false  ||
            stripos($email, '@gamail.top') !== false  ||
            stripos($email, '@gamegregious.com') !== false  ||
            stripos($email, '@gamgling.com') !== false  ||
            stripos($email, '@garage46.com') !== false  ||
            stripos($email, '@garasikita.pw') !== false  ||
            stripos($email, '@garbagecollector.org') !== false  ||
            stripos($email, '@garbagemail.org') !== false  ||
            stripos($email, '@gardenscape.ca') !== false  ||
            stripos($email, '@garizo.com') !== false  ||
            stripos($email, '@garliclife.com') !== false  ||
            stripos($email, '@garrymccooey.com') !== false  ||
            stripos($email, '@gav0.com') !== false  ||
            stripos($email, '@gawab.com') !== false  ||
            stripos($email, '@gbcmail.win') !== false  ||
            stripos($email, '@gbmail.top') !== false  ||
            stripos($email, '@gcmail.top') !== false  ||
            stripos($email, '@gdmail.top') !== false  ||
            stripos($email, '@gedmail.win') !== false  ||
            stripos($email, '@geekforex.com') !== false  ||
            stripos($email, '@geew.ru') !== false  ||
            stripos($email, '@gehensiemirnichtaufdensack.de') !== false  ||
            stripos($email, '@geldwaschmaschine.de') !== false  ||
            stripos($email, '@gelitik.in') !== false  ||
            stripos($email, '@geludkita.tk') !== false  ||
            stripos($email, '@gen.uu.gl') !== false  ||
            stripos($email, '@genderfuck.net') !== false  ||
            stripos($email, '@geronra.com') !== false  ||
            stripos($email, '@geschent.biz') !== false  ||
            stripos($email, '@get1mail.com') !== false  ||
            stripos($email, '@get2mail.fr') !== false  ||
            stripos($email, '@get-mail.cf') !== false  ||
            stripos($email, '@get-mail.ga') !== false  ||
            stripos($email, '@get-mail.ml') !== false  ||
            stripos($email, '@get-mail.tk') !== false  ||
            stripos($email, '@get.pp.ua') !== false  ||
            stripos($email, '@getairmail.cf') !== false  ||
            stripos($email, '@getairmail.com') !== false  ||
            stripos($email, '@getairmail.ga') !== false  ||
            stripos($email, '@getairmail.gq') !== false  ||
            stripos($email, '@getairmail.ml') !== false  ||
            stripos($email, '@getairmail.tk') !== false  ||
            stripos($email, '@geteit.com') !== false  ||
            stripos($email, '@getfun.men') !== false  ||
            stripos($email, '@getmails.eu') !== false  ||
            stripos($email, '@getnada.com') !== false  ||
            stripos($email, '@getnowtoday.cf') !== false  ||
            stripos($email, '@getonemail.com') !== false  ||
            stripos($email, '@getonemail.net') !== false  ||
            stripos($email, '@ghosttexter.de') !== false  ||
            stripos($email, '@giacmosuaviet.info') !== false  ||
            stripos($email, '@giaiphapmuasam.com') !== false  ||
            stripos($email, '@giantmail.de') !== false  ||
            stripos($email, '@gifto12.com') !== false  ||
            stripos($email, '@ginzi.be') !== false  ||
            stripos($email, '@ginzi.co.uk') !== false  ||
            stripos($email, '@ginzi.es') !== false  ||
            stripos($email, '@ginzi.net') !== false  ||
            stripos($email, '@ginzy.co.uk') !== false  ||
            stripos($email, '@ginzy.eu') !== false  ||
            stripos($email, '@girlmail.win') !== false  ||
            stripos($email, '@girlsindetention.com') !== false  ||
            stripos($email, '@girlsundertheinfluence.com') !== false  ||
            stripos($email, '@gishpuppy.com') !== false  ||
            stripos($email, '@giveh2o.info') !== false  ||
            stripos($email, '@givmail.com') !== false  ||
            stripos($email, '@giyam.com') !== false  ||
            stripos($email, '@glitch.sx') !== false  ||
            stripos($email, '@globaltouron.com') !== false  ||
            stripos($email, '@glubex.com') !== false  ||
            stripos($email, '@glucosegrin.com') !== false  ||
            stripos($email, '@gmal.com') !== false  ||
            stripos($email, '@gmatch.org') !== false  ||
            stripos($email, '@gmial.com') !== false  ||
            stripos($email, '@gmx1mail.top') !== false  ||
            stripos($email, '@gmx.fr.nf') !== false  ||
            stripos($email, '@gmxmail.top') !== false  ||
            stripos($email, '@gmxmail.win') !== false  ||
            stripos($email, '@gnctr-calgary.com') !== false  ||
            stripos($email, '@go2usa.info') !== false  ||
            stripos($email, '@go2vpn.net') !== false  ||
            stripos($email, '@goemailgo.com') !== false  ||
            stripos($email, '@golemico.com') !== false  ||
            stripos($email, '@gomail.in') !== false  ||
            stripos($email, '@gorillaswithdirtyarmpits.com') !== false  ||
            stripos($email, '@goround.info') !== false  ||
            stripos($email, '@gothere.biz') !== false  ||
            stripos($email, '@gotimes.xyz') !== false  ||
            stripos($email, '@gotmail.com') !== false  ||
            stripos($email, '@gotmail.net') !== false  ||
            stripos($email, '@gotmail.org') !== false  ||
            stripos($email, '@gowikibooks.com') !== false  ||
            stripos($email, '@gowikicampus.com') !== false  ||
            stripos($email, '@gowikicars.com') !== false  ||
            stripos($email, '@gowikifilms.com') !== false  ||
            stripos($email, '@gowikigames.com') !== false  ||
            stripos($email, '@gowikimusic.com') !== false  ||
            stripos($email, '@gowikinetwork.com') !== false  ||
            stripos($email, '@gowikitravel.com') !== false  ||
            stripos($email, '@gowikitv.com') !== false  ||
            stripos($email, '@grandmamail.com') !== false  ||
            stripos($email, '@grandmasmail.com') !== false  ||
            stripos($email, '@great-host.in') !== false  ||
            stripos($email, '@greenhousemail.com') !== false  ||
            stripos($email, '@greensloth.com') !== false  ||
            stripos($email, '@greggamel.com') !== false  ||
            stripos($email, '@greggamel.net') !== false  ||
            stripos($email, '@gregorsky.zone') !== false  ||
            stripos($email, '@gregorygamel.com') !== false  ||
            stripos($email, '@gregorygamel.net') !== false  ||
            stripos($email, '@grish.de') !== false  ||
            stripos($email, '@griuc.schule') !== false  ||
            stripos($email, '@grr.la') !== false  ||
            stripos($email, '@gs-arc.org') !== false  ||
            stripos($email, '@gsredcross.org') !== false  ||
            stripos($email, '@gsrv.co.uk') !== false  ||
            stripos($email, '@gsxstring.ga') !== false  ||
            stripos($email, '@gudanglowongan.com') !== false  ||
            stripos($email, '@guerillamail.biz') !== false  ||
            stripos($email, '@guerillamail.com') !== false  ||
            stripos($email, '@guerillamail.de') !== false  ||
            stripos($email, '@guerillamail.info') !== false  ||
            stripos($email, '@guerillamail.net') !== false  ||
            stripos($email, '@guerillamail.org') !== false  ||
            stripos($email, '@guerillamailblock.com') !== false  ||
            stripos($email, '@guerrillamail.biz') !== false  ||
            stripos($email, '@guerrillamail.com') !== false  ||
            stripos($email, '@guerrillamail.de') !== false  ||
            stripos($email, '@guerrillamail.info') !== false  ||
            stripos($email, '@guerrillamail.net') !== false  ||
            stripos($email, '@guerrillamail.org') !== false  ||
            stripos($email, '@guerrillamailblock.com') !== false  ||
            stripos($email, '@gustr.com') !== false  ||
            stripos($email, '@gxemail.men') !== false  ||
            stripos($email, '@gynzi.co.uk') !== false  ||
            stripos($email, '@gynzi.es') !== false  ||
            stripos($email, '@gynzy.at') !== false  ||
            stripos($email, '@gynzy.es') !== false  ||
            stripos($email, '@gynzy.eu') !== false  ||
            stripos($email, '@gynzy.gr') !== false  ||
            stripos($email, '@gynzy.info') !== false  ||
            stripos($email, '@gynzy.lt') !== false  ||
            stripos($email, '@gynzy.mobi') !== false  ||
            stripos($email, '@gynzy.pl') !== false  ||
            stripos($email, '@gynzy.ro') !== false  ||
            stripos($email, '@gynzy.sk') !== false  ||
            stripos($email, '@gzb.ro') !== false  ||
            stripos($email, '@h8s.org') !== false  ||
            stripos($email, '@h.mintemail.com') !== false  ||
            stripos($email, '@habitue.net') !== false  ||
            stripos($email, '@hacccc.com') !== false  ||
            stripos($email, '@hackersquad.tk') !== false  ||
            stripos($email, '@hackthatbit.ch') !== false  ||
            stripos($email, '@hahawrong.com') !== false  ||
            stripos($email, '@haida-edu.cn') !== false  ||
            stripos($email, '@haltospam.com') !== false  ||
            stripos($email, '@hangxomcuatoilatotoro.ml') !== false  ||
            stripos($email, '@harakirimail.com') !== false  ||
            stripos($email, '@haribu.com') !== false  ||
            stripos($email, '@hartbot.de') !== false  ||
            stripos($email, '@hasanmail.ml') !== false  ||
            stripos($email, '@hat-geld.de') !== false  ||
            stripos($email, '@hatespam.org') !== false  ||
            stripos($email, '@hawrong.com') !== false  ||
            stripos($email, '@haydoo.com') !== false  ||
            stripos($email, '@hazelnut4u.com') !== false  ||
            stripos($email, '@hazelnuts4u.com') !== false  ||
            stripos($email, '@hazmatshipping.org') !== false  ||
            stripos($email, '@hbdya.info') !== false  ||
            stripos($email, '@hccmail.win') !== false  ||
            stripos($email, '@headstrong.de') !== false  ||
            stripos($email, '@heathenhammer.com') !== false  ||
            stripos($email, '@heathenhero.com') !== false  ||
            stripos($email, '@hecat.es') !== false  ||
            stripos($email, '@hellodream.mobi') !== false  ||
            stripos($email, '@helloricky.com') !== false  ||
            stripos($email, '@helpinghandtaxcenter.org') !== false  ||
            stripos($email, '@herp.in') !== false  ||
            stripos($email, '@herpderp.nl') !== false  ||
            stripos($email, '@hezll.com') !== false  ||
            stripos($email, '@hi5.si') !== false  ||
            stripos($email, '@hiddentragedy.com') !== false  ||
            stripos($email, '@hidebox.org') !== false  ||
            stripos($email, '@hidemail.de') !== false  ||
            stripos($email, '@hidemail.pro') !== false  ||
            stripos($email, '@hidemail.us') !== false  ||
            stripos($email, '@hidzz.com') !== false  ||
            stripos($email, '@highbros.org') !== false  ||
            stripos($email, '@hmail.us') !== false  ||
            stripos($email, '@hmamail.com') !== false  ||
            stripos($email, '@hmh.ro') !== false  ||
            stripos($email, '@hoanggiaanh.com') !== false  ||
            stripos($email, '@hoanglong.tech') !== false  ||
            stripos($email, '@hochsitze.com') !== false  ||
            stripos($email, '@hola.org') !== false  ||
            stripos($email, '@holl.ga') !== false  ||
            stripos($email, '@honor-8.com') !== false  ||
            stripos($email, '@hoo.com') !== false  ||
            stripos($email, '@hopemail.biz') !== false  ||
            stripos($email, '@hornyalwary.top') !== false  ||
            stripos($email, '@hostlaba.com') !== false  ||
            stripos($email, '@hot-mail.cf') !== false  ||
            stripos($email, '@hot-mail.ga') !== false  ||
            stripos($email, '@hot-mail.gq') !== false  ||
            stripos($email, '@hot-mail.ml') !== false  ||
            stripos($email, '@hot-mail.tk') !== false  ||
            stripos($email, '@hotmai.com') !== false  ||
            stripos($email, '@hotmessage.info') !== false  ||
            stripos($email, '@hotmial.com') !== false  ||
            stripos($email, '@hotpop.com') !== false  ||
            stripos($email, '@hotprice.co') !== false  ||
            stripos($email, '@housat.com') !== false  ||
            stripos($email, '@hpc.tw') !== false  ||
            stripos($email, '@hs.vc') !== false  ||
            stripos($email, '@ht.cx') !== false  ||
            stripos($email, '@huangniu8.com') !== false  ||
            stripos($email, '@hukkmu.tk') !== false  ||
            stripos($email, '@hulapla.de') !== false  ||
            stripos($email, '@hulksales.com') !== false  ||
            stripos($email, '@humaility.com') !== false  ||
            stripos($email, '@humn.ws.gy') !== false  ||
            stripos($email, '@hungpackage.com') !== false  ||
            stripos($email, '@hushmail.cf') !== false  ||
            stripos($email, '@huskion.net') !== false  ||
            stripos($email, '@hvastudiesucces.nl') !== false  ||
            stripos($email, '@hwsye.net') !== false  ||
            stripos($email, '@i6.cloudns.cc') !== false  ||
            stripos($email, '@i6.cloudns.cx') !== false  ||
            stripos($email, '@i-phone.nut.cc') !== false  ||
            stripos($email, '@iaoss.com') !== false  ||
            stripos($email, '@ibnuh.bz') !== false  ||
            stripos($email, '@ibsats.com') !== false  ||
            stripos($email, '@icantbelieveineedtoexplainthisshit.com') !== false  ||
            stripos($email, '@ichigo.me') !== false  ||
            stripos($email, '@icx.in') !== false  ||
            stripos($email, '@icx.ro') !== false  ||
            stripos($email, '@ieatspam.eu') !== false  ||
            stripos($email, '@ieatspam.info') !== false  ||
            stripos($email, '@ieh-mail.de') !== false  ||
            stripos($email, '@ige.es') !== false  ||
            stripos($email, '@ignoremail.com') !== false  ||
            stripos($email, '@ihateyoualot.info') !== false  ||
            stripos($email, '@ihazspam.ca') !== false  ||
            stripos($email, '@iheartspam.org') !== false  ||
            stripos($email, '@ikbenspamvrij.nl') !== false  ||
            stripos($email, '@illistnoise.com') !== false  ||
            stripos($email, '@ilovespam.com') !== false  ||
            stripos($email, '@imails.info') !== false  ||
            stripos($email, '@imgof.com') !== false  ||
            stripos($email, '@imgv.de') !== false  ||
            stripos($email, '@imstations.com') !== false  ||
            stripos($email, '@imul.info') !== false  ||
            stripos($email, '@inbax.tk') !== false  ||
            stripos($email, '@inbound.plus') !== false  ||
            stripos($email, '@inbox2.info') !== false  ||
            stripos($email, '@inbox.si') !== false  ||
            stripos($email, '@inboxalias.com') !== false  ||
            stripos($email, '@inboxbear.com') !== false  ||
            stripos($email, '@inboxclean.com') !== false  ||
            stripos($email, '@inboxclean.org') !== false  ||
            stripos($email, '@inboxdesign.me') !== false  ||
            stripos($email, '@inboxed.im') !== false  ||
            stripos($email, '@inboxed.pw') !== false  ||
            stripos($email, '@inboxproxy.com') !== false  ||
            stripos($email, '@inboxstore.me') !== false  ||
            stripos($email, '@inclusiveprogress.com') !== false  ||
            stripos($email, '@incognitomail.com') !== false  ||
            stripos($email, '@incognitomail.net') !== false  ||
            stripos($email, '@incognitomail.org') !== false  ||
            stripos($email, '@incq.com') !== false  ||
            stripos($email, '@ind.st') !== false  ||
            stripos($email, '@indieclad.com') !== false  ||
            stripos($email, '@indirect.ws') !== false  ||
            stripos($email, '@indomaed.pw') !== false  ||
            stripos($email, '@indomina.cf') !== false  ||
            stripos($email, '@indoserver.stream') !== false  ||
            stripos($email, '@indosukses.press') !== false  ||
            stripos($email, '@ineec.net') !== false  ||
            stripos($email, '@infocom.zp.ua') !== false  ||
            stripos($email, '@inggo.org') !== false  ||
            stripos($email, '@inoutmail.de') !== false  ||
            stripos($email, '@inoutmail.eu') !== false  ||
            stripos($email, '@inoutmail.info') !== false  ||
            stripos($email, '@inoutmail.net') !== false  ||
            stripos($email, '@insanumingeniumhomebrew.com') !== false  ||
            stripos($email, '@insorg-mail.info') !== false  ||
            stripos($email, '@instance-email.com') !== false  ||
            stripos($email, '@instant-mail.de') !== false  ||
            stripos($email, '@instantblingmail.info') !== false  ||
            stripos($email, '@instantemailaddress.com') !== false  ||
            stripos($email, '@instantmail.fr') !== false  ||
            stripos($email, '@internetoftags.com') !== false  ||
            stripos($email, '@interstats.org') !== false  ||
            stripos($email, '@intersteller.com') !== false  ||
            stripos($email, '@investore.co') !== false  ||
            stripos($email, '@iozak.com') !== false  ||
            stripos($email, '@ip4.pp.ua') !== false  ||
            stripos($email, '@ip6.li') !== false  ||
            stripos($email, '@ip6.pp.ua') !== false  ||
            stripos($email, '@ipoo.org') !== false  ||
            stripos($email, '@ippandansei.tk') !== false  ||
            stripos($email, '@ipsur.org') !== false  ||
            stripos($email, '@irabops.com') !== false  ||
            stripos($email, '@irc.so') !== false  ||
            stripos($email, '@irish2me.com') !== false  ||
            stripos($email, '@iroid.com') !== false  ||
            stripos($email, '@ironiebehindert.de') !== false  ||
            stripos($email, '@irssi.tv') !== false  ||
            stripos($email, '@is.af') !== false  ||
            stripos($email, '@isdaq.com') !== false  ||
            stripos($email, '@isosq.com') !== false  ||
            stripos($email, '@istii.ro') !== false  ||
            stripos($email, '@isukrainestillacountry.com') !== false  ||
            stripos($email, '@it7.ovh') !== false  ||
            stripos($email, '@italy-mail.com') !== false  ||
            stripos($email, '@itunesgiftcodegenerator.com') !== false  ||
            stripos($email, '@iuemail.men') !== false  ||
            stripos($email, '@iwi.net') !== false  ||
            stripos($email, '@ixx.io') !== false  ||
            stripos($email, '@j7.cloudns.cx') !== false  ||
            stripos($email, '@j8k2.usa.cc') !== false  ||
            stripos($email, '@j-p.us') !== false  ||
            stripos($email, '@jafps.com') !== false  ||
            stripos($email, '@jajxz.com') !== false  ||
            stripos($email, '@jamesbond.flu.cc') !== false  ||
            stripos($email, '@jamesbond.igg.biz') !== false  ||
            stripos($email, '@jamesbond.nut.cc') !== false  ||
            stripos($email, '@jamesbond.usa.cc') !== false  ||
            stripos($email, '@janproz.com') !== false  ||
            stripos($email, '@jdasdhj.gq') !== false  ||
            stripos($email, '@jdmadventures.com') !== false  ||
            stripos($email, '@jdz.ro') !== false  ||
            stripos($email, '@jeie.igg.biz') !== false  ||
            stripos($email, '@jellow.ml') !== false  ||
            stripos($email, '@jellyrolls.com') !== false  ||
            stripos($email, '@jet-renovation.fr') !== false  ||
            stripos($email, '@jetable.com') !== false  ||
            stripos($email, '@jetable.fr.nf') !== false  ||
            stripos($email, '@jetable.net') !== false  ||
            stripos($email, '@jetable.org') !== false  ||
            stripos($email, '@jetable.pp.ua') !== false  ||
            stripos($email, '@jilossesq.com') !== false  ||
            stripos($email, '@jmail.ovh') !== false  ||
            stripos($email, '@jmail.ro') !== false  ||
            stripos($email, '@jnxjn.com') !== false  ||
            stripos($email, '@jobbikszimpatizans.hu') !== false  ||
            stripos($email, '@jobposts.net') !== false  ||
            stripos($email, '@jobs-to-be-done.net') !== false  ||
            stripos($email, '@joelpet.com') !== false  ||
            stripos($email, '@joetestalot.com') !== false  ||
            stripos($email, '@jopho.com') !== false  ||
            stripos($email, '@joseihorumon.info') !== false  ||
            stripos($email, '@josse.ltd') !== false  ||
            stripos($email, '@jourrapide.com') !== false  ||
            stripos($email, '@jpco.org') !== false  ||
            stripos($email, '@jsrsolutions.com') !== false  ||
            stripos($email, '@jumonji.tk') !== false  ||
            stripos($email, '@jungkamushukum.com') !== false  ||
            stripos($email, '@junk1e.com') !== false  ||
            stripos($email, '@junk.to') !== false  ||
            stripos($email, '@junkmail.com') !== false  ||
            stripos($email, '@junkmail.ga') !== false  ||
            stripos($email, '@junkmail.gq') !== false  ||
            stripos($email, '@justemail.ml') !== false  ||
            stripos($email, '@juyouxi.com') !== false  ||
            stripos($email, '@jwork.ru') !== false  ||
            stripos($email, '@kademen.com') !== false  ||
            stripos($email, '@kadokawa.cf') !== false  ||
            stripos($email, '@kadokawa.ga') !== false  ||
            stripos($email, '@kadokawa.gq') !== false  ||
            stripos($email, '@kadokawa.ml') !== false  ||
            stripos($email, '@kadokawa.tk') !== false  ||
            stripos($email, '@kakadua.net') !== false  ||
            stripos($email, '@kalapi.org') !== false  ||
            stripos($email, '@kamsg.com') !== false  ||
            stripos($email, '@kaovo.com') !== false  ||
            stripos($email, '@kappala.info') !== false  ||
            stripos($email, '@karatraman.ml') !== false  ||
            stripos($email, '@kariplan.com') !== false  ||
            stripos($email, '@kartvelo.com') !== false  ||
            stripos($email, '@kasmail.com') !== false  ||
            stripos($email, '@kaspop.com') !== false  ||
            stripos($email, '@katztube.com') !== false  ||
            stripos($email, '@kazelink.ml') !== false  ||
            stripos($email, '@kcrw.de') !== false  ||
            stripos($email, '@keepmymail.com') !== false  ||
            stripos($email, '@keinhirn.de') !== false  ||
            stripos($email, '@keipino.de') !== false  ||
            stripos($email, '@kekita.com') !== false  ||
            stripos($email, '@kemptvillebaseball.com') !== false  ||
            stripos($email, '@kennedy808.com') !== false  ||
            stripos($email, '@kiani.com') !== false  ||
            stripos($email, '@killmail.com') !== false  ||
            stripos($email, '@killmail.net') !== false  ||
            stripos($email, '@kimsdisk.com') !== false  ||
            stripos($email, '@kingsq.ga') !== false  ||
            stripos($email, '@kiois.com') !== false  ||
            stripos($email, '@kir.ch.tc') !== false  ||
            stripos($email, '@kiryubox.cu.cc') !== false  ||
            stripos($email, '@kismail.ru') !== false  ||
            stripos($email, '@kisstwink.com') !== false  ||
            stripos($email, '@kitnastar.com') !== false  ||
            stripos($email, '@kiustdz.com') !== false  ||
            stripos($email, '@klassmaster.com') !== false  ||
            stripos($email, '@klassmaster.net') !== false  ||
            stripos($email, '@klick-tipp.us') !== false  ||
            stripos($email, '@klipschx12.com') !== false  ||
            stripos($email, '@kloap.com') !== false  ||
            stripos($email, '@kludgemush.com') !== false  ||
            stripos($email, '@klzlk.com') !== false  ||
            stripos($email, '@kmhow.com') !== false  ||
            stripos($email, '@knol-power.nl') !== false  ||
            stripos($email, '@kodorsex.cf') !== false  ||
            stripos($email, '@kommunity.biz') !== false  ||
            stripos($email, '@kon42.com') !== false  ||
            stripos($email, '@kook.ml') !== false  ||
            stripos($email, '@kopagas.com') !== false  ||
            stripos($email, '@kopaka.net') !== false  ||
            stripos($email, '@kosmetik-obatkuat.com') !== false  ||
            stripos($email, '@kostenlosemailadresse.de') !== false  ||
            stripos($email, '@koszmail.pl') !== false  ||
            stripos($email, '@krd.ag') !== false  ||
            stripos($email, '@krsw.tk') !== false  ||
            stripos($email, '@krypton.tk') !== false  ||
            stripos($email, '@ks87.igg.biz') !== false  ||
            stripos($email, '@ks87.usa.cc') !== false  ||
            stripos($email, '@ksmtrck.tk') !== false  ||
            stripos($email, '@kuhrap.com') !== false  ||
            stripos($email, '@kulturbetrieb.info') !== false  ||
            stripos($email, '@kurzepost.de') !== false  ||
            stripos($email, '@kutakbisajauhjauh.ga') !== false  ||
            stripos($email, '@kwift.net') !== false  ||
            stripos($email, '@kwilco.net') !== false  ||
            stripos($email, '@kyal.pl') !== false  ||
            stripos($email, '@kyois.com') !== false  ||
            stripos($email, '@l33r.eu') !== false  ||
            stripos($email, '@l-c-a.us') !== false  ||
            stripos($email, '@labetteraverouge.at') !== false  ||
            stripos($email, '@lacedmail.com') !== false  ||
            stripos($email, '@lackmail.net') !== false  ||
            stripos($email, '@lackmail.ru') !== false  ||
            stripos($email, '@lacto.info') !== false  ||
            stripos($email, '@lags.us') !== false  ||
            stripos($email, '@lain.ch') !== false  ||
            stripos($email, '@lak.pp.ua') !== false  ||
            stripos($email, '@lakelivingstonrealestate.com') !== false  ||
            stripos($email, '@landmail.co') !== false  ||
            stripos($email, '@laoeq.com') !== false  ||
            stripos($email, '@last-chance.pro') !== false  ||
            stripos($email, '@lastmail.co') !== false  ||
            stripos($email, '@lastmail.com') !== false  ||
            stripos($email, '@lawlita.com') !== false  ||
            stripos($email, '@lazyinbox.com') !== false  ||
            stripos($email, '@lazyinbox.us') !== false  ||
            stripos($email, '@ldaho.net') !== false  ||
            stripos($email, '@ldop.com') !== false  ||
            stripos($email, '@ldtp.com') !== false  ||
            stripos($email, '@lee.mx') !== false  ||
            stripos($email, '@leeching.net') !== false  ||
            stripos($email, '@lellno.gq') !== false  ||
            stripos($email, '@lenovog4.com') !== false  ||
            stripos($email, '@letmeinonthis.com') !== false  ||
            stripos($email, '@letthemeatspam.com') !== false  ||
            stripos($email, '@level3.flu.cc') !== false  ||
            stripos($email, '@level3.igg.biz') !== false  ||
            stripos($email, '@level3.nut.cc') !== false  ||
            stripos($email, '@level3.usa.cc') !== false  ||
            stripos($email, '@lez.se') !== false  ||
            stripos($email, '@lgxscreen.com') !== false  ||
            stripos($email, '@lhsdv.com') !== false  ||
            stripos($email, '@liamcyrus.com') !== false  ||
            stripos($email, '@lifebyfood.com') !== false  ||
            stripos($email, '@lifetimefriends.info') !== false  ||
            stripos($email, '@lifetotech.com') !== false  ||
            stripos($email, '@ligsb.com') !== false  ||
            stripos($email, '@lillemap.net') !== false  ||
            stripos($email, '@lilo.me') !== false  ||
            stripos($email, '@lindenbaumjapan.com') !== false  ||
            stripos($email, '@link2mail.net') !== false  ||
            stripos($email, '@linkedintuts2016.pw') !== false  ||
            stripos($email, '@linuxmail.so') !== false  ||
            stripos($email, '@litedrop.com') !== false  ||
            stripos($email, '@lkgn.se') !== false  ||
            stripos($email, '@llogin.ru') !== false  ||
            stripos($email, '@loadby.us') !== false  ||
            stripos($email, '@loan101.pro') !== false  ||
            stripos($email, '@loaoa.com') !== false  ||
            stripos($email, '@loapq.com') !== false  ||
            stripos($email, '@locanto1.club') !== false  ||
            stripos($email, '@locantofuck.top') !== false  ||
            stripos($email, '@locantowsite.club') !== false  ||
            stripos($email, '@locomodev.net') !== false  ||
            stripos($email, '@login-email.cf') !== false  ||
            stripos($email, '@login-email.ga') !== false  ||
            stripos($email, '@login-email.ml') !== false  ||
            stripos($email, '@login-email.tk') !== false  ||
            stripos($email, '@logular.com') !== false  ||
            stripos($email, '@loh.pp.ua') !== false  ||
            stripos($email, '@loin.in') !== false  ||
            stripos($email, '@lol.ovpn.to') !== false  ||
            stripos($email, '@lolfreak.net') !== false  ||
            stripos($email, '@lolmail.biz') !== false  ||
            stripos($email, '@lookugly.com') !== false  ||
            stripos($email, '@lopl.co.cc') !== false  ||
            stripos($email, '@lordsofts.com') !== false  ||
            stripos($email, '@lortemail.dk') !== false  ||
            stripos($email, '@losemymail.com') !== false  ||
            stripos($email, '@lovemeet.faith') !== false  ||
            stripos($email, '@lovemeleaveme.com') !== false  ||
            stripos($email, '@lpfmgmtltd.com') !== false  ||
            stripos($email, '@lr7.us') !== false  ||
            stripos($email, '@lr78.com') !== false  ||
            stripos($email, '@lroid.com') !== false  ||
            stripos($email, '@lru.me') !== false  ||
            stripos($email, '@luckymail.org') !== false  ||
            stripos($email, '@lukecarriere.com') !== false  ||
            stripos($email, '@lukemail.info') !== false  ||
            stripos($email, '@lukop.dk') !== false  ||
            stripos($email, '@luv2.us') !== false  ||
            stripos($email, '@lyfestylecreditsolutions.com') !== false  ||
            stripos($email, '@lzoaq.com') !== false  ||
            stripos($email, '@m4ilweb.info') !== false  ||
            stripos($email, '@m5s.flu.cc') !== false  ||
            stripos($email, '@m5s.igg.biz') !== false  ||
            stripos($email, '@m5s.nut.cc') !== false  ||
            stripos($email, '@m21.cc') !== false  ||
            stripos($email, '@maboard.com') !== false  ||
            stripos($email, '@macr2.com') !== false  ||
            stripos($email, '@macromaid.com') !== false  ||
            stripos($email, '@macromice.info') !== false  ||
            stripos($email, '@magamail.com') !== false  ||
            stripos($email, '@maggotymeat.ga') !== false  ||
            stripos($email, '@magicbox.ro') !== false  ||
            stripos($email, '@maidlow.info') !== false  ||
            stripos($email, '@mail0.ga') !== false  ||
            stripos($email, '@mail1a.de') !== false  ||
            stripos($email, '@mail2rss.org') !== false  ||
            stripos($email, '@mail4-us.org') !== false  ||
            stripos($email, '@mail4trash.com') !== false  ||
            stripos($email, '@mail21.cc') !== false  ||
            stripos($email, '@mail22.club') !== false  ||
            stripos($email, '@mail72.com') !== false  ||
            stripos($email, '@mail114.net') !== false  ||
            stripos($email, '@mail333.com') !== false  ||
            stripos($email, '@mail666.ru') !== false  ||
            stripos($email, '@mail707.com') !== false  ||
            stripos($email, '@mail-easy.fr') !== false  ||
            stripos($email, '@mail-filter.com') !== false  ||
            stripos($email, '@mail-owl.com') !== false  ||
            stripos($email, '@mail-temporaire.com') !== false  ||
            stripos($email, '@mail-temporaire.fr') !== false  ||
            stripos($email, '@mail.by') !== false  ||
            stripos($email, '@mail.mezimages.net') !== false  ||
            stripos($email, '@mail.qmeta.net') !== false  ||
            stripos($email, '@mail.wtf') !== false  ||
            stripos($email, '@mail.zp.ua') !== false  ||
            stripos($email, '@mailback.com') !== false  ||
            stripos($email, '@mailbidon.com') !== false  ||
            stripos($email, '@mailbiz.biz') !== false  ||
            stripos($email, '@mailblocks.com') !== false  ||
            stripos($email, '@mailbox52.ga') !== false  ||
            stripos($email, '@mailbox80.biz') !== false  ||
            stripos($email, '@mailbox82.biz') !== false  ||
            stripos($email, '@mailbox92.biz') !== false  ||
            stripos($email, '@mailbucket.org') !== false  ||
            stripos($email, '@mailcat.biz') !== false  ||
            stripos($email, '@mailcatch.com') !== false  ||
            stripos($email, '@mailchop.com') !== false  ||
            stripos($email, '@mailcker.com') !== false  ||
            stripos($email, '@mailde.de') !== false  ||
            stripos($email, '@mailde.info') !== false  ||
            stripos($email, '@maildrop.cc') !== false  ||
            stripos($email, '@maildrop.cf') !== false  ||
            stripos($email, '@maildrop.ga') !== false  ||
            stripos($email, '@maildrop.gq') !== false  ||
            stripos($email, '@maildrop.ml') !== false  ||
            stripos($email, '@maildu.de') !== false  ||
            stripos($email, '@maildx.com') !== false  ||
            stripos($email, '@maileater.com') !== false  ||
            stripos($email, '@mailed.in') !== false  ||
            stripos($email, '@mailed.ro') !== false  ||
            stripos($email, '@maileimer.de') !== false  ||
            stripos($email, '@maileme101.com') !== false  ||
            stripos($email, '@mailexpire.com') !== false  ||
            stripos($email, '@mailf5.com') !== false  ||
            stripos($email, '@mailfa.tk') !== false  ||
            stripos($email, '@mailfall.com') !== false  ||
            stripos($email, '@mailforspam.com') !== false  ||
            stripos($email, '@mailfree.ga') !== false  ||
            stripos($email, '@mailfree.gq') !== false  ||
            stripos($email, '@mailfree.ml') !== false  ||
            stripos($email, '@mailfreeonline.com') !== false  ||
            stripos($email, '@mailfs.com') !== false  ||
            stripos($email, '@mailgov.info') !== false  ||
            stripos($email, '@mailguard.me') !== false  ||
            stripos($email, '@mailgutter.com') !== false  ||
            stripos($email, '@mailhazard.com') !== false  ||
            stripos($email, '@mailhazard.us') !== false  ||
            stripos($email, '@mailhz.me') !== false  ||
            stripos($email, '@mailimate.com') !== false  ||
            stripos($email, '@mailin8r.com') !== false  ||
            stripos($email, '@mailinatar.com') !== false  ||
            stripos($email, '@mailinater.com') !== false  ||
            stripos($email, '@mailinator') !== false  ||
            stripos($email, '@mailinator0.com') !== false  ||
            stripos($email, '@mailinator1.com') !== false  ||
            stripos($email, '@mailinator2.com') !== false  ||
            stripos($email, '@mailinator2.net') !== false  ||
            stripos($email, '@mailinator3.com') !== false  ||
            stripos($email, '@mailinator4.com') !== false  ||
            stripos($email, '@mailinator5.com') !== false  ||
            stripos($email, '@mailinator6.com') !== false  ||
            stripos($email, '@mailinator7.com') !== false  ||
            stripos($email, '@mailinator8.com') !== false  ||
            stripos($email, '@mailinator9.com') !== false  ||
            stripos($email, '@mailinator.co.uk') !== false  ||
            stripos($email, '@mailinator.com') !== false  ||
            stripos($email, '@mailinator.gq') !== false  ||
            stripos($email, '@mailinator.info') !== false  ||
            stripos($email, '@mailinator.net') !== false  ||
            stripos($email, '@mailinator.org') !== false  ||
            stripos($email, '@mailinator.us') !== false  ||
            stripos($email, '@mailinator.usa.cc') !== false  ||
            stripos($email, '@mailincubator.com') !== false  ||
            stripos($email, '@mailismagic.com') !== false  ||
            stripos($email, '@mailita.tk') !== false  ||
            stripos($email, '@mailjunk.cf') !== false  ||
            stripos($email, '@mailjunk.ga') !== false  ||
            stripos($email, '@mailjunk.gq') !== false  ||
            stripos($email, '@mailjunk.ml') !== false  ||
            stripos($email, '@mailjunk.tk') !== false  ||
            stripos($email, '@mailmate.com') !== false  ||
            stripos($email, '@mailme24.com') !== false  ||
            stripos($email, '@mailme.gq') !== false  ||
            stripos($email, '@mailme.ir') !== false  ||
            stripos($email, '@mailme.lv') !== false  ||
            stripos($email, '@mailmetrash.com') !== false  ||
            stripos($email, '@mailmoat.com') !== false  ||
            stripos($email, '@mailmoth.com') !== false  ||
            stripos($email, '@mailms.com') !== false  ||
            stripos($email, '@mailna.biz') !== false  ||
            stripos($email, '@mailna.co') !== false  ||
            stripos($email, '@mailna.in') !== false  ||
            stripos($email, '@mailna.me') !== false  ||
            stripos($email, '@mailnator.com') !== false  ||
            stripos($email, '@mailnesia.com') !== false  ||
            stripos($email, '@mailnull.com') !== false  ||
            stripos($email, '@mailonaut.com') !== false  ||
            stripos($email, '@mailorc.com') !== false  ||
            stripos($email, '@mailorg.org') !== false  ||
            stripos($email, '@mailpick.biz') !== false  ||
            stripos($email, '@mailpooch.com') !== false  ||
            stripos($email, '@mailproxsy.com') !== false  ||
            stripos($email, '@mailquack.com') !== false  ||
            stripos($email, '@mailrock.biz') !== false  ||
            stripos($email, '@mailsac.com') !== false  ||
            stripos($email, '@mailscrap.com') !== false  ||
            stripos($email, '@mailseal.de') !== false  ||
            stripos($email, '@mailshell.com') !== false  ||
            stripos($email, '@mailsiphon.com') !== false  ||
            stripos($email, '@mailslapping.com') !== false  ||
            stripos($email, '@mailslite.com') !== false  ||
            stripos($email, '@mailsucker.net') !== false  ||
            stripos($email, '@mailtechx.com') !== false  ||
            stripos($email, '@mailtemp.info') !== false  ||
            stripos($email, '@mailtemporaire.com') !== false  ||
            stripos($email, '@mailtemporaire.fr') !== false  ||
            stripos($email, '@mailto.space') !== false  ||
            stripos($email, '@mailtome.de') !== false  ||
            stripos($email, '@mailtothis.com') !== false  ||
            stripos($email, '@mailtraps.com') !== false  ||
            stripos($email, '@mailtrash.net') !== false  ||
            stripos($email, '@mailtrix.net') !== false  ||
            stripos($email, '@mailtv.net') !== false  ||
            stripos($email, '@mailtv.tv') !== false  ||
            stripos($email, '@mailzi.ru') !== false  ||
            stripos($email, '@mailzilla.com') !== false  ||
            stripos($email, '@mailzilla.org') !== false  ||
            stripos($email, '@mailzilla.orgmbx.cc') !== false  ||
            stripos($email, '@mainerfolg.info') !== false  ||
            stripos($email, '@makemenaughty.club') !== false  ||
            stripos($email, '@makemetheking.com') !== false  ||
            stripos($email, '@malahov.de') !== false  ||
            stripos($email, '@malayalamdtp.com') !== false  ||
            stripos($email, '@malove.site') !== false  ||
            stripos($email, '@mandraghen.cf') !== false  ||
            stripos($email, '@manifestgenerator.com') !== false  ||
            stripos($email, '@mankyrecords.com') !== false  ||
            stripos($email, '@mansiondev.com') !== false  ||
            stripos($email, '@manybrain.com') !== false  ||
            stripos($email, '@markmurfin.com') !== false  ||
            stripos($email, '@masonline.info') !== false  ||
            stripos($email, '@maswae.world') !== false  ||
            stripos($email, '@matamuasu.ga') !== false  ||
            stripos($email, '@matchpol.net') !== false  ||
            stripos($email, '@mbox.0x01.tk') !== false  ||
            stripos($email, '@mbx.cc') !== false  ||
            stripos($email, '@mcache.net') !== false  ||
            stripos($email, '@mciek.com') !== false  ||
            stripos($email, '@mdhc.tk') !== false  ||
            stripos($email, '@mebelnu.info') !== false  ||
            stripos($email, '@mechanicalresumes.com') !== false  ||
            stripos($email, '@meepsheep.eu') !== false  ||
            stripos($email, '@mega.zik.dj') !== false  ||
            stripos($email, '@meinspamschutz.de') !== false  ||
            stripos($email, '@meltmail.com') !== false  ||
            stripos($email, '@mendoanmail.club') !== false  ||
            stripos($email, '@merry.pink') !== false  ||
            stripos($email, '@messagebeamer.de') !== false  ||
            stripos($email, '@messwiththebestdielikethe.rest') !== false  ||
            stripos($email, '@mezimages.net') !== false  ||
            stripos($email, '@mfsa.info') !== false  ||
            stripos($email, '@mfsa.ru') !== false  ||
            stripos($email, '@miaferrari.com') !== false  ||
            stripos($email, '@miauj.com') !== false  ||
            stripos($email, '@midcoastcustoms.com') !== false  ||
            stripos($email, '@midcoastcustoms.net') !== false  ||
            stripos($email, '@midcoastsolutions.com') !== false  ||
            stripos($email, '@midcoastsolutions.net') !== false  ||
            stripos($email, '@midlertidig.com') !== false  ||
            stripos($email, '@midlertidig.net') !== false  ||
            stripos($email, '@midlertidig.org') !== false  ||
            stripos($email, '@mierdamail.com') !== false  ||
            stripos($email, '@migmail.net') !== false  ||
            stripos($email, '@migmail.pl') !== false  ||
            stripos($email, '@migumail.com') !== false  ||
            stripos($email, '@mihep.com') !== false  ||
            stripos($email, '@mijnhva.nl') !== false  ||
            stripos($email, '@ministry-of-silly-walks.de') !== false  ||
            stripos($email, '@minsmail.com') !== false  ||
            stripos($email, '@mintemail.com') !== false  ||
            stripos($email, '@misterpinball.de') !== false  ||
            stripos($email, '@mji.ro') !== false  ||
            stripos($email, '@mjukglass.nu') !== false  ||
            stripos($email, '@mkpfilm.com') !== false  ||
            stripos($email, '@ml8.ca') !== false  ||
            stripos($email, '@mlx.ooo') !== false  ||
            stripos($email, '@mm5.se') !== false  ||
            stripos($email, '@mm.my') !== false  ||
            stripos($email, '@mnode.me') !== false  ||
            stripos($email, '@moakt.cc') !== false  ||
            stripos($email, '@moakt.co') !== false  ||
            stripos($email, '@moakt.com') !== false  ||
            stripos($email, '@moakt.ws') !== false  ||
            stripos($email, '@mobileninja.co.uk') !== false  ||
            stripos($email, '@mobilevpn.top') !== false  ||
            stripos($email, '@moburl.com') !== false  ||
            stripos($email, '@mockmyid.com') !== false  ||
            stripos($email, '@moeri.org') !== false  ||
            stripos($email, '@mohmal.com') !== false  ||
            stripos($email, '@mohmal.im') !== false  ||
            stripos($email, '@mohmal.in') !== false  ||
            stripos($email, '@mohmal.tech') !== false  ||
            stripos($email, '@momentics.ru') !== false  ||
            stripos($email, '@monachat.tk') !== false  ||
            stripos($email, '@monadi.ml') !== false  ||
            stripos($email, '@moncourrier.fr.nf') !== false  ||
            stripos($email, '@monemail.fr.nf') !== false  ||
            stripos($email, '@moneypipe.net') !== false  ||
            stripos($email, '@monmail.fr.nf') !== false  ||
            stripos($email, '@monumentmail.com') !== false  ||
            stripos($email, '@moonwake.com') !== false  ||
            stripos($email, '@moot.es') !== false  ||
            stripos($email, '@moreawesomethanyou.com') !== false  ||
            stripos($email, '@moreorcs.com') !== false  ||
            stripos($email, '@morriesworld.ml') !== false  ||
            stripos($email, '@morsin.com') !== false  ||
            stripos($email, '@motique.de') !== false  ||
            stripos($email, '@mountainregionallibrary.net') !== false  ||
            stripos($email, '@mox.pp.ua') !== false  ||
            stripos($email, '@moza.pl') !== false  ||
            stripos($email, '@mozej.com') !== false  ||
            stripos($email, '@mp-j.ga') !== false  ||
            stripos($email, '@mp-j.igg.biz') !== false  ||
            stripos($email, '@mp.igg.biz') !== false  ||
            stripos($email, '@mr24.co') !== false  ||
            stripos($email, '@msa.minsmail.com') !== false  ||
            stripos($email, '@msgos.com') !== false  ||
            stripos($email, '@mspeciosa.com') !== false  ||
            stripos($email, '@msrc.ml') !== false  ||
            stripos($email, '@mswork.ru') !== false  ||
            stripos($email, '@msxd.com') !== false  ||
            stripos($email, '@mt2009.com') !== false  ||
            stripos($email, '@mt2014.com') !== false  ||
            stripos($email, '@mt2015.com') !== false  ||
            stripos($email, '@mtmdev.com') !== false  ||
            stripos($email, '@muathegame.com') !== false  ||
            stripos($email, '@muchomail.com') !== false  ||
            stripos($email, '@mucincanon.com') !== false  ||
            stripos($email, '@munoubengoshi.gq') !== false  ||
            stripos($email, '@mutant.me') !== false  ||
            stripos($email, '@mvrht.com') !== false  ||
            stripos($email, '@mvrht.net') !== false  ||
            stripos($email, '@mwarner.org') !== false  ||
            stripos($email, '@mx0.wwwnew.eu') !== false  ||
            stripos($email, '@mxfuel.com') !== false  ||
            stripos($email, '@my10minutemail.com') !== false  ||
            stripos($email, '@my.safe-mail.gq') !== false  ||
            stripos($email, '@myallgaiermogensen.com') !== false  ||
            stripos($email, '@mybisnis.online') !== false  ||
            stripos($email, '@mybitti.de') !== false  ||
            stripos($email, '@mycard.net.ua') !== false  ||
            stripos($email, '@mycleaninbox.net') !== false  ||
            stripos($email, '@mycorneroftheinter.net') !== false  ||
            stripos($email, '@myde.ml') !== false  ||
            stripos($email, '@mydemo.equipment') !== false  ||
            stripos($email, '@myecho.es') !== false  ||
            stripos($email, '@myemailboxy.com') !== false  ||
            stripos($email, '@myglockneronline.com') !== false  ||
            stripos($email, '@myhagiasophia.com') !== false  ||
            stripos($email, '@myindohome.services') !== false  ||
            stripos($email, '@myinterserver.ml') !== false  ||
            stripos($email, '@mykickassideas.com') !== false  ||
            stripos($email, '@mymail-in.net') !== false  ||
            stripos($email, '@mymailoasis.com') !== false  ||
            stripos($email, '@mynetstore.de') !== false  ||
            stripos($email, '@myopang.com') !== false  ||
            stripos($email, '@mypacks.net') !== false  ||
            stripos($email, '@mypartyclip.de') !== false  ||
            stripos($email, '@myphantomemail.com') !== false  ||
            stripos($email, '@mysamp.de') !== false  ||
            stripos($email, '@myspaceinc.com') !== false  ||
            stripos($email, '@myspaceinc.net') !== false  ||
            stripos($email, '@myspaceinc.org') !== false  ||
            stripos($email, '@myspacepimpedup.com') !== false  ||
            stripos($email, '@myspamless.com') !== false  ||
            stripos($email, '@mystvpn.com') !== false  ||
            stripos($email, '@mytandbergonline.com') !== false  ||
            stripos($email, '@mytemp.email') !== false  ||
            stripos($email, '@mytempemail.com') !== false  ||
            stripos($email, '@mytempmail.com') !== false  ||
            stripos($email, '@mytrashmail.com') !== false  ||
            stripos($email, '@mywarnernet.net') !== false  ||
            stripos($email, '@myzx.com') !== false  ||
            stripos($email, '@mziqo.com') !== false  ||
            stripos($email, '@n1nja.org') !== false  ||
            stripos($email, '@nabuma.com') !== false  ||
            stripos($email, '@nada.email') !== false  ||
            stripos($email, '@nada.ltd') !== false  ||
            stripos($email, '@nakedtruth.biz') !== false  ||
            stripos($email, '@nanonym.ch') !== false  ||
            stripos($email, '@nationalgardeningclub.com') !== false  ||
            stripos($email, '@nawmin.info') !== false  ||
            stripos($email, '@nbox.notif.me') !== false  ||
            stripos($email, '@negated.com') !== false  ||
            stripos($email, '@neko2.net') !== false  ||
            stripos($email, '@neomailbox.com') !== false  ||
            stripos($email, '@nepwk.com') !== false  ||
            stripos($email, '@nervmich.net') !== false  ||
            stripos($email, '@nervtmich.net') !== false  ||
            stripos($email, '@netmails.com') !== false  ||
            stripos($email, '@netmails.net') !== false  ||
            stripos($email, '@netricity.nl') !== false  ||
            stripos($email, '@netris.net') !== false  ||
            stripos($email, '@netviewer-france.com') !== false  ||
            stripos($email, '@netzidiot.de') !== false  ||
            stripos($email, '@neverbox.com') !== false  ||
            stripos($email, '@nevermail.de') !== false  ||
            stripos($email, '@newbpotato.tk') !== false  ||
            stripos($email, '@newideasfornewpeople.info') !== false  ||
            stripos($email, '@next.ovh') !== false  ||
            stripos($email, '@nextstopvalhalla.com') !== false  ||
            stripos($email, '@nezdiro.org') !== false  ||
            stripos($email, '@nezzart.com') !== false  ||
            stripos($email, '@nfast.net') !== false  ||
            stripos($email, '@nguyenusedcars.com') !== false  ||
            stripos($email, '@nh3.ro') !== false  ||
            stripos($email, '@nice-4u.com') !== false  ||
            stripos($email, '@nicknassar.com') !== false  ||
            stripos($email, '@niepodam.pl') !== false  ||
            stripos($email, '@nincsmail.com') !== false  ||
            stripos($email, '@nincsmail.hu') !== false  ||
            stripos($email, '@niwl.net') !== false  ||
            stripos($email, '@nm7.cc') !== false  ||
            stripos($email, '@nmail.cf') !== false  ||
            stripos($email, '@nnh.com') !== false  ||
            stripos($email, '@nnot.net') !== false  ||
            stripos($email, '@nnoway.ru') !== false  ||
            stripos($email, '@no-spam.ws') !== false  ||
            stripos($email, '@no-ux.com') !== false  ||
            stripos($email, '@noblepioneer.com') !== false  ||
            stripos($email, '@nobugmail.com') !== false  ||
            stripos($email, '@nobulk.com') !== false  ||
            stripos($email, '@nobuma.com') !== false  ||
            stripos($email, '@noclickemail.com') !== false  ||
            stripos($email, '@nodezine.com') !== false  ||
            stripos($email, '@nogmailspam.info') !== false  ||
            stripos($email, '@noicd.com') !== false  ||
            stripos($email, '@nokiamail.com') !== false  ||
            stripos($email, '@nolemail.ga') !== false  ||
            stripos($email, '@nomail2me.com') !== false  ||
            stripos($email, '@nomail.cf') !== false  ||
            stripos($email, '@nomail.ga') !== false  ||
            stripos($email, '@nomail.pw') !== false  ||
            stripos($email, '@nomail.xl.cx') !== false  ||
            stripos($email, '@nomorespamemails.com') !== false  ||
            stripos($email, '@nonspam.eu') !== false  ||
            stripos($email, '@nonspammer.de') !== false  ||
            stripos($email, '@nonze.ro') !== false  ||
            stripos($email, '@noref.in') !== false  ||
            stripos($email, '@norseforce.com') !== false  ||
            stripos($email, '@nospam4.us') !== false  ||
            stripos($email, '@nospam.ze.tc') !== false  ||
            stripos($email, '@nospamfor.us') !== false  ||
            stripos($email, '@nospammail.net') !== false  ||
            stripos($email, '@nospamthanks.info') !== false  ||
            stripos($email, '@nothingtoseehere.ca') !== false  ||
            stripos($email, '@notmailinator.com') !== false  ||
            stripos($email, '@notrnailinator.com') !== false  ||
            stripos($email, '@notsharingmy.info') !== false  ||
            stripos($email, '@now.im') !== false  ||
            stripos($email, '@nowhere.org') !== false  ||
            stripos($email, '@nowmymail.com') !== false  ||
            stripos($email, '@ntlhelp.net') !== false  ||
            stripos($email, '@nubescontrol.com') !== false  ||
            stripos($email, '@nullbox.info') !== false  ||
            stripos($email, '@nurfuerspam.de') !== false  ||
            stripos($email, '@nus.edu.sg') !== false  ||
            stripos($email, '@nut-cc.nut.cc') !== false  ||
            stripos($email, '@nutcc.nut.cc') !== false  ||
            stripos($email, '@nutpa.net') !== false  ||
            stripos($email, '@nuts2trade.com') !== false  ||
            stripos($email, '@nwldx.com') !== false  ||
            stripos($email, '@nwytg.com') !== false  ||
            stripos($email, '@ny7.me') !== false  ||
            stripos($email, '@nypato.com') !== false  ||
            stripos($email, '@o2stk.org') !== false  ||
            stripos($email, '@o7i.net') !== false  ||
            stripos($email, '@oalsp.com') !== false  ||
            stripos($email, '@obfusko.com') !== false  ||
            stripos($email, '@objectmail.com') !== false  ||
            stripos($email, '@obobbo.com') !== false  ||
            stripos($email, '@obxpestcontrol.com') !== false  ||
            stripos($email, '@odaymail.com') !== false  ||
            stripos($email, '@odem.com') !== false  ||
            stripos($email, '@odnorazovoe.ru') !== false  ||
            stripos($email, '@oepia.com') !== false  ||
            stripos($email, '@oerpub.org') !== false  ||
            stripos($email, '@offshore-proxies.net') !== false  ||
            stripos($email, '@ohaaa.de') !== false  ||
            stripos($email, '@ohi.tw') !== false  ||
            stripos($email, '@oing.cf') !== false  ||
            stripos($email, '@okclprojects.com') !== false  ||
            stripos($email, '@okrent.us') !== false  ||
            stripos($email, '@okzk.com') !== false  ||
            stripos($email, '@olypmall.ru') !== false  ||
            stripos($email, '@omail.pro') !== false  ||
            stripos($email, '@omnievents.org') !== false  ||
            stripos($email, '@one2mail.info') !== false  ||
            stripos($email, '@one-time.email') !== false  ||
            stripos($email, '@oneoffemail.com') !== false  ||
            stripos($email, '@oneoffmail.com') !== false  ||
            stripos($email, '@onewaymail.com') !== false  ||
            stripos($email, '@onlatedotcom.info') !== false  ||
            stripos($email, '@online.ms') !== false  ||
            stripos($email, '@onlineidea.info') !== false  ||
            stripos($email, '@onlysext.com') !== false  ||
            stripos($email, '@onqin.com') !== false  ||
            stripos($email, '@ontyne.biz') !== false  ||
            stripos($email, '@oolus.com') !== false  ||
            stripos($email, '@oopi.org') !== false  ||
            stripos($email, '@opayq.com') !== false  ||
            stripos($email, '@opendns.ro') !== false  ||
            stripos($email, '@opmmedia.ga') !== false  ||
            stripos($email, '@opp24.com') !== false  ||
            stripos($email, '@optimaweb.me') !== false  ||
            stripos($email, '@ordinaryamerican.net') !== false  ||
            stripos($email, '@oreidresume.com') !== false  ||
            stripos($email, '@oroki.de') !== false  ||
            stripos($email, '@oshietechan.link') !== false  ||
            stripos($email, '@otherinbox.com') !== false  ||
            stripos($email, '@ourawesome.life') !== false  ||
            stripos($email, '@ourklips.com') !== false  ||
            stripos($email, '@ourpreviewdomain.com') !== false  ||
            stripos($email, '@outlawspam.com') !== false  ||
            stripos($email, '@outmail.win') !== false  ||
            stripos($email, '@ovi.usa.cc') !== false  ||
            stripos($email, '@ovpn.to') !== false  ||
            stripos($email, '@owlpic.com') !== false  ||
            stripos($email, '@ownsyou.de') !== false  ||
            stripos($email, '@oxopoha.com') !== false  ||
            stripos($email, '@ozyl.de') !== false  ||
            stripos($email, '@p33.org') !== false  ||
            stripos($email, '@p71ce1m.com') !== false  ||
            stripos($email, '@pa9e.com') !== false  ||
            stripos($email, '@pagamenti.tk') !== false  ||
            stripos($email, '@paharpurmim.tk') !== false  ||
            stripos($email, '@pakadebu.ga') !== false  ||
            stripos($email, '@pancakemail.com') !== false  ||
            stripos($email, '@paplease.com') !== false  ||
            stripos($email, '@pastebitch.com') !== false  ||
            stripos($email, '@pavilionx2.com') !== false  ||
            stripos($email, '@payforclick.net') !== false  ||
            stripos($email, '@payperex2.com') !== false  ||
            stripos($email, '@pcmylife.com') !== false  ||
            stripos($email, '@penisgoes.in') !== false  ||
            stripos($email, '@penoto.tk') !== false  ||
            stripos($email, '@pepbot.com') !== false  ||
            stripos($email, '@peterdethier.com') !== false  ||
            stripos($email, '@petrzilka.net') !== false  ||
            stripos($email, '@pfui.ru') !== false  ||
            stripos($email, '@photo-impact.eu') !== false  ||
            stripos($email, '@photomark.net') !== false  ||
            stripos($email, '@pi.vu') !== false  ||
            stripos($email, '@piaa.me') !== false  ||
            stripos($email, '@pig.pp.ua') !== false  ||
            stripos($email, '@pii.at') !== false  ||
            stripos($email, '@piki.si') !== false  ||
            stripos($email, '@pimpedupmyspace.com') !== false  ||
            stripos($email, '@pinehill-seattle.org') !== false  ||
            stripos($email, '@pingir.com') !== false  ||
            stripos($email, '@pisls.com') !== false  ||
            stripos($email, '@pjjkp.com') !== false  ||
            stripos($email, '@playcard-semi.com') !== false  ||
            stripos($email, '@plexolan.de') !== false  ||
            stripos($email, '@plhk.ru') !== false  ||
            stripos($email, '@ploae.com') !== false  ||
            stripos($email, '@plw.me') !== false  ||
            stripos($email, '@pojok.ml') !== false  ||
            stripos($email, '@pokemail.net') !== false  ||
            stripos($email, '@pokiemobile.com') !== false  ||
            stripos($email, '@polarkingxx.ml') !== false  ||
            stripos($email, '@politikerclub.de') !== false  ||
            stripos($email, '@pooae.com') !== false  ||
            stripos($email, '@poofy.org') !== false  ||
            stripos($email, '@pookmail.com') !== false  ||
            stripos($email, '@poopiebutt.club') !== false  ||
            stripos($email, '@popesodomy.com') !== false  ||
            stripos($email, '@popgx.com') !== false  ||
            stripos($email, '@porsh.net') !== false  ||
            stripos($email, '@posdz.com') !== false  ||
            stripos($email, '@posta.store') !== false  ||
            stripos($email, '@postacin.com') !== false  ||
            stripos($email, '@postonline.me') !== false  ||
            stripos($email, '@poutineyourface.com') !== false  ||
            stripos($email, '@powered.name') !== false  ||
            stripos($email, '@powlearn.com') !== false  ||
            stripos($email, '@ppetw.com') !== false  ||
            stripos($email, '@predatorrat.cf') !== false  ||
            stripos($email, '@predatorrat.ga') !== false  ||
            stripos($email, '@predatorrat.gq') !== false  ||
            stripos($email, '@predatorrat.ml') !== false  ||
            stripos($email, '@predatorrat.tk') !== false  ||
            stripos($email, '@premium-mail.fr') !== false  ||
            stripos($email, '@primabananen.net') !== false  ||
            stripos($email, '@privacy.net') !== false  ||
            stripos($email, '@privatdemail.net') !== false  ||
            stripos($email, '@privy-mail.com') !== false  ||
            stripos($email, '@privy-mail.de') !== false  ||
            stripos($email, '@privymail.de') !== false  ||
            stripos($email, '@pro-tag.org') !== false  ||
            stripos($email, '@procrackers.com') !== false  ||
            stripos($email, '@projectcl.com') !== false  ||
            stripos($email, '@proprietativalcea.ro') !== false  ||
            stripos($email, '@propscore.com') !== false  ||
            stripos($email, '@protempmail.com') !== false  ||
            stripos($email, '@proxymail.eu') !== false  ||
            stripos($email, '@proxyparking.com') !== false  ||
            stripos($email, '@prtnx.com') !== false  ||
            stripos($email, '@prtz.eu') !== false  ||
            stripos($email, '@psh.me') !== false  ||
            stripos($email, '@psles.com') !== false  ||
            stripos($email, '@psoxs.com') !== false  ||
            stripos($email, '@puglieisi.com') !== false  ||
            stripos($email, '@puji.pro') !== false  ||
            stripos($email, '@punkass.com') !== false  ||
            stripos($email, '@purcell.email') !== false  ||
            stripos($email, '@purelogistics.org') !== false  ||
            stripos($email, '@put2.net') !== false  ||
            stripos($email, '@putthisinyourspamdatabase.com') !== false  ||
            stripos($email, '@pw.j7.cloudns.cx') !== false  ||
            stripos($email, '@pw.mymy.cf') !== false  ||
            stripos($email, '@pw.mysafe.ml') !== false  ||
            stripos($email, '@pwrby.com') !== false  ||
            stripos($email, '@qaioz.com') !== false  ||
            stripos($email, '@qasti.com') !== false  ||
            stripos($email, '@qbfree.us') !== false  ||
            stripos($email, '@qc.to') !== false  ||
            stripos($email, '@qibl.at') !== false  ||
            stripos($email, '@qipmail.net') !== false  ||
            stripos($email, '@qiq.us') !== false  ||
            stripos($email, '@qisdo.com') !== false  ||
            stripos($email, '@qisoa.com') !== false  ||
            stripos($email, '@qoika.com') !== false  ||
            stripos($email, '@qq.com') !== false  ||
            stripos($email, '@qq.my') !== false  ||
            stripos($email, '@qsl.ro') !== false  ||
            stripos($email, '@qtum-ico.com') !== false  ||
            stripos($email, '@quadrafit.com') !== false  ||
            stripos($email, '@queuem.com') !== false  ||
            stripos($email, '@quickinbox.com') !== false  ||
            stripos($email, '@quickmail.nl') !== false  ||
            stripos($email, '@ququb.com') !== false  ||
            stripos($email, '@qvy.me') !== false  ||
            stripos($email, '@qwickmail.com') !== false  ||
            stripos($email, '@r4nd0m.de') !== false  ||
            stripos($email, '@ra3.us') !== false  ||
            stripos($email, '@rabin.ca') !== false  ||
            stripos($email, '@rabiot.reisen') !== false  ||
            stripos($email, '@raetp9.com') !== false  ||
            stripos($email, '@raffles.gg') !== false  ||
            stripos($email, '@raketenmann.de') !== false  ||
            stripos($email, '@rancidhome.net') !== false  ||
            stripos($email, '@randomail.net') !== false  ||
            stripos($email, '@raqid.com') !== false  ||
            stripos($email, '@rax.la') !== false  ||
            stripos($email, '@raxtest.com') !== false  ||
            stripos($email, '@razemail.com') !== false  ||
            stripos($email, '@rbb.org') !== false  ||
            stripos($email, '@rcasd.com') !== false  ||
            stripos($email, '@rcpt.at') !== false  ||
            stripos($email, '@rdklcrv.xyz') !== false  ||
            stripos($email, '@re-gister.com') !== false  ||
            stripos($email, '@reality-concept.club') !== false  ||
            stripos($email, '@reallymymail.com') !== false  ||
            stripos($email, '@realtyalerts.ca') !== false  ||
            stripos($email, '@rebates.stream') !== false  ||
            stripos($email, '@receiveee.com') !== false  ||
            stripos($email, '@recipeforfailure.com') !== false  ||
            stripos($email, '@recode.me') !== false  ||
            stripos($email, '@reconmail.com') !== false  ||
            stripos($email, '@recursor.net') !== false  ||
            stripos($email, '@recyclemail.dk') !== false  ||
            stripos($email, '@reddit.usa.cc') !== false  ||
            stripos($email, '@redfeathercrow.com') !== false  ||
            stripos($email, '@reftoken.net') !== false  ||
            stripos($email, '@regbypass.com') !== false  ||
            stripos($email, '@regbypass.comsafe-mail.net') !== false  ||
            stripos($email, '@rejectmail.com') !== false  ||
            stripos($email, '@rejo.technology') !== false  ||
            stripos($email, '@relay-bossku3.com') !== false  ||
            stripos($email, '@reliable-mail.com') !== false  ||
            stripos($email, '@remail.cf') !== false  ||
            stripos($email, '@remail.ga') !== false  ||
            stripos($email, '@remarkable.rocks') !== false  ||
            stripos($email, '@remote.li') !== false  ||
            stripos($email, '@reptech.org') !== false  ||
            stripos($email, '@reptilegenetics.com') !== false  ||
            stripos($email, '@revolvingdoorhoax.org') !== false  ||
            stripos($email, '@rhyta.com') !== false  ||
            stripos($email, '@richfinances.pw') !== false  ||
            stripos($email, '@riddermark.de') !== false  ||
            stripos($email, '@rifkian.gq') !== false  ||
            stripos($email, '@risingsuntouch.com') !== false  ||
            stripos($email, '@riski.cf') !== false  ||
            stripos($email, '@rklips.com') !== false  ||
            stripos($email, '@rkomo.com') !== false  ||
            stripos($email, '@rma.ec') !== false  ||
            stripos($email, '@rmqkr.net') !== false  ||
            stripos($email, '@rnailinator.com') !== false  ||
            stripos($email, '@ro.lt') !== false  ||
            stripos($email, '@robertspcrepair.com') !== false  ||
            stripos($email, '@rollindo.agency') !== false  ||
            stripos($email, '@ronnierage.net') !== false  ||
            stripos($email, '@rootfest.net') !== false  ||
            stripos($email, '@rotaniliam.com') !== false  ||
            stripos($email, '@rowe-solutions.com') !== false  ||
            stripos($email, '@royal.net') !== false  ||
            stripos($email, '@royaldoodles.org') !== false  ||
            stripos($email, '@rppkn.com') !== false  ||
            stripos($email, '@rr.0x01.gq') !== false  ||
            stripos($email, '@rtrtr.com') !== false  ||
            stripos($email, '@rtskiya.xyz') !== false  ||
            stripos($email, '@rudymail.ml') !== false  ||
            stripos($email, '@ruffrey.com') !== false  ||
            stripos($email, '@rumgel.com') !== false  ||
            stripos($email, '@runi.ca') !== false  ||
            stripos($email, '@ruru.be') !== false  ||
            stripos($email, '@rustydoor.com') !== false  ||
            stripos($email, '@rvb.ro') !== false  ||
            stripos($email, '@s0ny.flu.cc') !== false  ||
            stripos($email, '@s0ny.igg.biz') !== false  ||
            stripos($email, '@s0ny.net') !== false  ||
            stripos($email, '@s0ny.nut.cc') !== false  ||
            stripos($email, '@s0ny.usa.cc') !== false  ||
            stripos($email, '@s33db0x.com') !== false  ||
            stripos($email, '@s-s.flu.cc') !== false  ||
            stripos($email, '@s-s.igg.biz') !== false  ||
            stripos($email, '@s-s.nut.cc') !== false  ||
            stripos($email, '@s-s.usa.cc') !== false  ||
            stripos($email, '@s.0x01.gq') !== false  ||
            stripos($email, '@sabrestlouis.com') !== false  ||
            stripos($email, '@sackboii.com') !== false  ||
            stripos($email, '@safaat.cf') !== false  ||
            stripos($email, '@safe-mail.net') !== false  ||
            stripos($email, '@safermail.info') !== false  ||
            stripos($email, '@safersignup.de') !== false  ||
            stripos($email, '@safetymail.info') !== false  ||
            stripos($email, '@safetypost.de') !== false  ||
            stripos($email, '@saharanightstempe.com') !== false  ||
            stripos($email, '@saigonmail.us') !== false  ||
            stripos($email, '@salmeow.tk') !== false  ||
            stripos($email, '@samsclass.info') !== false  ||
            stripos($email, '@sandelf.de') !== false  ||
            stripos($email, '@sandwhichvideo.com') !== false  ||
            stripos($email, '@sanfinder.com') !== false  ||
            stripos($email, '@sanim.net') !== false  ||
            stripos($email, '@sanstr.com') !== false  ||
            stripos($email, '@sast.ro') !== false  ||
            stripos($email, '@satisfyme.club') !== false  ||
            stripos($email, '@satukosong.com') !== false  ||
            stripos($email, '@sausen.com') !== false  ||
            stripos($email, '@sawoe.com') !== false  ||
            stripos($email, '@saynotospams.com') !== false  ||
            stripos($email, '@scatmail.com') !== false  ||
            stripos($email, '@scay.net') !== false  ||
            stripos($email, '@schachrol.com') !== false  ||
            stripos($email, '@schafmail.de') !== false  ||
            stripos($email, '@schmeissweg.tk') !== false  ||
            stripos($email, '@schmid.cf') !== false  ||
            stripos($email, '@schrott-email.de') !== false  ||
            stripos($email, '@sd3.in') !== false  ||
            stripos($email, '@secmail.pw') !== false  ||
            stripos($email, '@secretemail.de') !== false  ||
            stripos($email, '@sector2.org') !== false  ||
            stripos($email, '@secure-box.info') !== false  ||
            stripos($email, '@secure-mail.biz') !== false  ||
            stripos($email, '@secure-mail.cc') !== false  ||
            stripos($email, '@secured-link.net') !== false  ||
            stripos($email, '@securehost.com.es') !== false  ||
            stripos($email, '@securemail.flu.cc') !== false  ||
            stripos($email, '@securemail.igg.biz') !== false  ||
            stripos($email, '@securemail.nut.cc') !== false  ||
            stripos($email, '@securemail.usa.cc') !== false  ||
            stripos($email, '@seekapps.com') !== false  ||
            stripos($email, '@seekjobs4u.com') !== false  ||
            stripos($email, '@sejaa.lv') !== false  ||
            stripos($email, '@selfdestructingmail.com') !== false  ||
            stripos($email, '@selfdestructingmail.org') !== false  ||
            stripos($email, '@semail.us') !== false  ||
            stripos($email, '@send22u.info') !== false  ||
            stripos($email, '@sendfree.org') !== false  ||
            stripos($email, '@sendingspecialflyers.com') !== false  ||
            stripos($email, '@SendSpamHere.com') !== false  ||
            stripos($email, '@sendspamhere.com') !== false  ||
            stripos($email, '@senseless-entertainment.com') !== false  ||
            stripos($email, '@server.ms') !== false  ||
            stripos($email, '@services391.com') !== false  ||
            stripos($email, '@sexforswingers.com') !== false  ||
            stripos($email, '@sexical.com') !== false  ||
            stripos($email, '@sexyalwasmi.top') !== false  ||
            stripos($email, '@shalar.net') !== false  ||
            stripos($email, '@sharedmailbox.org') !== false  ||
            stripos($email, '@sharklasers.com') !== false  ||
            stripos($email, '@shhmail.com') !== false  ||
            stripos($email, '@shhuut.org') !== false  ||
            stripos($email, '@shieldedmail.com') !== false  ||
            stripos($email, '@shieldemail.com') !== false  ||
            stripos($email, '@shiftmail.com') !== false  ||
            stripos($email, '@shipfromto.com') !== false  ||
            stripos($email, '@shiphazmat.org') !== false  ||
            stripos($email, '@shipping-regulations.com') !== false  ||
            stripos($email, '@shippingterms.org') !== false  ||
            stripos($email, '@shitmail.de') !== false  ||
            stripos($email, '@shitmail.me') !== false  ||
            stripos($email, '@shitmail.org') !== false  ||
            stripos($email, '@shitware.nl') !== false  ||
            stripos($email, '@shmeriously.com') !== false  ||
            stripos($email, '@shortmail.net') !== false  ||
            stripos($email, '@shotmail.ru') !== false  ||
            stripos($email, '@showslow.de') !== false  ||
            stripos($email, '@shrib.com') !== false  ||
            stripos($email, '@shufuni.cn') !== false  ||
            stripos($email, '@shut.name') !== false  ||
            stripos($email, '@shut.ws') !== false  ||
            stripos($email, '@sibmail.com') !== false  ||
            stripos($email, '@sify.com') !== false  ||
            stripos($email, '@sikux.com') !== false  ||
            stripos($email, '@siliwangi.ga') !== false  ||
            stripos($email, '@simpleitsecurity.info') !== false  ||
            stripos($email, '@sin.cl') !== false  ||
            stripos($email, '@sinfiltro.cl') !== false  ||
            stripos($email, '@singlespride.com') !== false  ||
            stripos($email, '@sinnlos-mail.de') !== false  ||
            stripos($email, '@sino.tw') !== false  ||
            stripos($email, '@siteposter.net') !== false  ||
            stripos($email, '@sizzlemctwizzle.com') !== false  ||
            stripos($email, '@sjuaq.com') !== false  ||
            stripos($email, '@skeefmail.com') !== false  ||
            stripos($email, '@sky-inbox.com') !== false  ||
            stripos($email, '@sky-ts.de') !== false  ||
            stripos($email, '@skypaluten.de') !== false  ||
            stripos($email, '@slapsfromlastnight.com') !== false  ||
            stripos($email, '@slaskpost.se') !== false  ||
            stripos($email, '@slave-auctions.net') !== false  ||
            stripos($email, '@slippery.email') !== false  ||
            stripos($email, '@slipry.net') !== false  ||
            stripos($email, '@slopsbox.com') !== false  ||
            stripos($email, '@slothmail.net') !== false  ||
            stripos($email, '@slushmail.com') !== false  ||
            stripos($email, '@sluteen.com') !== false  ||
            stripos($email, '@sly.io') !== false  ||
            stripos($email, '@smallker.tk') !== false  ||
            stripos($email, '@smapfree24.com') !== false  ||
            stripos($email, '@smapfree24.de') !== false  ||
            stripos($email, '@smapfree24.eu') !== false  ||
            stripos($email, '@smapfree24.info') !== false  ||
            stripos($email, '@smapfree24.org') !== false  ||
            stripos($email, '@smarttalent.pw') !== false  ||
            stripos($email, '@smashmail.de') !== false  ||
            stripos($email, '@smellfear.com') !== false  ||
            stripos($email, '@smellrear.com') !== false  ||
            stripos($email, '@smellypotato.tk') !== false  ||
            stripos($email, '@smtp99.com') !== false  ||
            stripos($email, '@smwg.info') !== false  ||
            stripos($email, '@snakemail.com') !== false  ||
            stripos($email, '@snapwet.com') !== false  ||
            stripos($email, '@sneakemail.com') !== false  ||
            stripos($email, '@sneakmail.de') !== false  ||
            stripos($email, '@snkmail.com') !== false  ||
            stripos($email, '@socialfurry.org') !== false  ||
            stripos($email, '@socrazy.club') !== false  ||
            stripos($email, '@socrazy.online') !== false  ||
            stripos($email, '@sofimail.com') !== false  ||
            stripos($email, '@sofort-mail.de') !== false  ||
            stripos($email, '@sofortmail.de') !== false  ||
            stripos($email, '@softpls.asia') !== false  ||
            stripos($email, '@sogetthis.com') !== false  ||
            stripos($email, '@sohai.ml') !== false  ||
            stripos($email, '@sohu.com') !== false  ||
            stripos($email, '@sohus.cn') !== false  ||
            stripos($email, '@soioa.com') !== false  ||
            stripos($email, '@soisz.com') !== false  ||
            stripos($email, '@solar-impact.pro') !== false  ||
            stripos($email, '@solvemail.info') !== false  ||
            stripos($email, '@solventtrap.wiki') !== false  ||
            stripos($email, '@sonshi.cf') !== false  ||
            stripos($email, '@soodmail.com') !== false  ||
            stripos($email, '@soodomail.com') !== false  ||
            stripos($email, '@soodonims.com') !== false  ||
            stripos($email, '@soon.it') !== false  ||
            stripos($email, '@spam') !== false  ||
            stripos($email, '@spam4.me') !== false  ||
            stripos($email, '@spam-be-gone.com') !== false  ||
            stripos($email, '@spam.0x01.tk') !== false  ||
            stripos($email, '@spam.la') !== false  ||
            stripos($email, '@spam.org.es') !== false  ||
            stripos($email, '@spam.su') !== false  ||
            stripos($email, '@spamail.de') !== false  ||
            stripos($email, '@spamarrest.com') !== false  ||
            stripos($email, '@spamavert.com') !== false  ||
            stripos($email, '@spambob.com') !== false  ||
            stripos($email, '@spambob.net') !== false  ||
            stripos($email, '@spambob.org') !== false  ||
            stripos($email, '@spambog.com') !== false  ||
            stripos($email, '@spambog.de') !== false  ||
            stripos($email, '@spambog.net') !== false  ||
            stripos($email, '@spambog.ru') !== false  ||
            stripos($email, '@spambooger.com') !== false  ||
            stripos($email, '@spambox.info') !== false  ||
            stripos($email, '@spambox.irishspringrealty.com') !== false  ||
            stripos($email, '@spambox.org') !== false  ||
            stripos($email, '@spambox.us') !== false  ||
            stripos($email, '@spamcannon.com') !== false  ||
            stripos($email, '@spamcannon.net') !== false  ||
            stripos($email, '@spamcero.com') !== false  ||
            stripos($email, '@spamcon.org') !== false  ||
            stripos($email, '@spamcorptastic.com') !== false  ||
            stripos($email, '@spamcowboy.com') !== false  ||
            stripos($email, '@spamcowboy.net') !== false  ||
            stripos($email, '@spamcowboy.org') !== false  ||
            stripos($email, '@spamday.com') !== false  ||
            stripos($email, '@spamdecoy.net') !== false  ||
            stripos($email, '@spamex.com') !== false  ||
            stripos($email, '@spamfighter.cf') !== false  ||
            stripos($email, '@spamfighter.ga') !== false  ||
            stripos($email, '@spamfighter.gq') !== false  ||
            stripos($email, '@spamfighter.ml') !== false  ||
            stripos($email, '@spamfighter.tk') !== false  ||
            stripos($email, '@spamfree24.com') !== false  ||
            stripos($email, '@spamfree24.de') !== false  ||
            stripos($email, '@spamfree24.eu') !== false  ||
            stripos($email, '@spamfree24.info') !== false  ||
            stripos($email, '@spamfree24.net') !== false  ||
            stripos($email, '@spamfree24.org') !== false  ||
            stripos($email, '@spamfree.eu') !== false  ||
            stripos($email, '@spamgoes.in') !== false  ||
            stripos($email, '@spamgourmet.com') !== false  ||
            stripos($email, '@spamherelots.com') !== false  ||
            stripos($email, '@SpamHerePlease.com') !== false  ||
            stripos($email, '@spamhereplease.com') !== false  ||
            stripos($email, '@spamhole.com') !== false  ||
            stripos($email, '@spamify.com') !== false  ||
            stripos($email, '@spaminator.de') !== false  ||
            stripos($email, '@spamkill.info') !== false  ||
            stripos($email, '@spaml.com') !== false  ||
            stripos($email, '@spaml.de') !== false  ||
            stripos($email, '@spamlot.net') !== false  ||
            stripos($email, '@spammotel.com') !== false  ||
            stripos($email, '@spamobox.com') !== false  ||
            stripos($email, '@spamoff.de') !== false  ||
            stripos($email, '@spamsalad.in') !== false  ||
            stripos($email, '@spamslicer.com') !== false  ||
            stripos($email, '@spamsphere.com') !== false  ||
            stripos($email, '@spamspot.com') !== false  ||
            stripos($email, '@spamstack.net') !== false  ||
            stripos($email, '@spamthis.co.uk') !== false  ||
            stripos($email, '@SpamThisPlease.com') !== false  ||
            stripos($email, '@spamthisplease.com') !== false  ||
            stripos($email, '@spamtrail.com') !== false  ||
            stripos($email, '@spamtrap.ro') !== false  ||
            stripos($email, '@spamtroll.net') !== false  ||
            stripos($email, '@spamwc.cf') !== false  ||
            stripos($email, '@spamwc.ga') !== false  ||
            stripos($email, '@spamwc.gq') !== false  ||
            stripos($email, '@spamwc.ml') !== false  ||
            stripos($email, '@speed.1s.fr') !== false  ||
            stripos($email, '@speedgaus.net') !== false  ||
            stripos($email, '@spikio.com') !== false  ||
            stripos($email, '@spoofmail.de') !== false  ||
            stripos($email, '@spr.io') !== false  ||
            stripos($email, '@spritzzone.de') !== false  ||
            stripos($email, '@spybox.de') !== false  ||
            stripos($email, '@squizzy.de') !== false  ||
            stripos($email, '@squizzy.net') !== false  ||
            stripos($email, '@sroff.com') !== false  ||
            stripos($email, '@sry.li') !== false  ||
            stripos($email, '@ss.0x01.tk') !== false  ||
            stripos($email, '@ssoia.com') !== false  ||
            stripos($email, '@stanfordujjain.com') !== false  ||
            stripos($email, '@starlight-breaker.net') !== false  ||
            stripos($email, '@startfu.com') !== false  ||
            stripos($email, '@startkeys.com') !== false  ||
            stripos($email, '@statdvr.com') !== false  ||
            stripos($email, '@stathost.net') !== false  ||
            stripos($email, '@statiix.com') !== false  ||
            stripos($email, '@steambot.net') !== false  ||
            stripos($email, '@stelliteop.info') !== false  ||
            stripos($email, '@stexsy.com') !== false  ||
            stripos($email, '@stinkefinger.net') !== false  ||
            stripos($email, '@stop-my-spam.cf') !== false  ||
            stripos($email, '@stop-my-spam.com') !== false  ||
            stripos($email, '@stop-my-spam.ga') !== false  ||
            stripos($email, '@stop-my-spam.ml') !== false  ||
            stripos($email, '@stop-my-spam.pp.ua') !== false  ||
            stripos($email, '@stop-my-spam.tk') !== false  ||
            stripos($email, '@storiqax.com') !== false  ||
            stripos($email, '@storiqax.top') !== false  ||
            stripos($email, '@storj99.com') !== false  ||
            stripos($email, '@storj99.top') !== false  ||
            stripos($email, '@streetwisemail.com') !== false  ||
            stripos($email, '@stromox.com') !== false  ||
            stripos($email, '@stuckmail.com') !== false  ||
            stripos($email, '@stuffmail.de') !== false  ||
            stripos($email, '@stumpfwerk.com') !== false  ||
            stripos($email, '@suburbanthug.com') !== false  ||
            stripos($email, '@suckmyd.com') !== false  ||
            stripos($email, '@sudolife.me') !== false  ||
            stripos($email, '@sudolife.net') !== false  ||
            stripos($email, '@sudomail.biz') !== false  ||
            stripos($email, '@sudomail.com') !== false  ||
            stripos($email, '@sudomail.net') !== false  ||
            stripos($email, '@sudoverse.com') !== false  ||
            stripos($email, '@sudoverse.net') !== false  ||
            stripos($email, '@sudoweb.net') !== false  ||
            stripos($email, '@sudoworld.com') !== false  ||
            stripos($email, '@sudoworld.net') !== false  ||
            stripos($email, '@suhuempek.ga') !== false  ||
            stripos($email, '@suioe.com') !== false  ||
            stripos($email, '@suoox.com') !== false  ||
            stripos($email, '@super-auswahl.de') !== false  ||
            stripos($email, '@supergreatmail.com') !== false  ||
            stripos($email, '@supermailer.jp') !== false  ||
            stripos($email, '@superplatyna.com') !== false  ||
            stripos($email, '@superrito.com') !== false  ||
            stripos($email, '@superstachel.de') !== false  ||
            stripos($email, '@suremail.info') !== false  ||
            stripos($email, '@svip520.cn') !== false  ||
            stripos($email, '@svk.jp') !== false  ||
            stripos($email, '@svxr.org') !== false  ||
            stripos($email, '@sweetpotato.ml') !== false  ||
            stripos($email, '@sweetxxx.de') !== false  ||
            stripos($email, '@swift10minutemail.com') !== false  ||
            stripos($email, '@sylvannet.com') !== false  ||
            stripos($email, '@symphonyresume.com') !== false  ||
            stripos($email, '@syosetu.gq') !== false  ||
            stripos($email, '@system-2125.com') !== false  ||
            stripos($email, '@syujob.accountants') !== false  ||
            stripos($email, '@szerz.com') !== false  ||
            stripos($email, '@tafmail.com') !== false  ||
            stripos($email, '@tafoi.gr') !== false  ||
            stripos($email, '@taglead.com') !== false  ||
            stripos($email, '@tagmymedia.com') !== false  ||
            stripos($email, '@tagyourself.com') !== false  ||
            stripos($email, '@tahutex.online') !== false  ||
            stripos($email, '@talkinator.com') !== false  ||
            stripos($email, '@tanukis.org') !== false  ||
            stripos($email, '@tapchicuoihoi.com') !== false  ||
            stripos($email, '@taphear.com') !== false  ||
            stripos($email, '@tb-on-line.net') !== false  ||
            stripos($email, '@tech69.com') !== false  ||
            stripos($email, '@techemail.com') !== false  ||
            stripos($email, '@techgroup.me') !== false  ||
            stripos($email, '@teerest.com') !== false  ||
            stripos($email, '@teewars.org') !== false  ||
            stripos($email, '@tefl.ro') !== false  ||
            stripos($email, '@telecomix.pl') !== false  ||
            stripos($email, '@teleworm.com') !== false  ||
            stripos($email, '@teleworm.us') !== false  ||
            stripos($email, '@tellos.xyz') !== false  ||
            stripos($email, '@temp-mail.com') !== false  ||
            stripos($email, '@temp-mail.de') !== false  ||
            stripos($email, '@temp-mail.org') !== false  ||
            stripos($email, '@temp-mail.pp.ua') !== false  ||
            stripos($email, '@temp-mail.ru') !== false  ||
            stripos($email, '@temp-mails.com') !== false  ||
            stripos($email, '@tempail.com') !== false  ||
            stripos($email, '@tempalias.com') !== false  ||
            stripos($email, '@tempe-mail.com') !== false  ||
            stripos($email, '@tempemail.biz') !== false  ||
            stripos($email, '@tempemail.co.za') !== false  ||
            stripos($email, '@tempemail.com') !== false  ||
            stripos($email, '@tempemail.net') !== false  ||
            stripos($email, '@tempinbox.co.uk') !== false  ||
            stripos($email, '@tempinbox.com') !== false  ||
            stripos($email, '@tempmail2.com') !== false  ||
            stripos($email, '@tempmail.co') !== false  ||
            stripos($email, '@tempmail.de') !== false  ||
            stripos($email, '@tempmail.eu') !== false  ||
            stripos($email, '@tempmail.it') !== false  ||
            stripos($email, '@tempmail.pp.ua') !== false  ||
            stripos($email, '@tempmail.us') !== false  ||
            stripos($email, '@tempmailaddress.com') !== false  ||
            stripos($email, '@tempmaildemo.com') !== false  ||
            stripos($email, '@tempmailer.com') !== false  ||
            stripos($email, '@tempmailer.de') !== false  ||
            stripos($email, '@tempomail.fr') !== false  ||
            stripos($email, '@temporarily.de') !== false  ||
            stripos($email, '@temporarioemail.com.br') !== false  ||
            stripos($email, '@temporaryemail.net') !== false  ||
            stripos($email, '@temporaryemail.us') !== false  ||
            stripos($email, '@temporaryforwarding.com') !== false  ||
            stripos($email, '@temporaryinbox.com') !== false  ||
            stripos($email, '@temporarymailaddress.com') !== false  ||
            stripos($email, '@tempr.email') !== false  ||
            stripos($email, '@tempsky.com') !== false  ||
            stripos($email, '@tempthe.net') !== false  ||
            stripos($email, '@tempymail.com') !== false  ||
            stripos($email, '@ternaklele.ga') !== false  ||
            stripos($email, '@testore.co') !== false  ||
            stripos($email, '@testudine.com') !== false  ||
            stripos($email, '@thanksnospam.info') !== false  ||
            stripos($email, '@thankyou2010.com') !== false  ||
            stripos($email, '@thatim.info') !== false  ||
            stripos($email, '@thc.st') !== false  ||
            stripos($email, '@theaviors.com') !== false  ||
            stripos($email, '@thebearshark.com') !== false  ||
            stripos($email, '@thecloudindex.com') !== false  ||
            stripos($email, '@thediamants.org') !== false  ||
            stripos($email, '@thedirhq.info') !== false  ||
            stripos($email, '@thelightningmail.net') !== false  ||
            stripos($email, '@thelimestones.com') !== false  ||
            stripos($email, '@thembones.com.au') !== false  ||
            stripos($email, '@themostemail.com') !== false  ||
            stripos($email, '@thereddoors.online') !== false  ||
            stripos($email, '@thescrappermovie.com') !== false  ||
            stripos($email, '@theteastory.info') !== false  ||
            stripos($email, '@thex.ro') !== false  ||
            stripos($email, '@thietbivanphong.asia') !== false  ||
            stripos($email, '@thisisnotmyrealemail.com') !== false  ||
            stripos($email, '@thismail.net') !== false  ||
            stripos($email, '@thisurl.website') !== false  ||
            stripos($email, '@thnikka.com') !== false  ||
            stripos($email, '@thraml.com') !== false  ||
            stripos($email, '@thrma.com') !== false  ||
            stripos($email, '@throam.com') !== false  ||
            stripos($email, '@thrott.com') !== false  ||
            stripos($email, '@throwam.com') !== false  ||
            stripos($email, '@throwawayemailaddress.com') !== false  ||
            stripos($email, '@throwawaymail.com') !== false  ||
            stripos($email, '@throwawaymail.pp.ua') !== false  ||
            stripos($email, '@throya.com') !== false  ||
            stripos($email, '@thunkinator.org') !== false  ||
            stripos($email, '@thxmate.com') !== false  ||
            stripos($email, '@ti.igg.biz') !== false  ||
            stripos($email, '@tiapz.com') !== false  ||
            stripos($email, '@tic.ec') !== false  ||
            stripos($email, '@tilien.com') !== false  ||
            stripos($email, '@timgiarevn.com') !== false  ||
            stripos($email, '@timkassouf.com') !== false  ||
            stripos($email, '@tinoza.org') !== false  ||
            stripos($email, '@tinyurl24.com') !== false  ||
            stripos($email, '@tipsb.com') !== false  ||
            stripos($email, '@titaspaharpur5.cf') !== false  ||
            stripos($email, '@titaspaharpur5.ga') !== false  ||
            stripos($email, '@titaspaharpur5.gq') !== false  ||
            stripos($email, '@tittbit.in') !== false  ||
            stripos($email, '@tiv.cc') !== false  ||
            stripos($email, '@tizi.com') !== false  ||
            stripos($email, '@tkitc.de') !== false  ||
            stripos($email, '@tlpn.org') !== false  ||
            stripos($email, '@tm2mail.com') !== false  ||
            stripos($email, '@tmail.com') !== false  ||
            stripos($email, '@tmail.ws') !== false  ||
            stripos($email, '@tmailinator.com') !== false  ||
            stripos($email, '@tmails.net') !== false  ||
            stripos($email, '@tmpeml.info') !== false  ||
            stripos($email, '@tmpjr.me') !== false  ||
            stripos($email, '@tmpmail.net') !== false  ||
            stripos($email, '@tmpmail.org') !== false  ||
            stripos($email, '@toddsbighug.com') !== false  ||
            stripos($email, '@toiea.com') !== false  ||
            stripos($email, '@tokem.co') !== false  ||
            stripos($email, '@tokenmail.de') !== false  ||
            stripos($email, '@tonymanso.com') !== false  ||
            stripos($email, '@toomail.biz') !== false  ||
            stripos($email, '@toon.ml') !== false  ||
            stripos($email, '@top1mail.ru') !== false  ||
            stripos($email, '@top1post.ru') !== false  ||
            stripos($email, '@top101.de') !== false  ||
            stripos($email, '@topinrock.cf') !== false  ||
            stripos($email, '@topofertasdehoy.com') !== false  ||
            stripos($email, '@toppieter.com') !== false  ||
            stripos($email, '@topranklist.de') !== false  ||
            stripos($email, '@toprumours.com') !== false  ||
            stripos($email, '@tormail.org') !== false  ||
            stripos($email, '@toss.pw') !== false  ||
            stripos($email, '@tosunkaya.com') !== false  ||
            stripos($email, '@totalvista.com') !== false  ||
            stripos($email, '@totesmail.com') !== false  ||
            stripos($email, '@totoan.info') !== false  ||
            stripos($email, '@tourbalitravel.com') !== false  ||
            stripos($email, '@tp-qa-mail.com') !== false  ||
            stripos($email, '@tqoai.com') !== false  ||
            stripos($email, '@tqosi.com') !== false  ||
            stripos($email, '@tradermail.info') !== false  ||
            stripos($email, '@tranceversal.com') !== false  ||
            stripos($email, '@trash2009.com') !== false  ||
            stripos($email, '@trash2010.com') !== false  ||
            stripos($email, '@trash2011.com') !== false  ||
            stripos($email, '@trash-amil.com') !== false  ||
            stripos($email, '@trash-mail.at') !== false  ||
            stripos($email, '@trash-mail.cf') !== false  ||
            stripos($email, '@trash-mail.com') !== false  ||
            stripos($email, '@trash-mail.de') !== false  ||
            stripos($email, '@trash-mail.ga') !== false  ||
            stripos($email, '@trash-mail.gq') !== false  ||
            stripos($email, '@trash-mail.ml') !== false  ||
            stripos($email, '@trash-mail.tk') !== false  ||
            stripos($email, '@trash-me.com') !== false  ||
            stripos($email, '@trashcanmail.com') !== false  ||
            stripos($email, '@trashdevil.com') !== false  ||
            stripos($email, '@trashdevil.de') !== false  ||
            stripos($email, '@trashemail.de') !== false  ||
            stripos($email, '@trashemails.de') !== false  ||
            stripos($email, '@trashinbox.com') !== false  ||
            stripos($email, '@trashmail.at') !== false  ||
            stripos($email, '@trashmail.com') !== false  ||
            stripos($email, '@trashmail.de') !== false  ||
            stripos($email, '@trashmail.gq') !== false  ||
            stripos($email, '@trashmail.io') !== false  ||
            stripos($email, '@trashmail.me') !== false  ||
            stripos($email, '@trashmail.net') !== false  ||
            stripos($email, '@trashmail.org') !== false  ||
            stripos($email, '@trashmail.ws') !== false  ||
            stripos($email, '@trashmailer.com') !== false  ||
            stripos($email, '@trashmails.com') !== false  ||
            stripos($email, '@trashymail.com') !== false  ||
            stripos($email, '@trashymail.net') !== false  ||
            stripos($email, '@trasz.com') !== false  ||
            stripos($email, '@trayna.com') !== false  ||
            stripos($email, '@trbvm.com') !== false  ||
            stripos($email, '@trbvn.com') !== false  ||
            stripos($email, '@trbvo.com') !== false  ||
            stripos($email, '@trgovinanaveliko.info') !== false  ||
            stripos($email, '@trialmail.de') !== false  ||
            stripos($email, '@trickmail.net') !== false  ||
            stripos($email, '@trillianpro.com') !== false  ||
            stripos($email, '@trollproject.com') !== false  ||
            stripos($email, '@tropicalbass.info') !== false  ||
            stripos($email, '@trungtamtoeic.com') !== false  ||
            stripos($email, '@tryalert.com') !== false  ||
            stripos($email, '@ttszuo.xyz') !== false  ||
            stripos($email, '@tualias.com') !== false  ||
            stripos($email, '@tuongtactot.tk') !== false  ||
            stripos($email, '@turoid.com') !== false  ||
            stripos($email, '@turual.com') !== false  ||
            stripos($email, '@tuyulmokad.tk') !== false  ||
            stripos($email, '@tvchd.com') !== false  ||
            stripos($email, '@tverya.com') !== false  ||
            stripos($email, '@twinmail.de') !== false  ||
            stripos($email, '@twkly.ml') !== false  ||
            stripos($email, '@twocowmail.net') !== false  ||
            stripos($email, '@twoweirdtricks.com') !== false  ||
            stripos($email, '@txpwg.usa.cc') !== false  ||
            stripos($email, '@txt.flu.cc') !== false  ||
            stripos($email, '@txtadvertise.com') !== false  ||
            stripos($email, '@tyhe.ro') !== false  ||
            stripos($email, '@tyldd.com') !== false  ||
            stripos($email, '@u.0u.ro') !== false  ||
            stripos($email, '@uacro.com') !== false  ||
            stripos($email, '@ubismail.net') !== false  ||
            stripos($email, '@ubm.md') !== false  ||
            stripos($email, '@ucche.us') !== false  ||
            stripos($email, '@ucupdong.ml') !== false  ||
            stripos($email, '@uemail99.com') !== false  ||
            stripos($email, '@ufacturing.com') !== false  ||
            stripos($email, '@uggsrock.com') !== false  ||
            stripos($email, '@ugimail.net') !== false  ||
            stripos($email, '@uguuchantele.com') !== false  ||
            stripos($email, '@uhhu.ru') !== false  ||
            stripos($email, '@uiu.us') !== false  ||
            stripos($email, '@ujijima1129.gq') !== false  ||
            stripos($email, '@uk.to') !== false  ||
            stripos($email, '@umail.net') !== false  ||
            stripos($email, '@undo.it') !== false  ||
            stripos($email, '@unids.com') !== false  ||
            stripos($email, '@unimark.org') !== false  ||
            stripos($email, '@unit7lahaina.com') !== false  ||
            stripos($email, '@unmail.ru') !== false  ||
            stripos($email, '@upliftnow.com') !== false  ||
            stripos($email, '@uplipht.com') !== false  ||
            stripos($email, '@uploadnolimit.com') !== false  ||
            stripos($email, '@upozowac.info') !== false  ||
            stripos($email, '@urfunktion.se') !== false  ||
            stripos($email, '@uroid.com') !== false  ||
            stripos($email, '@us.af') !== false  ||
            stripos($email, '@us.to') !== false  ||
            stripos($email, '@usa-cc.usa.cc') !== false  ||
            stripos($email, '@usako.net') !== false  ||
            stripos($email, '@uscaves.com') !== false  ||
            stripos($email, '@used-product.fr') !== false  ||
            stripos($email, '@ushijima1129.cf') !== false  ||
            stripos($email, '@ushijima1129.ga') !== false  ||
            stripos($email, '@ushijima1129.gq') !== false  ||
            stripos($email, '@ushijima1129.ml') !== false  ||
            stripos($email, '@ushijima1129.tk') !== false  ||
            stripos($email, '@utiket.us') !== false  ||
            stripos($email, '@uu2.ovh') !== false  ||
            stripos($email, '@uu.gl') !== false  ||
            stripos($email, '@uwork4.us') !== false  ||
            stripos($email, '@uyhip.com') !== false  ||
            stripos($email, '@vaati.org') !== false  ||
            stripos($email, '@valemail.net') !== false  ||
            stripos($email, '@valhalladev.com') !== false  ||
            stripos($email, '@vankin.de') !== false  ||
            stripos($email, '@vaultsophiaonline.com') !== false  ||
            stripos($email, '@vctel.com') !== false  ||
            stripos($email, '@vda.ro') !== false  ||
            stripos($email, '@vdig.com') !== false  ||
            stripos($email, '@vektik.com') !== false  ||
            stripos($email, '@vemomail.win') !== false  ||
            stripos($email, '@venompen.com') !== false  ||
            stripos($email, '@veo.kr') !== false  ||
            stripos($email, '@ver0.cf') !== false  ||
            stripos($email, '@ver0.ga') !== false  ||
            stripos($email, '@ver0.gq') !== false  ||
            stripos($email, '@ver0.ml') !== false  ||
            stripos($email, '@ver0.tk') !== false  ||
            stripos($email, '@vercelli.cf') !== false  ||
            stripos($email, '@vercelli.ga') !== false  ||
            stripos($email, '@vercelli.gq') !== false  ||
            stripos($email, '@vercelli.ml') !== false  ||
            stripos($email, '@verdejo.com') !== false  ||
            stripos($email, '@veryday.ch') !== false  ||
            stripos($email, '@veryday.eu') !== false  ||
            stripos($email, '@veryday.info') !== false  ||
            stripos($email, '@veryrealemail.com') !== false  ||
            stripos($email, '@vesa.pw') !== false  ||
            stripos($email, '@vfemail.net') !== false  ||
            stripos($email, '@via.tokyo.jp') !== false  ||
            stripos($email, '@victime.ninja') !== false  ||
            stripos($email, '@victoriantwins.com') !== false  ||
            stripos($email, '@vidchart.com') !== false  ||
            stripos($email, '@viditag.com') !== false  ||
            stripos($email, '@viewcastmedia.com') !== false  ||
            stripos($email, '@viewcastmedia.net') !== false  ||
            stripos($email, '@viewcastmedia.org') !== false  ||
            stripos($email, '@vikingsonly.com') !== false  ||
            stripos($email, '@vinernet.com') !== false  ||
            stripos($email, '@vipepe.com') !== false  ||
            stripos($email, '@vipmail.name') !== false  ||
            stripos($email, '@vipmail.pw') !== false  ||
            stripos($email, '@vipxm.net') !== false  ||
            stripos($email, '@viralplays.com') !== false  ||
            stripos($email, '@visal007.tk') !== false  ||
            stripos($email, '@visal168.cf') !== false  ||
            stripos($email, '@visal168.ga') !== false  ||
            stripos($email, '@visal168.gq') !== false  ||
            stripos($email, '@visal168.ml') !== false  ||
            stripos($email, '@visal168.tk') !== false  ||
            stripos($email, '@vixletdev.com') !== false  ||
            stripos($email, '@vkcode.ru') !== false  ||
            stripos($email, '@vlwomhm.xyz') !== false  ||
            stripos($email, '@vmailing.info') !== false  ||
            stripos($email, '@vmani.com') !== false  ||
            stripos($email, '@vmpanda.com') !== false  ||
            stripos($email, '@vnedu.me') !== false  ||
            stripos($email, '@voidbay.com') !== false  ||
            stripos($email, '@vomoto.com') !== false  ||
            stripos($email, '@vorga.org') !== false  ||
            stripos($email, '@votiputox.org') !== false  ||
            stripos($email, '@voxelcore.com') !== false  ||
            stripos($email, '@vpn.st') !== false  ||
            stripos($email, '@vps30.com') !== false  ||
            stripos($email, '@vps911.net') !== false  ||
            stripos($email, '@vpstraffic.com') !== false  ||
            stripos($email, '@vrmtr.com') !== false  ||
            stripos($email, '@vsimcard.com') !== false  ||
            stripos($email, '@vssms.com') !== false  ||
            stripos($email, '@vtxmail.us') !== false  ||
            stripos($email, '@vubby.com') !== false  ||
            stripos($email, '@vuiy.pw') !== false  ||
            stripos($email, '@vztc.com') !== false  ||
            stripos($email, '@w3internet.co.uk') !== false  ||
            stripos($email, '@wakingupesther.com') !== false  ||
            stripos($email, '@walala.org') !== false  ||
            stripos($email, '@walkmail.net') !== false  ||
            stripos($email, '@walkmail.ru') !== false  ||
            stripos($email, '@wallm.com') !== false  ||
            stripos($email, '@wasteland.rfc822.org') !== false  ||
            stripos($email, '@watch-harry-potter.com') !== false  ||
            stripos($email, '@watchever.biz') !== false  ||
            stripos($email, '@watchfull.net') !== false  ||
            stripos($email, '@watchironman3onlinefreefullmovie.com') !== false  ||
            stripos($email, '@wazabi.club') !== false  ||
            stripos($email, '@wbml.net') !== false  ||
            stripos($email, '@web2mailco.com') !== false  ||
            stripos($email, '@web-ideal.fr') !== false  ||
            stripos($email, '@web-mail.pp.ua') !== false  ||
            stripos($email, '@webcontact-france.eu') !== false  ||
            stripos($email, '@webemail.me') !== false  ||
            stripos($email, '@webm4il.info') !== false  ||
            stripos($email, '@webmail.igg.biz') !== false  ||
            stripos($email, '@webtrip.ch') !== false  ||
            stripos($email, '@webuser.in') !== false  ||
            stripos($email, '@wee.my') !== false  ||
            stripos($email, '@wef.gr') !== false  ||
            stripos($email, '@wefjo.grn.cc') !== false  ||
            stripos($email, '@weg-werf-email.de') !== false  ||
            stripos($email, '@wegwerf-email-addressen.de') !== false  ||
            stripos($email, '@wegwerf-email-adressen.de') !== false  ||
            stripos($email, '@wegwerf-email.at') !== false  ||
            stripos($email, '@wegwerf-email.de') !== false  ||
            stripos($email, '@wegwerf-email.net') !== false  ||
            stripos($email, '@wegwerf-emails.de') !== false  ||
            stripos($email, '@wegwerfadresse.de') !== false  ||
            stripos($email, '@wegwerfemail.com') !== false  ||
            stripos($email, '@wegwerfemail.de') !== false  ||
            stripos($email, '@wegwerfemail.info') !== false  ||
            stripos($email, '@wegwerfemail.net') !== false  ||
            stripos($email, '@wegwerfemail.org') !== false  ||
            stripos($email, '@wegwerfemailadresse.com') !== false  ||
            stripos($email, '@wegwerfmail.de') !== false  ||
            stripos($email, '@wegwerfmail.info') !== false  ||
            stripos($email, '@wegwerfmail.net') !== false  ||
            stripos($email, '@wegwerfmail.org') !== false  ||
            stripos($email, '@wegwerpmailadres.nl') !== false  ||
            stripos($email, '@wegwrfmail.de') !== false  ||
            stripos($email, '@wegwrfmail.net') !== false  ||
            stripos($email, '@wegwrfmail.org') !== false  ||
            stripos($email, '@welikecookies.com') !== false  ||
            stripos($email, '@wetrainbayarea.com') !== false  ||
            stripos($email, '@wetrainbayarea.org') !== false  ||
            stripos($email, '@wg0.com') !== false  ||
            stripos($email, '@wh4f.org') !== false  ||
            stripos($email, '@whatiaas.com') !== false  ||
            stripos($email, '@whatifanalytics.com') !== false  ||
            stripos($email, '@whatpaas.com') !== false  ||
            stripos($email, '@whatsaas.com') !== false  ||
            stripos($email, '@whiffles.org') !== false  ||
            stripos($email, '@whopy.com') !== false  ||
            stripos($email, '@whyspam.me') !== false  ||
            stripos($email, '@wibblesmith.com') !== false  ||
            stripos($email, '@wickmail.net') !== false  ||
            stripos($email, '@widaryanto.info') !== false  ||
            stripos($email, '@widget.gg') !== false  ||
            stripos($email, '@wierie.tk') !== false  ||
            stripos($email, '@wikidocuslava.ru') !== false  ||
            stripos($email, '@wilemail.com') !== false  ||
            stripos($email, '@willhackforfood.biz') !== false  ||
            stripos($email, '@willselfdestruct.com') !== false  ||
            stripos($email, '@wimsg.com') !== false  ||
            stripos($email, '@winemaven.info') !== false  ||
            stripos($email, '@wins.com.br') !== false  ||
            stripos($email, '@wlist.ro') !== false  ||
            stripos($email, '@wmail.cf') !== false  ||
            stripos($email, '@wmail.club') !== false  ||
            stripos($email, '@wolfsmail.tk') !== false  ||
            stripos($email, '@wolfsmails.tk') !== false  ||
            stripos($email, '@wollan.info') !== false  ||
            stripos($email, '@worldspace.link') !== false  ||
            stripos($email, '@wpg.im') !== false  ||
            stripos($email, '@wralawfirm.com') !== false  ||
            stripos($email, '@writeme.us') !== false  ||
            stripos($email, '@wronghead.com') !== false  ||
            stripos($email, '@wudet.men') !== false  ||
            stripos($email, '@wuespdj.xyz') !== false  ||
            stripos($email, '@wupics.com') !== false  ||
            stripos($email, '@wuzup.net') !== false  ||
            stripos($email, '@wuzupmail.net') !== false  ||
            stripos($email, '@www.e4ward.com') !== false  ||
            stripos($email, '@www.gishpuppy.com') !== false  ||
            stripos($email, '@www.mailinator.com') !== false  ||
            stripos($email, '@wwwnew.eu') !== false  ||
            stripos($email, '@wxnw.net') !== false  ||
            stripos($email, '@x24.com') !== false  ||
            stripos($email, '@x.0x01.tk') !== false  ||
            stripos($email, '@x.ip6.li') !== false  ||
            stripos($email, '@xagloo.co') !== false  ||
            stripos($email, '@xagloo.com') !== false  ||
            stripos($email, '@xbaby69.top') !== false  ||
            stripos($email, '@xcode.ro') !== false  ||
            stripos($email, '@xcompress.com') !== false  ||
            stripos($email, '@xcpy.com') !== false  ||
            stripos($email, '@xemaps.com') !== false  ||
            stripos($email, '@xemne.com') !== false  ||
            stripos($email, '@xents.com') !== false  ||
            stripos($email, '@xiyaopin.cn') !== false  ||
            stripos($email, '@xjoi.com') !== false  ||
            stripos($email, '@xl.cx') !== false  ||
            stripos($email, '@xmail.com') !== false  ||
            stripos($email, '@xmaily.com') !== false  ||
            stripos($email, '@xn--9kq967o.com') !== false  ||
            stripos($email, '@xn--d-bga.net') !== false  ||
            stripos($email, '@xost.us') !== false  ||
            stripos($email, '@xoxox.cc') !== false  ||
            stripos($email, '@xoxy.net') !== false  ||
            stripos($email, '@xperiae5.com') !== false  ||
            stripos($email, '@xrho.com') !== false  ||
            stripos($email, '@xvx.us') !== false  ||
            stripos($email, '@xwaretech.com') !== false  ||
            stripos($email, '@xwaretech.info') !== false  ||
            stripos($email, '@xwaretech.net') !== false  ||
            stripos($email, '@xww.ro') !== false  ||
            stripos($email, '@xxhamsterxx.ga') !== false  ||
            stripos($email, '@xxi2.com') !== false  ||
            stripos($email, '@xxlocanto.us') !== false  ||
            stripos($email, '@xxolocanto.us') !== false  ||
            stripos($email, '@xxqx3802.com') !== false  ||
            stripos($email, '@xyzfree.net') !== false  ||
            stripos($email, '@xzsok.com') !== false  ||
            stripos($email, '@yabai-oppai.tk') !== false  ||
            stripos($email, '@yahmail.top') !== false  ||
            stripos($email, '@yamail.win') !== false  ||
            stripos($email, '@yanet.me') !== false  ||
            stripos($email, '@yannmail.win') !== false  ||
            stripos($email, '@yapped.net') !== false  ||
            stripos($email, '@yaqp.com') !== false  ||
            stripos($email, '@ycare.de') !== false  ||
            stripos($email, '@ycn.ro') !== false  ||
            stripos($email, '@ye.vc') !== false  ||
            stripos($email, '@yedi.org') !== false  ||
            stripos($email, '@yep.it') !== false  ||
            stripos($email, '@yhg.biz') !== false  ||
            stripos($email, '@ynmrealty.com') !== false  ||
            stripos($email, '@yodx.ro') !== false  ||
            stripos($email, '@yogamaven.com') !== false  ||
            stripos($email, '@yomail.info') !== false  ||
            stripos($email, '@yoo.ro') !== false  ||
            stripos($email, '@yop.0x01.gq') !== false  ||
            stripos($email, '@yopmail.com') !== false  ||
            stripos($email, '@yopmail.fr') !== false  ||
            stripos($email, '@yopmail.gq') !== false  ||
            stripos($email, '@yopmail.net') !== false  ||
            stripos($email, '@yopmail.pp.ua') !== false  ||
            stripos($email, '@yopmail.usa.cc') !== false  ||
            stripos($email, '@yordanmail.cf') !== false  ||
            stripos($email, '@you-spam.com') !== false  ||
            stripos($email, '@yougotgoated.com') !== false  ||
            stripos($email, '@youmail.ga') !== false  ||
            stripos($email, '@youmailr.com') !== false  ||
            stripos($email, '@youneedmore.info') !== false  ||
            stripos($email, '@youpymail.com') !== false  ||
            stripos($email, '@youquwa.cn') !== false  ||
            stripos($email, '@yourdomain.com') !== false  ||
            stripos($email, '@youremail.cf') !== false  ||
            stripos($email, '@yourewronghereswhy.com') !== false  ||
            stripos($email, '@yourlms.biz') !== false  ||
            stripos($email, '@yourtube.ml') !== false  ||
            stripos($email, '@ypmail.webarnak.fr.eu.org') !== false  ||
            stripos($email, '@yspend.com') !== false  ||
            stripos($email, '@ytpayy.com') !== false  ||
            stripos($email, '@yueluqu.cn') !== false  ||
            stripos($email, '@yugasandrika.com') !== false  ||
            stripos($email, '@yui.it') !== false  ||
            stripos($email, '@yuirz.com') !== false  ||
            stripos($email, '@yuurok.com') !== false  ||
            stripos($email, '@yxzx.net') !== false  ||
            stripos($email, '@yyolf.net') !== false  ||
            stripos($email, '@z0d.eu') !== false  ||
            stripos($email, '@z1p.biz') !== false  ||
            stripos($email, '@z5cpw9pg8oiiuwylva.ml') !== false  ||
            stripos($email, '@z86.ru') !== false  ||
            stripos($email, '@zahuy.site') !== false  ||
            stripos($email, '@zain.site') !== false  ||
            stripos($email, '@zainmax.net') !== false  ||
            stripos($email, '@zaktouni.fr') !== false  ||
            stripos($email, '@zasod.com') !== false  ||
            stripos($email, '@zebins.com') !== false  ||
            stripos($email, '@zebins.eu') !== false  ||
            stripos($email, '@zehnminuten.de') !== false  ||
            stripos($email, '@zehnminutenmail.de') !== false  ||
            stripos($email, '@zepp.dk') !== false  ||
            stripos($email, '@zetmail.com') !== false  ||
            stripos($email, '@zfymail.com') !== false  ||
            stripos($email, '@zhaoyuanedu.cn') !== false  ||
            stripos($email, '@zhcne.com') !== false  ||
            stripos($email, '@zhewei88.com') !== false  ||
            stripos($email, '@zhorachu.com') !== false  ||
            stripos($email, '@zik.dj') !== false  ||
            stripos($email, '@zipcad.com') !== false  ||
            stripos($email, '@zippymail.info') !== false  ||
            stripos($email, '@zipsendtest.com') !== false  ||
            stripos($email, '@zoaxe.com') !== false  ||
            stripos($email, '@zoemail.com') !== false  ||
            stripos($email, '@zoemail.net') !== false  ||
            stripos($email, '@zoemail.org') !== false  ||
            stripos($email, '@zoetropes.org') !== false  ||
            stripos($email, '@zombie-hive.com') !== false  ||
            stripos($email, '@zomg.info') !== false  ||
            stripos($email, '@zoqqa.com') !== false  ||
            stripos($email, '@zumpul.com') !== false  ||
            stripos($email, '@zv68.com') !== false  ||
            stripos($email, '@zxcv.com') !== false  ||
            stripos($email, '@zxcvbnm.com') !== false  ||
            stripos($email, '@zymuying.com') !== false  ||
            stripos($email, '@zzi.us') !== false  ||
            stripos($email, '@zzz.com') !== false ||
            stripos($email, '@datenschutz.ru') !== false  ||
            stripos($email, '@existiert.net') !== false  ||
            stripos($email, '@dsgvo.ru') !== false  ||
            stripos($email, '@grugrug.ru') !== false  ||
            stripos($email, '@kaengu.ru') !== false  ||
            stripos($email, '@muellmail.com') !== false  ||
            stripos($email, '@muellemail.com') !== false  ||
            stripos($email, '@muell.monster') !== false  ||
            stripos($email, '@muell.icu') !== false  ||
            stripos($email, '@muell.xyz') !== false  ||
            stripos($email, '@magspam.net') !== false  ||
            stripos($email, '@fukaru.com') !== false  ||
            stripos($email, '@oida.icu') !== false  ||
            stripos($email, '@papierkorb.me') !== false  ||
            stripos($email, '@spam.care') !== false  ||
            stripos($email, '@tonne.to') !== false  ||
            stripos($email, '@ultra.fyi') !== false  ||
            stripos($email, '@yeezus.ru') !== false 
            ) {
            return true;
        } else {
            return false;
        }
    }
}
