<?php
namespace vc\controller\web\user\pictures;

class PicturesController extends AbstractPicturesController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $defaultValues = array(
            'pictures' => array()
        );
        $visibilities = array(
            \vc\object\SavedPicture::VISIBILITY_FRIENDS => 0,
            \vc\object\SavedPicture::VISIBILITY_FRIENDS_FAVORITES => 0,
            \vc\object\SavedPicture::VISIBILITY_REGISTERED => 0,
            \vc\object\SavedPicture::VISIBILITY_PUBLIC => 0
        );

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->getAllProfilePictures($this->getSession()->getUserId());
        foreach ($pictures as $picture) {
            $defaultValues['pictures'][$picture->id] = array(
                'image' => $picture->filename,
                'description' => $picture->description,
                'visibility' => $picture->visibility,
                'weight' => $picture->weight
            );
            $visibilities[$picture->visibility] = $visibilities[$picture->visibility] + 1;

            if ($picture->defaultpic) {
                $defaultValues['pictures']['defaultpic'] = $picture->id;
            }
        }

        $defaultVisibility = \vc\object\SavedPicture::VISIBILITY_PUBLIC;
        if (!empty($visibilities)) {
            asort($visibilities);
            $defaultVisibility =  array_shift(array_reverse(array_keys($visibilities)));
        }

        $form = $this->createForm($defaultVisibility);
        $form->setDefaultValues($defaultValues);
        $this->view($form);
    }

    private function createForm($defaultVisibility)
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'UserPictures',
            $this->path,
            $this->locale,
            'user/pictures/'
        );

        if ($this->getSession()->getPlusLevel() >= \vc\object\Plus::PLUS_TYPE_STANDARD) {
            $maxPictures = \vc\config\Globals::MAX_PICTURES_PLUS;
        } else {
            $maxPictures = \vc\config\Globals::MAX_PICTURES_DEFAULT;
        }
        $multiple = new \vc\form\Multiple(
            'pictures',
            null,
            $maxPictures,
            gettext('user.picture.delete')
        );
        $form->add($multiple);
        $multiple->setSortable();
        $multiple->setClass('floating');

        $multiple->add(new \vc\form\AjaxImage(
            'pictures.image',
            'image',
            'user/picture',
            gettext('user.picture.fileupload')
        ))->setSmall(true);
        $multiple->add(new \vc\form\Text(
            'pictures.description',
            'description',
            gettext('user.picture.description'),
            200,
            gettext('user.picture.description.title')
        ))->setSmall(true);
        $visibilities = array(
            \vc\object\SavedPicture::VISIBILITY_PUBLIC => gettext('user.picture.visibility.public'),
            \vc\object\SavedPicture::VISIBILITY_REGISTERED => gettext('user.picture.visibility.registered'),
            \vc\object\SavedPicture::VISIBILITY_FRIENDS_FAVORITES => gettext('user.picture.visibility.friendsFavorites'),
            \vc\object\SavedPicture::VISIBILITY_FRIENDS => gettext('user.picture.visibility.friends')
        );
        $multiple->add(new \vc\form\Select(
            'pictures.visibility',
            'visibility',
            gettext('user.picture.visibility'),
            $visibilities
        ))->setSmall(true)
          ->setMandatory(true)
          ->setDefault($defaultVisibility);

        $multiple->add(new \vc\form\Radio(
            'pictures.defaultpic',
            'defaultpic',
            gettext('user.picture.profilepic')
        ));

        $form->add(new \vc\form\Submit(gettext('user.picture.submit')));
        return $form;
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $formValues = array_merge($_POST, $_FILES);
        if (empty($formValues['formid'])) {
            throw new \vc\exception\RedirectException($this->path . 'user/pictures/');
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $success = true;
                    $pictureErrors = array();

                    $pictureModel = $this->getDbModel('Picture');
                    $pictureChecklistModel = $this->getDbModel('PictureChecklist');

                    $updatedPics = 0;
                    $newPics = 0;
                    if ($this->getSession()->getPlusLevel() >= \vc\object\Plus::PLUS_TYPE_STANDARD) {
                        $maxPictures = \vc\config\Globals::MAX_PICTURES_PLUS;
                    } else {
                        $maxPictures = \vc\config\Globals::MAX_PICTURES_DEFAULT;
                    }

                    // Deleting pictures
                    $deletedPics = array();
                    if (!empty($formValues['pictures']['delete'])) {
                        // Deleting the feed entries before deleting the picture
                        foreach ($formValues['pictures']['delete'] as $pictureId) {
                            $this->getEventService()->preDelete(
                                \vc\config\EntityTypes::PROFILE_PICTURE,
                                intval($pictureId),
                                $this->getSession()->getUserId()
                            );
                        }

                        $deleted = $pictureModel->deletePictures(
                            $this->getSession()->getUserId(),
                            $formValues['pictures']['delete']
                        );

                        if ($deleted) {
                            $deletedPics = $formValues['pictures']['delete'];
                        } else {
                            $success = false;
                            $pictureErrors[] = gettext('pictures.save.failed.delete');
                        }
                        $pictureChecklistModel->clearDeletedPictures();
                    }

                    // Updating order/description/defaultPic
                    if (empty($formValues['pictures']['defaultpic'])) {
                        $defaultPic = 0;
                    } else {
                        $defaultPic = $formValues['pictures']['defaultpic'];
                    }
                    foreach ($formValues['pictures'] as $id => $picture) {
                        // Don't update new rows
                        if (is_numeric($id) &&
                            !in_array($id, $deletedPics) &&
                            array_key_exists('weight', $picture)) {
                            $updated = $pictureModel->update(
                                array(
                                    'id' => intval($id),
                                    'profileid' => $this->getSession()->getUserId()
                                ),
                                array(
                                    'defaultpic' => ($defaultPic == $id ? 1 : 0),
                                    'description' => $picture['description'],
                                    'visibility' => intval($picture['visibility']),
                                    'weight' => intval($picture['weight'])
                                )
                            );
                            if ($updated) {
                                $updatedPics++;
                            } else {
                                $success = false;
                                $pictureErrors[] = gettext('pictures.save.failed.update');
                            }
                        }
                    }

                    // Inserting picture
                    foreach ($formValues['pictures'] as $id => $picture) {
                        if (is_array($picture) && array_key_exists('image', $picture)) {
                            $imageFilename = $picture['image'];
                            // Make sure that nobody uses this script to get to a more sensitive path
                            $filename = preg_replace('@([^a-z,0-9,\.])@', '', $imageFilename);
                            if (!empty($filename)) {
                                $pictureSaveComponent = $this->getComponent('PictureSave');

                                if ($newPics + $updatedPics - count($deletedPics) <= $maxPictures) {
                                    if (file_exists(TEMP_PIC_DIR . '/full/' . $filename)) {
                                        $fileInfo = $pictureSaveComponent->movePic(
                                            TEMP_PIC_DIR . '/full/' . $filename,
                                            PROFILE_PIC_DIR . '/full/',
                                            $this->getSession()->getSetting(\vc\object\Settings::ROTATE_PICS, 1)
                                        );

                                        $this->testFilehash(PROFILE_PIC_DIR . '/full/' . $fileInfo['filename']);

                                        if ($fileInfo === false) {
                                            $success = false;
                                        } else {
                                            $pictureObject = new \vc\object\SavedPicture();
                                            $pictureObject->profileid = $this->getSession()->getUserId();
                                            $pictureObject->filename = $fileInfo['filename'];
                                            $pictureObject->description = $picture['description'];
                                            $pictureObject->visibility = intval($picture['visibility']);
                                            $pictureObject->weight = intval($picture['weight']);
                                            $pictureObject->defaultpic = ($defaultPic == $id ? 1 : 0);
                                            $pictureObject->width = $fileInfo['width'];
                                            $pictureObject->height = $fileInfo['height'];
                                            $smallSize = $pictureSaveComponent->getRescaleSize(
                                                PROFILE_PIC_DIR . '/full/' . $fileInfo['filename'],
                                                125,
                                                154,
                                                $fileInfo['exifOrientation']
                                            );

                                            $pictureObject->smallwidth = $smallSize[0];
                                            $pictureObject->smallheight = $smallSize[1];
                                            $pictureId = $pictureModel->insertObject(
                                                $this->getSession()->getProfile(),
                                                $pictureObject
                                            );

                                            if ($pictureId === false) {
                                                $success = false;
                                                $pictureErrors[] = gettext('pictures.save.failed.insert');
                                            } else {
                                                $pictureChecklist = new \vc\object\PictureChecklist();
                                                $pictureChecklist->id = $pictureId;
                                                $pictureChecklistModel->insertObject(
                                                    $this->getSession()->getProfile(),
                                                    $pictureChecklist
                                                );

                                                $this->getEventService()->added(
                                                    \vc\config\EntityTypes::PROFILE_PICTURE,
                                                    $pictureId,
                                                    $this->getSession()->getUserId()
                                                );

                                                $newPics++;
                                            }
                                        }
                                    } else {
                                        $this->addSuspicion(
                                            \vc\model\db\SuspicionDbModel::TYPE_MISSING_PICTURE,
                                            array(
                                                'formValues' => $formValues,
                                                'filename' => $filename
                                            )
                                        );

                                        $success = false;
                                        $pictureErrors[] = gettext('pictures.save.failed.upload');
                                    }
                                } else {
                                    \vc\lib\ErrorHandler::error(
                                        'Too many pictures',
                                        __FILE__,
                                        __LINE__,
                                        array(
                                            'formValues' => $formValues,
                                            'newPics' => $newPics,
                                            'updatedPics' => $updatedPics,
                                            'deletedPics' => count($deletedPics),
                                            'maxPictures' => $maxPictures
                                        )
                                    );
                                    $pictureErrors[] = gettext('pictures.save.failed.toomany');
                                    $success = false;
                                }
                            }
                        }
                    }

                    // Updating default picture
                    $pictureModel->updateDefaultPicture($this->getSession()->getUserId());

                    if ($success) {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_SUCCESS,
                            gettext('pictures.save.success')
                        );
                    } else {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('pictures.save.failed') . "\n" .
                            implode("\n", $pictureErrors)
                        );
                    }
                    throw new \vc\exception\RedirectException(
                        $this->path . 'user/pictures/?notification=' . $notification
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
                throw new \vc\exception\RedirectException($this->path . 'user/pictures/');
            }
        }
    }

    private function view($form)
    {
        $this->setTitle(gettext('pictures.title'));

        $this->setForm($form);
        $this->getView()->set('simpleUpload', false);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('user/pictures', true);
    }
}
