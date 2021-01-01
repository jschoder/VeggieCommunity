<?php
namespace vc\controller\web\user;

class EditController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'register/');
        }

        // Check status of profile. Block if sending user has been deleted.
        if ($this->autoLogout()) {
            throw new \vc\exception\RedirectException($this->path);
        }

        $termsModel = $this->getDbModel('Terms');
        if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
            throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
        }

        $profileId = $this->getSession()->getUserId();
        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getProfiles($this->locale, array($profileId));

        $form = $this->createForm();
        $form->setObject($profiles[0]);

        $questionaires = $profileModel->getQuestionaires($profileId);
        $defaultFormValues = array();
        foreach ($questionaires as $groupId => $group) {
            foreach ($group as $questionaireId => $questionaire) {
                $defaultFormValues['questionaire' . $groupId . $questionaireId] = $questionaire;
            }
        }

        $profileHobbyModel = $this->getDbModel('ProfileHobby');
        $selectedHobbies = $profileHobbyModel->getFieldList('hobbyid', array('profileid' => $profileId));
        $defaultFormValues['hobbies'] = $selectedHobbies;

        $form->setDefaultValues($defaultFormValues);

        $this->view($form);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'register/');
        }

        // Check status of profile. Block if sending user has been deleted.
        if ($this->autoLogout()) {
            throw new \vc\exception\RedirectException($this->path);
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $formValues = $_POST;
        if (empty($formValues['formid'])) {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    if ($this->saveData($form, $formValues)) {
                        $this->createFriendMailNotifications(
                            $this->getSession()->getUserId(),
                            $this->getSession()->getProfile()->nickname
                        );

                        $profileModel = $this->getDbModel('Profile');
                        $profiles = $profileModel->getProfiles($this->locale, array($this->getSession()->getUserId()));
                        $this->getSession()->updateSession($profiles[0]);

                        $this->getEventService()->edited(
                            \vc\config\EntityTypes::PROFILE,
                            $this->getSession()->getUserId(),
                            $this->getSession()->getUserId()
                        );

                        $notification = $this->setNotification(
                            self::NOTIFICATION_SUCCESS,
                            gettext('edit.save.success')
                        );
                    } else {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('edit.save.failed')
                        );
                    }

                    throw new \vc\exception\RedirectException(
                        $this->path . 'user/share/' . $this->getSession()->getUserId() .
                        '/?notification=' . $notification
                    );
                } else {
                    $this->getView()->set(
                        'notification',
                        array('type' => self::NOTIFICATION_WARNING, 'message' => gettext('form.validationFailed'))
                    );
                    $form->setDefaultValues($formValues);
                    $this->view($form);
                }
            } else {
                throw new \vc\exception\RedirectException($this->path . 'user/edit/');
            }
        }
    }

    private function createForm()
    {
        $cacheModel = $this->getModel('Cache');

        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'UserEdit',
            $this->path,
            $this->locale,
            'user/edit/'
        );

        $infoGroup = new \vc\form\Group(gettext('profile.tab.info'));
        $infoGroup->setId('groupInfo');
        $form->add($infoGroup);

        $infoGroup->add(new \vc\form\Text(
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
              'active > 0',
              'id',
              $this->getSession()->getUserId()
          ));

        $infoGroup->add(new \vc\form\Text(
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
              'active > 0',
              'id',
              $this->getSession()->getUserId()
          ));

        $infoGroup->add(new \vc\form\Select(
            'gender',
            'gender',
            gettext('profile.gender'),
            \vc\config\Fields::getGenderFields(),
            trim(gettext('profile.gender.edithelp'))
        ))->setMandatory(true);

        $infoGroup->add(new \vc\form\Checkbox(
            'hideAge',
            'hide_age',
            gettext('profile.hide_age'),
            false,
            gettext('profile.hide_age.edithelp')
        ));

        $infoGroup->add(new \vc\form\Select(
            'zodiac',
            'zodiac',
            gettext('profile.zodiac'),
            \vc\config\Fields::getZodiacFields(),
            trim(gettext('profile.zodiac.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'nutrition',
            'nutrition',
            gettext('profile.nutrition'),
            \vc\config\Fields::getNutritionFields(),
            trim(gettext('profile.nutrition.edithelp'))
        ))->setMandatory(true);

        $infoGroup->add(new \vc\form\Text(
            'nutritionFreetext',
            'nutrition_freetext',
            gettext('profile.nutrition_freetext'),
            25,
            trim(gettext('profile.nutrition_freetext.edithelp'))
        ));

        $infoGroup->add(new \vc\form\custom\Search(
            'search',
            'search',
            gettext('profile.search'),
            \vc\config\Fields::getSearchFields(),
            trim(gettext('profile.search.edithelp'))
        ));

        $infoGroup->add(new \vc\form\NumberRange(
            'ageFromFriends',
            'ageToFriends',
            'ageFriends',
            gettext('profile.agespectrum.friends'),
            8,
            120,
            trim(gettext('profile.agespectrum.friends.edithelp'))
        ));

        $infoGroup->add(new \vc\form\NumberRange(
            'ageFromRomantic',
            'ageToRomantic',
            'ageRomantic',
            gettext('profile.agespectrum.romantic'),
            14,
            120,
            trim(gettext('profile.agespectrum.romantic.edithelp'))
        ))->addValidator(new \vc\form\validation\PedobearAgeValidator(
            $this->getSession()->getUserId(),
            $this->getSession()->getProfile()->age,
            $this->getIp(),
            14,
            120
        ));

        $infoGroup->add(new \vc\form\Text(
            'postalcode',
            'postalcode',
            gettext('profile.postalcode'),
            10,
            trim(gettext('profile.postalcode.edithelp'))
        ));
        $infoGroup->add(new \vc\form\Text(
            'city',
            'city',
            gettext('profile.city'),
            30,
            trim(gettext('profile.city.edithelp'))
        ));
        $countries = $cacheModel->getCountries($this->locale);
        $indexedCountries = array();
        foreach ($countries as $country) {
            $indexedCountries[$country[0]] = $country[1];
        }
        $countrySelect = $infoGroup->add(new \vc\form\Select(
            'country',
            'country',
            gettext('profile.country'),
            $indexedCountries,
            trim(gettext('profile.country.edithelp'))
        ))->setMandatory(true)
          ->setFilterField('region');

        $regions = \vc\config\Fields::getRegions();
        $indexedRegions = array();
        $indexedRegions[''] = gettext('profile.no_statement');
        $regionGroups = array();
        foreach ($regions as $countryId => $countryRegions) {
            $countrySelect->setFilterGroup($countryId, $countryId);
            foreach ($countryRegions as $region) {
                $indexedRegions[$region] = $region;
                $regionGroups[$region] = $countryId;
            }
        }
        $infoGroup->add(new \vc\form\Select(
            'region',
            'region',
            gettext('profile.region'),
            $indexedRegions,
            trim(gettext('profile.region.edithelp'))
        ))->setGroups($regionGroups);

        $infoGroup->add(new \vc\form\Select(
            'smoking',
            'smoking',
            gettext('profile.smoking'),
            \vc\config\Fields::getSmokingFields(),
            trim(gettext('profile.smoking.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'alcohol',
            'alcohol',
            gettext('profile.alcohol'),
            \vc\config\Fields::getAlcoholFields(),
            trim(gettext('profile.alcohol.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'religion',
            'religion',
            gettext('profile.religion'),
            \vc\config\Fields::getReligionFields(),
            trim(gettext('profile.religion.edithelp'))
        ));

        $infoGroup->add(new \vc\form\MultipleChoice(
            'political',
            'political',
            gettext('profile.political'),
            \vc\config\Fields::getPoliticalFields(),
            trim(gettext('profile.political.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'marital',
            'marital',
            gettext('profile.marital'),
            \vc\config\Fields::getMaritalFields(),
            trim(gettext('profile.marital.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'children',
            'children',
            gettext('profile.children'),
            \vc\config\Fields::getChildrenFields(),
            trim(gettext('profile.children.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'relocate',
            'relocate',
            gettext('profile.relocate'),
            \vc\config\Fields::getRelocateFields(),
            trim(gettext('profile.relocate.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'bodytype',
            'bodytype',
            gettext('profile.bodytype'),
            \vc\config\Fields::getBodyTypeFields(),
            trim(gettext('profile.bodytype.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'bodyheight',
            'bodyheight',
            gettext('profile.bodyheight'),
            \vc\config\Fields::getBodyHeightFields(),
            trim(gettext('profile.bodyheight.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'clothing',
            'clothing',
            gettext('profile.clothing'),
            \vc\config\Fields::getClothingFields(),
            trim(gettext('profile.clothing.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'haircolor',
            'haircolor',
            gettext('profile.haircolor'),
            \vc\config\Fields::getHairColorFields(),
            trim(gettext('profile.haircolor.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Select(
            'eyecolor',
            'eyecolor',
            gettext('profile.eyecolor'),
            \vc\config\Fields::getEyeColorFields(),
            trim(gettext('profile.eyecolor.edithelp'))
        ));

        $infoGroup->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        $questionaire1Group = new \vc\form\Group(gettext('profile.tab.questionaire.1'));
        $questionaire1Group->setId('groupQuestionaire1');
        $form->add($questionaire1Group);

        $questionaire1Group->add(new \vc\form\Checkbox(
            'tabQuestionaire1Hide',
            'tabQuestionaire1Hide',
            gettext('edit.tab.hide.option')
        ));

        $questionaire1Group->add(new \vc\form\Text(
            'word1',
            'word1',
            gettext('profile.threewords'),
            25
        ));
        $questionaire1Group->add(new \vc\form\Text(
            'word2',
            'word2',
            null,
            25
        ));
        $questionaire1Group->add(new \vc\form\Text(
            'word3',
            'word3',
            null,
            25
        ));

        $questionaire1Group->add(new \vc\form\Text(
            null,
            'questionaire11',
            gettext('profile.questionaire.1.1'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire1Group->add(new \vc\form\Text(
            null,
            'questionaire12',
            gettext('profile.questionaire.1.2'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire1Group->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        $questionaire2Group = new \vc\form\Group(gettext('profile.tab.questionaire.2'));
        $questionaire2Group->setId('groupQuestionaire2');
        $form->add($questionaire2Group);

        $questionaire2Group->add(new \vc\form\Checkbox(
            'tabQuestionaire2Hide',
            'tabQuestionaire2Hide',
            gettext('edit.tab.hide.option')
        ));

        $questionaire2Group->add(new \vc\form\Text(
            null,
            'questionaire21',
            gettext('profile.questionaire.2.1'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire2Group->add(new \vc\form\Text(
            null,
            'questionaire22',
            gettext('profile.questionaire.2.2'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire2Group->add(new \vc\form\Text(
            null,
            'questionaire23',
            gettext('profile.questionaire.2.3'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire2Group->add(new \vc\form\Text(
            null,
            'questionaire24',
            gettext('profile.questionaire.2.4'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire2Group->add(new \vc\form\Text(
            null,
            'questionaire25',
            gettext('profile.questionaire.2.5'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire2Group->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        $questionaire3Group = new \vc\form\Group(gettext('profile.tab.questionaire.3'));
        $questionaire3Group->setId('groupQuestionaire3');
        $form->add($questionaire3Group);

        $questionaire3Group->add(new \vc\form\Checkbox(
            'tabQuestionaire3Hide',
            'tabQuestionaire3Hide',
            gettext('edit.tab.hide.option')
        ));

        $questionaire3Group->add(new \vc\form\Text(
            null,
            'questionaire31',
            gettext('profile.questionaire.3.1'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire3Group->add(new \vc\form\Text(
            null,
            'questionaire32',
            gettext('profile.questionaire.3.2'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire3Group->add(new \vc\form\Text(
            null,
            'questionaire33',
            gettext('profile.questionaire.3.3'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire3Group->add(new \vc\form\Text(
            null,
            'questionaire34',
            gettext('profile.questionaire.3.4'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire3Group->add(new \vc\form\Text(
            null,
            'questionaire35',
            gettext('profile.questionaire.3.5'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire3Group->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        $questionaire4Group = new \vc\form\Group(gettext('profile.tab.questionaire.4'));
        $questionaire4Group->setId('groupQuestionaire4');
        $form->add($questionaire4Group);

        $questionaire4Group->add(new \vc\form\Checkbox(
            'tabQuestionaire4Hide',
            'tabQuestionaire4Hide',
            gettext('edit.tab.hide.option')
        ));

        $questionaire4Group->add(new \vc\form\Text(
            null,
            'questionaire41',
            gettext('profile.questionaire.4.1'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire4Group->add(new \vc\form\Text(
            null,
            'questionaire42',
            gettext('profile.questionaire.4.2'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire4Group->add(new \vc\form\Text(
            null,
            'questionaire43',
            gettext('profile.questionaire.4.3'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire4Group->add(new \vc\form\Text(
            null,
            'questionaire44',
            gettext('profile.questionaire.4.4'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire4Group->add(new \vc\form\Text(
            null,
            'questionaire45',
            gettext('profile.questionaire.4.5'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire4Group->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        $questionaire5Group = new \vc\form\Group(gettext('profile.tab.questionaire.5'));
        $questionaire5Group->setId('groupQuestionaire5');
        $form->add($questionaire5Group);

        $questionaire5Group->add(new \vc\form\Checkbox(
            'tabQuestionaire5Hide',
            'tabQuestionaire5Hide',
            gettext('edit.tab.hide.option')
        ));

        $questionaire5Group->add(new \vc\form\Text(
            null,
            'questionaire51',
            gettext('profile.questionaire.5.1'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire5Group->add(new \vc\form\Text(
            null,
            'questionaire52',
            gettext('profile.questionaire.5.2'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire5Group->add(new \vc\form\Text(
            null,
            'questionaire53',
            gettext('profile.questionaire.5.3'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire5Group->add(new \vc\form\Text(
            null,
            'questionaire54',
            gettext('profile.questionaire.5.4'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire5Group->add(new \vc\form\Text(
            null,
            'questionaire55',
            gettext('profile.questionaire.5.5'),
            20000,
            null,
            \vc\form\Text::TEXTAREA
        ));

        $questionaire5Group->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        $hobbiesGroup = new \vc\form\Group(gettext('profile.tab.hobbies'));
        $hobbiesGroup->setId('groupHobbies');
        $form->add($hobbiesGroup);

        $hobbiesGroup->add(new \vc\form\InfoText(
            'notifyInfo',
            gettext('edit.tab.hide.auto')
        ));
        $hobbiesGroup->add(new \vc\form\Text(
            'homepage',
            'homepage',
            gettext('profile.homepage'),
            255,
            trim(gettext('profile.homepage.edithelp'))
        ))->addValidator(new \vc\form\validation\UrlValidator());
        $hobbiesGroup->add(new \vc\form\Text(
            'favlink1',
            'favlink1',
            gettext('profile.favlink1'),
            255,
            trim(gettext('profile.favlink1.edithelp'))
        ))->addValidator(new \vc\form\validation\UrlValidator());
        $hobbiesGroup->add(new \vc\form\Text(
            'favlink2',
            'favlink2',
            gettext('profile.favlink2'),
            255,
            trim(gettext('profile.favlink2.edithelp'))
        ))->addValidator(new \vc\form\validation\UrlValidator());
        $hobbiesGroup->add(new \vc\form\Text(
            'favlink3',
            'favlink3',
            gettext('profile.favlink3'),
            255,
            trim(gettext('profile.favlink3.edithelp'))
        ))->addValidator(new \vc\form\validation\UrlValidator());

        $hobbies = $cacheModel->getHobbies($this->locale);

        foreach ($hobbies as $hobbyGroupId => $hobbyGroup) {
            $hobbiesGroup->add(new \vc\form\MultipleChoice(
                'hobbies',
                'hobbies',
                $hobbyGroup['title'],
                $hobbyGroup['hobbies']
            ))->setColumns(true);

            $hobbiesGroup->add(new \vc\form\Text(
                null,
                'questionaire6' . $hobbyGroupId,
                gettext('profile.hobbies.customtext'),
                20000,
                null,
                \vc\form\Text::TEXTAREA
            ));
        }

        $hobbiesGroup->add(new \vc\form\Submit(
            gettext('edit.submit')
        ));

        return $form;
    }

    private function saveData($form, $formValues)
    {
        $oldObject = clone($this->getSession()->getProfile());
        $object = $form->getObject($this->getSession()->getProfile(), $formValues);

        if ($oldObject->email != $object->email) {
            $profileEmailLogModel = $this->getDbModel('ProfileEmailLog');
            $profileEmailLogModel->addLog($this->getSession()->getUserId(), $object->email, $this->getIp());
        }

        // Only update geo info if the location actually changed.
        if ($oldObject->postalcode != $object->postalcode ||
            $oldObject->city != $object->city ||
            $oldObject->region != $object->region ||
            $oldObject->country != $object->country) {
            $geoComponent = $this->getComponent('Geo');
            $coordinates = $geoComponent->getCoordinates(
                $this->locale,
                $object->postalcode,
                $object->city,
                $object->region,
                $object->country
            );
            $object->latitude = $coordinates[0];
            $object->longitude = $coordinates[1];
        }
        // Recalculating it every time you save the object costs
        // less resources than loading it for every user every time
        $object->sinLatitude = sin(pi() * $object->latitude / 180);
        $object->cosLatitude = cos(pi() * $object->latitude / 180);
        $object->longitudeRadius = pi() * $object->longitude / 180;

        $object->lastUpdate = date('Y-m-d H:i:s');

        $profileModel = $this->getDbModel('Profile');
        $success = $profileModel->updateObject($this->getSession()->getProfile(), $object);

        if ($success) {
            $profileModel->setMultiValueField(
                'search',
                $this->getSession()->getUserId(),
                empty($formValues['search']) ? array() : $formValues['search']
            );
            $profileModel->setMultiValueField(
                'political',
                $this->getSession()->getUserId(),
                empty($formValues['political']) ? array() : $formValues['political']
            );

            $userId = $this->getSession()->getUserId();
            $this->saveQuestionaire($userId, 1, 1);
            $this->saveQuestionaire($userId, 1, 2);
            $this->saveQuestionaire($userId, 2, 1);
            $this->saveQuestionaire($userId, 2, 2);
            $this->saveQuestionaire($userId, 2, 3);
            $this->saveQuestionaire($userId, 2, 4);
            $this->saveQuestionaire($userId, 2, 5);
            $this->saveQuestionaire($userId, 3, 1);
            $this->saveQuestionaire($userId, 3, 2);
            $this->saveQuestionaire($userId, 3, 3);
            $this->saveQuestionaire($userId, 3, 4);
            $this->saveQuestionaire($userId, 3, 5);
            $this->saveQuestionaire($userId, 4, 1);
            $this->saveQuestionaire($userId, 4, 2);
            $this->saveQuestionaire($userId, 4, 3);
            $this->saveQuestionaire($userId, 4, 4);
            $this->saveQuestionaire($userId, 4, 5);
            $this->saveQuestionaire($userId, 5, 1);
            $this->saveQuestionaire($userId, 5, 2);
            $this->saveQuestionaire($userId, 5, 3);
            $this->saveQuestionaire($userId, 5, 4);
            $this->saveQuestionaire($userId, 5, 5);

            // Reading the questionairetexts
            $hobbyGroupModel = $this->getDbModel('HobbyGroup');
            $hobbyGroupIds = $hobbyGroupModel->getFieldList('id');
            foreach ($hobbyGroupIds as $hobbyGroupId) {
                $this->saveQuestionaire($userId, 6, $hobbyGroupId);
            }

            $profileHobbyModel = $this->getDbModel('ProfileHobby');
            $profileHobbyModel->updateProfileHobbies(
                $userId,
                empty($formValues['hobbies'])
                    ? array()
                    : array_map('intval', $formValues['hobbies'])
            );

            // Recalculate the the match-values if the search parameters changed
            if (empty($formValues['search'])) {
                $implodedQuerySearch = '';
            } else {
                $implodedQuerySearch = implode(',', $formValues['search']);
            }
            $implodedProfileSearch = implode(',', $oldObject->search);
            if ($implodedQuerySearch != $implodedProfileSearch ||
                $oldObject->ageFromFriends != intval($formValues['ageFriends']['from']) ||
                $oldObject->ageToFriends != intval($formValues['ageFriends']['to']) ||
                $oldObject->ageFromRomantic != intval($formValues['ageRomantic']['from']) ||
                $oldObject->ageToRomantic != intval($formValues['ageRomantic']['to'])) {
                $matchingModel = $this->getDbModel('Matching');
                $matchingModel->recalculate($this->getSession()->getUserId());
            }
            return true;
        } else {
            return false;
        }
    }

    private function saveQuestionaire($userId, $topic, $item)
    {
        $key = 'questionaire' . $topic . $item;
        $questionaireModel = $this->getDbModel('Questionaire');
        $questionaireModel->insertUpdate(
            $userId,
            $topic,
            $item,
            empty($_POST[$key]) ? '' : $_POST[$key]
        );
    }

    private function createFriendMailNotifications($profileid, $nickname)
    {
        $friendModel = $this->getDbModel('Friend');
        $friendIds = $friendModel->getFriendsToNotify($profileid);
        if (!empty($friendIds)) {
            $mailComponent = $this->getComponent('Mail');
            foreach ($friendIds as $friendId) {
                $mailComponent->sendMailToUser(
                    $this->locale,
                    $friendId,
                    'edit.emailnotification.friend.subject',
                    'friendchanged',
                    array(
                        'USERNAME' => $nickname,
                        'LINK' => 'user/view/' . $profileid . '/'
                    )
                );
            }
        }
    }

    private function view($form)
    {
        $this->setTitle(gettext('edit.title'));
        $this->getView()->set('activeMenuitem', 'mysite');

        $this->setForm($form);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('user/edit', true);
    }
}
