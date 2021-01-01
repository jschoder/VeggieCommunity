<?php
namespace vc\controller\web\user\pictures;

class SimplePicturesController extends AbstractPicturesController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $form = $this->createForm();
        $this->view($form);
    }

    private function createForm()
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'UserPictures',
            $this->path,
            $this->locale,
            'user/pictures/simple/'
        );

        $form->add(new \vc\form\Image(
            'image',
            'image',
            gettext('pictures.simple.field'),
            gettext('pictures.simple.field.help')
        ))->setMandatory(true);
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
            throw new \vc\exception\RedirectException($this->path . 'user/pictures/simple/');
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $pictureModel = $this->getDbModel('Picture');
                    $pictureChecklistModel = $this->getDbModel('PictureChecklist');

                    if ($this->getSession()->getPlusLevel() >= \vc\object\Plus::PLUS_TYPE_STANDARD) {
                        $maxPictures = \vc\config\Globals::MAX_PICTURES_PLUS;
                    } else {
                        $maxPictures = \vc\config\Globals::MAX_PICTURES_DEFAULT;
                    }

                    $currentPictureCount = $pictureModel->getCount(array('profileid' => $this->getSession()->getUserId()));

                    if ($currentPictureCount >= $maxPictures) {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('pictures.simple.save.error.tooMuchPictures')
                        );
                        throw new \vc\exception\RedirectException(
                            $this->path . 'user/pictures/simple/?notification=' . $notification
                        );
                    } else {
                        $pictureSaveComponent = $this->getComponent('PictureSave');
                        $fileInfo = $pictureSaveComponent->saveFormPicture(
                            $formValues['image'],
                            '/pictures/full',
                            20,
                            \vc\config\Globals::MAX_PICTURE_WIDTH,
                            \vc\config\Globals::MAX_PICTURE_HEIGHT,
                            $this->getSession()->getSetting(\vc\object\Settings::ROTATE_PICS, 1)
                        );

                        if ($fileInfo === false) {
                            $notification = $this->setNotification(
                                self::NOTIFICATION_ERROR,
                                gettext('pictures.simple.save.error.uploadMissing')
                            );
                            $this->storePicError($formValues['image'], 'pictures.simple.save.error.uploadMissing');
                            throw new \vc\exception\RedirectException(
                                $this->path . 'user/pictures/simple/?notification=' . $notification
                            );
                        } else {
                            $pictureObject = new \vc\object\SavedPicture();
                            $pictureObject->profileid = $this->getSession()->getUserId();
                            $pictureObject->filename = $fileInfo['filename'];
                            $pictureObject->description = null;
                            $pictureObject->weight = $currentPictureCount + 1;
                            $pictureObject->defaultpic = 0;
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

                            if ($pictureId) {
                                // Updating default picture
                                $pictureModel->updateDefaultPicture($this->getSession()->getUserId());

                                $notification = $this->setNotification(
                                    self::NOTIFICATION_SUCCESS,
                                    gettext('pictures.simple.save.success')
                                );
                            } else {
                                $this->storePicError($pictureObject, 'pictures.simple.save.failed');
                                $notification = $this->setNotification(
                                    self::NOTIFICATION_ERROR,
                                    gettext('pictures.simple.save.failed')
                                );
                            }
                            throw new \vc\exception\RedirectException(
                                $this->path . 'user/pictures/simple/?notification=' . $notification
                            );
                        }
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
                throw new \vc\exception\RedirectException($this->path . 'user/pictures/');
            }
        }
    }

    private function view($form)
    {
        $this->setTitle(gettext('pictures.simple.title'));

        $this->setForm($form);
        $this->getView()->set('simpleUpload', true);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('user/pictures', true);
    }

    private function storePicError($file, $message)
    {
        $_SESSION['pic_errors'] = array();
        if (!array_key_exists('pic_errors', $_SESSION)) {
            $_SESSION['pic_errors'] = array();
        }

        $_SESSION['pic_errors'][] =
            'Controller: Simple' . "\n" .
            'Message: ' . $message . "\n" .
            'File: ' . var_export($file, true);
    }
}
