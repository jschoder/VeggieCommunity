<?php
namespace vc\controller\web\mod;

class RealController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $realCheckModel = $this->getDbModel('RealCheck');
        $realCheckObjects = $realCheckModel->loadObjects(array(
            'status' => array(
                \vc\object\RealCheck::STATUS_SUBMITTED,
                \vc\object\RealCheck::STATUS_REOPENED
            )
        ));

        $cacheModel = $this->getModel('Cache');
        $userLanguages = array();

        $pictureModel = $this->getDbModel('Picture');
        $profileIds = array();
        $profilePictures = array();
        foreach ($realCheckObjects as $realCheckObject) {
            $profileIds[] = $realCheckObject->profileId;
            $profilePictures[$realCheckObject->profileId] = $pictureModel->getAllProfilePictures(
                $realCheckObject->profileId
            );

            $settings = $cacheModel->getSettings($realCheckObject->profileId);
            $userLanguages[$realCheckObject->profileId] = $settings->getValue(\vc\object\Settings::USER_LANGUAGE);
        }

        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getProfiles($this->getLocale(), $profileIds);
        $indexedProfiles = array();
        foreach ($profiles as $profile) {
            $indexedProfiles[$profile->id] = $profile;
        }

        $this->setTitle('Real Check');

        $this->getView()->set('realCheckObjects', $realCheckObjects);
        $this->getView()->set('profiles', $indexedProfiles);
        $this->getView()->set('pictures', $profilePictures);
        $this->getView()->set('userLanguages', $userLanguages);
        echo $this->getView()->render('mod/real', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $formValues = $_POST;

        $profileModel = $this->getDbModel('Profile');
        $realCheckModel = $this->getDbModel('RealCheck');
        $realPictureModel = $this->getDbModel('RealPicture');

        foreach ($formValues['action'] as $realCheckId => $action) {
            if (($action == 'confirm' || $action == 'reconfirm') &&
                !empty($formValues['picture'][$realCheckId])) {
                $realCheckObject = $realCheckModel->loadObject(array(
                    'id' => intval($realCheckId)
                ));

                if ($realCheckObject !== null) {
                    // Change the status of the realCheck
                    $realCheckModel->update(
                        array(
                            'id' => intval($realCheckId)
                        ),
                        array(
                            'checked_by' => $this->getSession()->getUserId(),
                            'status' => \vc\object\RealCheck::STATUS_CONFIRMED,
                            'admin_comment' => empty($formValues['comment'][$realCheckId])
                                ? ''
                                : $formValues['comment'][$realCheckId]
                        )
                    );

                    // Add the pictures to the confirmed database
                    foreach ($formValues['picture'][$realCheckId] as $pictureId) {
                        $realPicture = new \vc\object\RealPicture();
                        $realPicture->pictureId = intval($pictureId);
                        $realPicture->realCheckId = intval($realCheckId);
                        $realPictureModel->insertObject($this->getSession()->getProfile(), $realPicture);
                    }

                    // Update the status of the user
                    $profileModel->update(
                        array(
                            'id' => $realCheckObject->profileId
                        ),
                        array(
                            'real_marker' => 1
                        )
                    );

                    if ($action == 'confirm') {
                        $mailComponent = $this->getComponent('Mail');
                        $mailComponent->sendMailToUser(
                            $this->locale,
                            $realCheckObject->profileId,
                            'real.email.confirm.subject',
                            'real-confirm'
                        );
                    }
                }
            } elseif (($action == 'deny' && !empty($formValues['usermessage'][$realCheckId])) ||
                      strpos($action, 'noarm.') === 0) {
                $realCheckObject = $realCheckModel->loadObject(array(
                    'id' => intval($realCheckId)
                ));

                if ($realCheckObject !== null) {
                    if (strpos($action, 'noarm.') === 0) {
                        $adminComment = 'No arm';
                        if ($action === 'noarm.de') {
                            $customMessage = 'Kein durchgehender Arm zwischen deinem Körper und dem Zettel erkennbar. Das Bild könnte daher leicht photoshopped sein.';
                        } else {
                            $customMessage = 'No continuous arm between your body and the piece of paper. The picture could therefore be easily photoshopped.';
                        }
                    } else {
                        if (empty($formValues['comment'][$realCheckId])) {
                            $adminComment = '';
                        } else {
                            $adminComment = $formValues['comment'][$realCheckId];
                        }
                        $customMessage = $formValues['usermessage'][$realCheckId];
                    }
                    $realCheckModel->update(
                        array(
                            'id' => intval($realCheckId)
                        ),
                        array(
                            'checked_by' => $this->getSession()->getUserId(),
                            'status' => \vc\object\RealCheck::STATUS_DENIED,
                            'admin_comment' => $adminComment
                        )
                    );

                    $mailComponent = $this->getComponent('Mail');
                    $mailComponent->sendMailToUser(
                        $this->locale,
                        $realCheckObject->profileId,
                        'real.email.deny.subject',
                        'real-deny',
                        array(
                            'CUSTOM_MESSAGE' => $customMessage
                        )
                    );
                }
            } elseif ($action == 'remove') {
                $realCheckObject = $realCheckModel->loadObject(array(
                    'id' => intval($realCheckId)
                ));

                if ($realCheckObject !== null) {
                    $realCheckModel->update(
                        array(
                            'id' => intval($realCheckId)
                        ),
                        array(
                            'checked_by' => $this->getSession()->getUserId(),
                            'status' => \vc\object\RealCheck::STATUS_REMOVED,
                            'admin_comment' => empty($formValues['comment'][$realCheckId])
                                ? ''
                                : $formValues['comment'][$realCheckId]
                        )
                    );

                    $mailComponent = $this->getComponent('Mail');
                    $mailComponent->sendMailToUser(
                        $this->locale,
                        $realCheckObject->profileId,
                        'real.email.removed.subject',
                        'real-removed'
                    );
                }
            }
        }

        throw new \vc\exception\RedirectException($this->path . 'mod/real/');
    }
}
