<?php
namespace vc\controller\web\account\real;

class ConfirmController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'account/signup/');
        }

        $formValues = array_merge($_POST, $_FILES);
        if (empty($formValues['formid'])) {
            throw new \vc\exception\RedirectException($this->path . 'account/real/');
        } else {
            $realCheckModel = $this->getDbModel('RealCheck');
            $openRealCheckObject = $realCheckModel->loadObject(
                array(
                    'profile_id' => $this->getSession()->getUserId(),
                    'created_at >' => date('Y-m-d H:i:s', time() - 86400),
                    'status' => \vc\object\RealCheck::STATUS_OPEN
                )
            );
            $form = $this->getForm($formValues['formid']);
            if ($openRealCheckObject !== null &&
                $form instanceof \vc\form\Form &&
                $form->validate($this->getDb(), $formValues)) {
                if ($form->somethingInHoneytrap($formValues)) {
                    $this->handleHoneypot($form->getHoneypot(), $formValues);
                }

                $saveObject = $form->getObject($openRealCheckObject, $formValues);

                $pictureSaveComponent = $this->getComponent('PictureSave');
                $pictureSaveComponent->saveObjectPictures(
                    \vc\object\RealCheck::$fields,
                    $saveObject,
                    $this->getSession()->getSetting(\vc\object\Settings::ROTATE_PICS, 1)
                );
                $saveObject->status = \vc\object\RealCheck::STATUS_SUBMITTED;
                $objectSaved = $realCheckModel->updateObject($this->getSession()->getProfile(), $saveObject);
                if ($objectSaved) {
                    $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('real.confirm.success'));
                } else {
                    $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('real.confirm.failed'));
                }
                throw new \vc\exception\RedirectException($this->path . 'account/real/?notification=' . $notification);
            } else {
                throw new \vc\exception\RedirectException($this->path . 'account/real/');
            }
        }
    }
}
