<?php
namespace vc\controller\web\group;

class AddController extends \vc\controller\web\AbstractWebController
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

        $form = $this->createForm();
        $this->view($form);
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
            $form = $this->createForm();
            $this->view($form);
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $object = $form->getObject(new \vc\object\Group(), $formValues);

                    $groupModel = $this->getDbModel('Group');
                    $pictureSaveComponent = $this->getComponent('PictureSave');
                    $pictureSaveComponent->saveObjectPictures(
                        \vc\object\Group::$fields,
                        $object,
                        $this->getSession()->getSetting(\vc\object\Settings::ROTATE_PICS, 1)
                    );
                    $objectSaved = $groupModel->insertObject($this->getSession()->getProfile(), $object);

                    if ($objectSaved !== false) {
                        // Inform moderators about new group
                        $websocketMessageModel = $this->getDbModel('WebsocketMessage');
                        $websocketMessageModel->triggerMods(\vc\config\EntityTypes::STATUS);

                        $notification = $this->setNotification(
                            self::NOTIFICATION_SUCCESS,
                            gettext('group.save.success')
                        );
                    } else {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('group.save.failed')
                        );
                    }
                    throw new \vc\exception\RedirectException(
                        $this->path . 'groups/add/?notification=' . $notification
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
                $form = $this->createForm();
                $this->view($form);
            }
        }
    }

    private function createForm()
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));

        $form = new \vc\form\Form(
            $formId,
            'GroupCreate',
            $this->path,
            $this->locale,
            'groups/add/'
        );
        $form->add(new \vc\form\Text(
            'name',
            'name',
            gettext('group.form.name'),
            255
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\MinLengthValidator(5));
        $form->add(new \vc\form\Image(
            'image',
            'image',
            gettext('group.form.image'),
            gettext('group.form.image.help')
        ));
        $form->add(new \vc\form\Text(
            'description',
            'description',
            gettext('group.form.description'),
            4096,
            null,
            \vc\form\Text::TEXTAREA
        ))->setMandatory(true);
        $form->add(new \vc\form\Text(
            'rules',
            'rules',
            gettext('group.form.rules'),
            4096,
            gettext('group.form.rules.help'),
            \vc\form\Text::TEXTAREA
        ));
        $form->add(new \vc\form\Text(
            'modMessage',
            'mod_message',
            gettext('group.form.modmessage'),
            4096,
            gettext('group.form.modmessage.help'),
            \vc\form\Text::TEXTAREA
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\MinLengthValidator(10));
        $languages = array('de' => gettext('language.de'),
                           'en' => gettext('language.en'));
        asort($languages);
        $form->add(new \vc\form\Select(
            'language',
            'language',
            gettext('group.form.language'),
            $languages
        ))->setMandatory(true);
        $form->add(new \vc\form\Submit(gettext('group.form.submit')));
        return $form;
    }

    private function view($form)
    {
        $this->setTitle(gettext('groups.add.title'));
        $this->setForm($form);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('group/add', true);
    }
}
