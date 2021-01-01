<?php
namespace vc\controller\web\group;

class SettingsController extends AbstractGroupController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $termsModel = $this->getDbModel('Terms');
        if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
            throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
        }

        $this->loadGroup($this->siteParams[0]);
        $groupObject = $this->getGroupObject();

        if ($this->getGroupRole() !== \vc\object\GroupRole::ROLE_ADMIN) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONADMIN,
                array(
                    'groupId' => $groupObject->id,
                    'profileId' => $this->getSession()->getUserId()
                )
            );
            throw new \vc\exception\RedirectException($this->path . 'groups/info/' . $groupObject->hashId . '/');
        }

        $formValues = array_merge($_POST, $_FILES);
        if (empty($formValues['formid'])) {
            $form = $this->createForm();
            $this->view($groupObject, $form);
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $this->saveForm($groupObject, $form, $formValues);
                } else {
                    $this->getView()->set(
                        'notification',
                        array('type' => self::NOTIFICATION_WARNING, 'message' => gettext('form.validationFailed'))
                    );

                    $form->setDefaultValues($formValues);
                    $this->view($groupObject, $form);
                }
            } else {
                $form = $this->createForm();
                $this->view($groupObject, $form);
            }
        }
    }

    private function saveForm($group, $form, $formValues)
    {
        $updateSuccessful = true;

        $formObject = $form->getObject($group, $formValues);

        $groupModel = $this->getDbModel('Group');
        $pictureSaveComponent = $this->getComponent('PictureSave');
        $pictureSaveComponent->saveObjectPictures(
            \vc\object\Group::$fields,
            $formObject,
            $this->getSession()->getSetting(\vc\object\Settings::ROTATE_PICS, 1)
        );
        $objectSaved = $groupModel->updateObject($this->getSession()->getProfile(), $formObject);
        if (!$objectSaved) {
            $updateSuccessful = false;
        }

        // Update the forums
        $existingForums = 0;
        $hasMainForum = false;

        $groupForumModel = $this->getDbModel('GroupForum');
        $forums = $groupForumModel->loadObjects(array('group_id' => $group->id, 'deleted_at IS NULL'));
        foreach ($forums as $forum) {
            if (array_key_exists('delete', $formValues['forums']) &&
                in_array($forum->hashId, $formValues['forums']['delete'])) {
                $forum->deletedBy = $this->getSession()->getUserId();
                $forum->deletedAt = date('Y-m-d H:i:s');

                $subscriptionModel = $this->getDbModel('Subscription');
                $subscriptionModel->unsubscribeAllGroupMembers($group->id, $forum->id);

            } else {
                $existingForums++;
                if (!empty($formValues['forums'][$forum->hashId]['name'])) {
                    $forum->name = $formValues['forums'][$forum->hashId]['name'];
                }
                if (!empty($formValues['forums'][$forum->hashId]['contentVisibility'])) {
                    $forum->contentVisibility = intval($formValues['forums'][$forum->hashId]['contentVisibility']);
                }
                if (!empty($formValues['forums'][$forum->hashId]['weight'])) {
                    $forum->weight = intval($formValues['forums'][$forum->hashId]['weight']);
                }
                if ($formValues['forums']['isMain'] == $forum->hashId) {
                    $forum->isMain = 1;
                    $hasMainForum = true;
                } else {
                    $forum->isMain = 0;
                }
            }
            $saved = $groupForumModel->updateObject(
                $this->getSession()->getProfile(),
                $forum
            );
            if (!$saved) {
                $updateSuccessful = false;
            }
        }

        for ($i = 0; $i < \vc\config\Globals::MAX_FORUMS_PER_GROUP; $i++) {
            $forumFormKey = 'new-' . $i;
            if (!empty($formValues['forums'][$forumFormKey]['name']) &&
                $existingForums < \vc\config\Globals::MAX_FORUMS_PER_GROUP) {
                $existingForums++;

                $forum = new \vc\object\GroupForum();
                $forum->groupId = $group->id;
                $forum->name = $formValues['forums'][$forumFormKey]['name'];
                if (!empty($formValues['forums'][$forumFormKey]['contentVisibility'])) {
                    $forum->contentVisibility = intval($formValues['forums'][$forumFormKey]['contentVisibility']);
                }
                if (!empty($formValues['forums'][$forumFormKey]['weight'])) {
                    $forum->weight = intval($formValues['forums'][$forumFormKey]['weight']);
                }
                if ($formValues['forums']['isMain'] == $forumFormKey) {
                    $forum->isMain = 1;
                    $hasMainForum = true;
                } else {
                    $forum->isMain = 0;
                }

                $saved = $groupForumModel->insertObject(
                    $this->getSession()->getProfile(),
                    $forum
                );

                $subscriptionModel = $this->getDbModel('Subscription');
                $subscriptionModel->subscribeAllGroupMembers($group->id, $saved);

                if (!$saved) {
                    $updateSuccessful = false;
                }
            }
        }

        if ($existingForums === 0) {
            $saved = $groupForumModel->createDefaultForum(
                $group->id,
                $this->getSession()->getProfile()
            );
            if (!$saved) {
                $updateSuccessful = false;
            }
        } elseif (!$hasMainForum) {
            $groupForumModel->setAutoMainForum($group->id);
        }

        // Redirect to the user
        if ($updateSuccessful) {
            $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('group.settings.success'));
        } else {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext('group.settings.failed'));
        }
        throw new \vc\exception\RedirectException(
            $this->path . 'groups/settings/' . $group->hashId . '/?notification=' . $notification
        );
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (count($this->siteParams) === 0 || !$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\NotFoundException();
        }

        $this->loadGroup($this->siteParams[0]);
        $groupObject = $this->getGroupObject();

        $defaultValues = array(
            'name' => $groupObject->name,
            'description' => $groupObject->description,
            'rules' => $groupObject->rules,
            'member_visibility' => $groupObject->memberVisibility,
            'auto_confirm_members' => $groupObject->autoConfirmMembers,
            'language' => $groupObject->language,
            'forums' => array()
        );
        foreach ($this->getForums() as $forum) {
            $defaultValues['forums'][$forum->hashId] = array(
                'name' => $forum->name,
                'contentVisibility' => $forum->contentVisibility,
                'weight' => $forum->weight,
            );
            if ($forum->isMain) {
                $defaultValues['forums']['isMain'] = $forum->hashId;
            }
        }
        $form = $this->createForm($groupObject);
        $form->setDefaultValues($defaultValues);
        $this->view($groupObject, $form);
    }

    private function createForm($group)
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'GroupSettings',
            $this->path,
            $this->locale,
            'groups/settings/' . $group->hashId . '/'
        );

        $form->add(new \vc\form\Text(
            'name',
            'name',
            gettext('group.form.name'),
            255
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\MinLengthValidator(3));
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

        $memberVisibilities = array(
            \vc\object\Group::MEMBER_VISBILITY_MEMBER_ONLY => gettext('group.visibility.member.member'),
            \vc\object\Group::MEMBER_VISBILITY_SITE_MEMBERS => gettext('group.visibility.member.registered')
        );
        $form->add(new \vc\form\Select(
            'memberVisibility',
            'member_visibility',
            gettext('group.settings.member.visibility'),
            $memberVisibilities
        ))->setMandatory(true);

        $form->add(new \vc\form\Checkbox(
            'autoConfirmMembers',
            'auto_confirm_members',
            gettext('group.settings.member.autoconfirm')
        ));

        $multiple = new \vc\form\Multiple(
            'forums',
            gettext('group.settings.forum.title'),
            \vc\config\Globals::MAX_FORUMS_PER_GROUP,
            gettext('group.settings.forum.delete')
        );
        $form->add($multiple);
        $multiple->setSortable();

        $multiple->add(new \vc\form\Text(
            'forums.name',
            'name',
            gettext('group.settings.forum.name'),
            25
        ))->setSmall(true)
          ->setMandatory(true);
        $multiple->add(new \vc\form\Radio(
            'forums.isMain',
            'isMain',
            gettext('group.settings.forum.main')
        ))->setSmall(true)
          ->setMandatory(true);
        $forumContentVisibilities = array(
            \vc\object\GroupForum::CONTENT_VISIBILITY_MEMBER => gettext('group.visibility.forum.member'),
            \vc\object\GroupForum::CONTENT_VISIBILITY_REGISTERED => gettext('group.visibility.forum.registered'),
            \vc\object\GroupForum::CONTENT_VISIBILITY_PUBLIC => gettext('group.visibility.forum.public')
        );
        $multiple->add(new \vc\form\Select(
            'forums.contentVisibility',
            'contentVisibility',
            gettext('group.settings.forum.contentVisibility'),
            $forumContentVisibilities
        ))->setSmall(true)
          ->setMandatory(true);

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

    private function view($group, $form)
    {
        $this->setForm($form);
        $this->getView()->set('form', $form);

        $groupObject = $this->getGroupObject();
        $this->setTitle($groupObject->name);

        if ($this->getGroupRole() !== \vc\object\GroupRole::ROLE_ADMIN) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONADMIN,
                array(
                    'groupId' => $group->id,
                    'profileId' => $this->getSession()->getUserId()
                )
            );
            throw new \vc\exception\RedirectException($this->path . 'groups/info/' . $group->hashId . '/');
        }

        $memberVisibilities = array(
            \vc\object\Group::MEMBER_VISBILITY_MEMBER_ONLY => gettext('group.visibility.member.member'),
            \vc\object\Group::MEMBER_VISBILITY_SITE_MEMBERS => gettext('group.visibility.member.registered')
        );
        $this->getView()->set('memberVisibilities', $memberVisibilities);

        $forumContentVisibilities = array(
            \vc\object\GroupForum::CONTENT_VISIBILITY_MEMBER => gettext('group.visibility.forum.member'),
            \vc\object\GroupForum::CONTENT_VISIBILITY_REGISTERED => gettext('group.visibility.forum.registered'),
            \vc\object\GroupForum::CONTENT_VISIBILITY_PUBLIC => gettext('group.visibility.forum.public')
        );
        $this->getView()->set('forumContentVisibilities', $forumContentVisibilities);

        echo $this->getView()->render('group/settings', true);
    }
}
