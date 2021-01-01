<?php
namespace vc\controller\web\account;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $form = $this->createForm();
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
            'account/delete/',
            0.0 // Thx to the pw field almost every browser will fill this field with the stored login name.
        );

        $form->add(new \vc\form\InfoText(
            self::NOTIFICATION_WARNING,
            gettext('unregister.infotext')
        ));

        $form->add(new \vc\form\Text(
            'password',
            'password',
            gettext('unregister.password'),
            100,
            null,
            \vc\form\Text::PASSWORD
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\MinLengthValidator(3))
          ->addValidator(new \vc\form\validation\OwnPasswordValidator($this->getSession()->getUserId()));

        $form->add(new \vc\form\Select(
            'reasons',
            'reasons',
            gettext('unregister.reason'),
            array(
                'foundpartner' => gettext('unregister.reasons.foundpartner'),
                'tootlitletime' => gettext('unregister.reasons.tootlitletime'),
                'lesssocialmedia' => gettext('unregister.reasons.lesssocialmedia'),
                'technical' => gettext('unregister.reasons.technical'),
                'harassment' => gettext('unregister.reasons.harassment'),
                'notenoughcontacts' => gettext('unregister.reasons.notenoughcontacts'),
                'notenoughreplies' => gettext('unregister.reasons.notenoughreplies'),
                'other' => gettext('unregister.reasons.other')
            ),
            null,
            true
        ))->setMandatory(true);

        $form->add(new \vc\form\Text(
            'reason',
            'reason',
            gettext('unregister.extendedreason'),
            100,
            trim(gettext('unregister.extendedreason.description')),
            \vc\form\Text::TEXTAREA
        ));

        $form->add(new \vc\form\Submit(
            gettext('unregister.confirm')
        ));

        return $form;
    }

    private function view($form)
    {
        $this->setTitle(gettext('unregister.sitetitle'));
        $this->getView()->setHeader('robots', 'noindex, follow');
        $this->getView()->set('activeMenuitem', 'mysite');

        $this->setForm($form);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('account/delete', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $formValues = $_POST;
        if (empty($formValues['formid'])) {
            throw new \vc\exception\RedirectException($this->path . 'account/delete/');
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $reason = 'REASON::' . $formValues['reasons'];
                    if (!empty($formValues['reason'])) {
                        $reason .= ' - ' . $formValues['reason'];
                    }
                    if ($this->deleteProfile($reason)) {
                        $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('unregister.done'));

                        $this->getEventService()->deleted(
                            \vc\config\EntityTypes::PROFILE,
                            $this->getSession()->getUserId(),
                            $this->getSession()->getUserId()
                        );

                        $this->getSession()->removeLoginCookie();
                        $this->getSession()->killSession();
                        throw new \vc\exception\RedirectException($this->path . 'login/?notification=' . $notification);
                    } else {
                        $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('unregister.failed'));
                        throw new \vc\exception\RedirectException(
                            $this->path . 'mysite/?notification=' . $notification
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
                throw new \vc\exception\RedirectException($this->path . 'account/delete/');
            }
        }
    }

    private function deleteProfile($reason)
    {
        // Reading the friends/favorites BEFORE deleting the profile
        $cacheModel = $this->getModel('Cache');
        $profileRelations = $cacheModel->getProfileRelations($this->getSession()->getUserId());
        $favoriteModel = $this->getDbModel('Favorite');
        $beingFavoriteList = $favoriteModel->getFieldList(
            'profileid',
            array('favoriteid' => $this->getSession()->getUserId())
        );

        $profileModel = $this->getDbModel('Profile');
        $deleted = $profileModel->deleteProfile($this->getSession()->getUserId(), $reason);

        // Reset Profile Cache for all friends, people who marked him/her as favorite
        foreach ($beingFavoriteList as $favoriteId) {
            $cacheModel->resetProfileRelations($favoriteId);
        }
        foreach ($profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED] as $friendId) {
            $cacheModel->resetProfileRelations($friendId);
        }
        foreach ($profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_TO_CONFIRM] as $friendId) {
            $cacheModel->resetProfileRelations($friendId);
        }
        foreach ($profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_WAIT_FOR_CONFIRM] as $friendId) {
            $cacheModel->resetProfileRelations($friendId);
        }
        return $deleted;
    }
}
