<?php
namespace vc\controller\web\event;

class EditController extends \vc\controller\web\AbstractWebController
{
    const MODE_ADD = 1;
    const MODE_COPY = 2;
    const MODE_EDIT = 3;

    private $mode;

    private $event;

    private $group;

    private function init()
    {
        if ($this->site == 'events/add') {
            $this->mode = self::MODE_ADD;
        } elseif ($this->site == 'events/add/group' && !empty($this->siteParams)) {
            $this->mode = self::MODE_ADD;
            $groupModel = $this->getDbModel('Group');
            $this->group = $groupModel->loadObject(array(
                'hash_id' => $this->siteParams[0],
                'deleted_at IS NULL'
            ));
            if (empty($this->group)) {
                throw new \vc\exception\NotFoundException();
            } else {
                $groupRoleModel = $this->getDbModel('GroupRole');
                $groupRole = $groupRoleModel->getRole(
                    $this->group->id,
                    $this->getSession()->getUserId()
                );
                if (empty($groupRole)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONADMIN,
                        array(
                            'type' => 'Adding event to other peoples group',
                            'groupId' => $this->group->id
                        )
                    );
                    throw new \vc\exception\NotFoundException();
                }
            }
        } elseif (($this->site == 'events/copy' || $this->site == 'events/edit') &&
                  !empty($this->siteParams)) {
            if ($this->site == 'events/copy') {
                $this->mode = self::MODE_COPY;
            } elseif ($this->site == 'events/edit') {
                $this->mode = self::MODE_EDIT;
            }

            $eventModel = $this->getDbModel('Event');
            $this->event = $eventModel->loadObject(array(
                'hash_id' => $this->siteParams[0],
                'deleted_at IS NULL'
            ));
            if (empty($this->event)) {
                throw new \vc\exception\NotFoundException();
            } else if ($this->mode === self::MODE_EDIT && strtotime($this->event->endDate) < time()) {
                // Can't edit past events
                throw new \vc\exception\NotFoundException();
            }

            if (!empty($this->event->groupId)) {
                // You can edit group events even after the parent group has been deleted
                $groupModel = $this->getDbModel('Group');
                $this->group = $groupModel->loadObject(array(
                    'id' => $this->event->groupId
                ));
                if (empty($this->group)) {
                    \vc\lib\ErrorHandler::error(
                        'Group for event doesn\'t exist. Even among the deleted ones.',
                        __FILE__,
                        __LINE__,
                        array(
                            'eventId' => $this->event->id,
                            'groupId' => $this->event->groupId
                        )
                    );
                    throw new \vc\exception\NotFoundException();
                }
            }

            // Checking access rights for editing or copying events
            if (empty($this->event->groupId)) {
                if ($this->event->createdBy !== $this->getSession()->getUserId()) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_ATTEMPT_EDITING_CONTENT_OF_OTHERS,
                        array(
                            'type' => 'Edit other peoples event',
                            'eventId' => $this->event->id
                        )
                    );
                    throw new \vc\exception\NotFoundException();
                }
            } else {
                $groupRoleModel = $this->getDbModel('GroupRole');
                $groupRole = $groupRoleModel->getRole(
                    $this->event->groupId,
                    $this->getSession()->getUserId()
                );
                if (empty($groupRole)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONADMIN,
                        array(
                            'type' => 'Adding event to other peoples group',
                            'eventId' => $this->event->id,
                            'groupId' => $this->event->groupId
                        )
                    );
                    throw new \vc\exception\NotFoundException();
                }
            }
        } else {
            throw new \vc\exception\NotFoundException();
        }
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $termsModel = $this->getDbModel('Terms');
        if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
            throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
        }

        $this->init();
        if ($this->mode === self::MODE_ADD) {
            $form = $this->createForm();
            $geoIpModel = $this->getDbModel('GeoIp');
            $defaultCountryId = $geoIpModel->getCountryByIp($this->getIp());
            if (!empty($defaultCountryId)) {
                $formObject = new \vc\object\Event();
                $formObject->locationCountry = $defaultCountryId;
                $form->setObject($formObject);
            }
            if (!empty($this->group)) {
                $form->setDefaultValues(array(
                    'group' => $this->group->name,
                    'inviteAllMembers' => true
                ));
            }
            $this->view($form);
        } elseif ($this->mode === self::MODE_COPY) {
            $form = $this->createForm();

            $formObject = clone($this->event);
            $formObject->startDate = null;
            $formObject->endDate = null;
            $formObject->image = null;
            $formObject->fbUrl = null;
            $formObject->locationLat = 0;
            $formObject->locationLng = 0;
            $form->setObject($formObject);

            if ($this->group !== null) {
                $form->setDefaultValues(array(
                    'group' => $this->group->name,
                    'inviteAllMembers' => true
                ));
            }

            // Set only the times not the dates
            foreach ($form->getChildren() as $child) {
                if ($child instanceof \vc\form\Date) {
                    if ($child->getName() == 'start_date') {
                        $child->setDefault(array(
                            'day' => null,
                            'month' => null,
                            'year' => null,
                            'time' => date('H:i', strtotime($this->event->startDate))
                        ));
                    } elseif ($child->getName() == 'end_date') {
                        $child->setDefault(array(
                            'day' => null,
                            'month' => null,
                            'year' => null,
                            'time' => date('H:i', strtotime($this->event->endDate))
                        ));
                    }
                }
            }
            $this->view($form);
        } elseif ($this->mode === self::MODE_EDIT) {
            $form = $this->createForm();
            if (!empty($this->group)) {
                $form->setDefaultValues(array('group' => $this->group->name));
            }
            $form->setObject($this->event);
            $this->view($form);
        } else {
            \vc\lib\Assert::assertUnreachable();
        }
    }

    private function createForm()
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));

        if ($this->mode == self::MODE_ADD) {
            if (empty($this->group)) {
                $formUrl = 'events/add/';
            } else {
                $formUrl = 'events/add/group/' . $this->group->hashId . '/';
            }
            $form = new \vc\form\Form(
                $formId,
                'EventCreate',
                $this->path,
                $this->locale,
                $formUrl
            );
        } elseif ($this->mode == self::MODE_COPY) {
            $form = new \vc\form\Form(
                $formId,
                'EventCopy',
                $this->path,
                $this->locale,
                'events/copy/' . $this->event->hashId . '/'
            );
        } elseif ($this->mode == self::MODE_EDIT) {
            $form = new \vc\form\Form(
                $formId,
                'EventEdit',
                $this->path,
                $this->locale,
                'events/edit/' . $this->event->hashId . '/'
            );
        } else {
            \vc\lib\Assert::assertUnreachable();
        }

        if (!empty($this->group)) {
            $form->add(new \vc\form\Text(
                'group',
                'group',
                gettext('event.form.group'),
                255
            ))->setReadOnly();
        }

        $form->add(new \vc\form\Text(
            'name',
            'name',
            gettext('event.form.name'),
            255
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\MinLengthValidator(5));
        $form->add(new \vc\form\Image(
            'image',
            'image',
            gettext('event.form.image'),
            gettext('event.form.image.help')
        ));

        $startDate = $form->add(new \vc\form\Date(
            'startDate',
            'start_date',
            gettext('event.form.startDate'),
            time(),
            time() + 31622400,
            true
        ))->setMandatory(true);

        $form->add(new \vc\form\Date(
            'endDate',
            'end_date',
            gettext('event.form.endDate'),
            time(),
            time() + 31622400,
            true,
            gettext('event.form.endDate.autoset')
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\DateAfterValidator($startDate));

        $cacheModel = $this->getModel('Cache');
        $countries = $cacheModel->getCountries($this->locale);
        $indexedCountries = array();
        foreach ($countries as $country) {
            $indexedCountries[$country[0]] = $country[1];
        }
        $form->add(new \vc\form\Location(
            'locationCaption',
            'locationStreet',
            'locationPostal',
            'locationCity',
            'locationRegion',
            'locationCountry',
            $indexedCountries,
            'locationLat',
            'locationLng',
            'location',
            gettext('event.form.location')
        ))->setMandatory(true);

        $form->add(new \vc\form\Text(
            'description',
            'description',
            gettext('event.form.description'),
            4096,
            null,
            \vc\form\Text::TEXTAREA
        ))->setMandatory(true);

        $form->add(new \vc\form\Text(
            'url',
            'url',
            gettext('event.form.url'),
            255
        ))->addValidator(new \vc\form\validation\UrlValidator());

        $form->add(new \vc\form\Text(
            'fbUrl',
            'fb_url',
            gettext('event.form.fbUrl'),
            255
        ))->addValidator(new \vc\form\validation\UrlValidator());

        $categoryOptions = array();
        $eventCategories = \vc\config\Fields::getEventCategories();
        foreach (\vc\config\Fields::getEventCategoryTree() as $mainCategoryId => $subCategories) {
            $categoryOptions[$mainCategoryId] = gettext($eventCategories[$mainCategoryId]['title']);
            $lastCategory = end($subCategories);
            foreach ($subCategories as $categoryId) {
                if ($categoryId == $lastCategory) {
                    $categoryOptions[$categoryId] = ' ╚═ ' . gettext($eventCategories[$categoryId]['title']);
                } else {
                    $categoryOptions[$categoryId] = ' ╠═ ' . gettext($eventCategories[$categoryId]['title']);
                }
            }
        }
        $form->add(new \vc\form\Select(
            'categoryId',
            'categoryId',
            gettext('event.form.category'),
            $categoryOptions
        ))->setMandatory(true);


        $eventVisibilityOptions = array();
        $eventVisibilityOptions[\vc\object\Event::EVENT_VISIBILITY_PUBLIC] = gettext('event.form.eventVisibility.public');
        $eventVisibilityOptions[\vc\object\Event::EVENT_VISIBILITY_REGISTERED] = gettext('event.form.eventVisibility.registered');
        if (!empty($this->group)) {
            $eventVisibilityOptions[\vc\object\Event::EVENT_VISIBILITY_GROUP] = gettext('event.form.eventVisibility.group');
        }
//        $eventVisibilityOptions[\vc\object\Event::EVENT_VISIBILITY_FRIENDS] = gettext('event.form.eventVisibility.friends');
        $eventVisibilityOptions[\vc\object\Event::EVENT_VISIBILITY_INVITEE] = gettext('event.form.eventVisibility.invitee');

        $form->add(new \vc\form\Select(
            'eventVisibility',
            'eventVisibility',
            gettext('event.form.eventVisibility'),
            $eventVisibilityOptions
        ))->setMandatory(true);

        $guestVisibilityOptions = array();
        $guestVisibilityOptions[\vc\object\Event::GUEST_VISIBILITY_REGISTERED] = gettext('event.form.groupVisibility.registered');
        if (!empty($this->group)) {
            $guestVisibilityOptions[\vc\object\Event::GUEST_VISIBILITY_GROUP] = gettext('event.form.groupVisibility.group');
        }
//        $guestVisibilityOptions[\vc\object\Event::GUEST_VISIBILITY_FRIENDS] = gettext('event.form.groupVisibility.friends');
        $guestVisibilityOptions[\vc\object\Event::GUEST_VISIBILITY_INVITEE] = gettext('event.form.groupVisibility.invitee');
        $form->add(new \vc\form\Select(
            'guestVisibility',
            'guestVisibility',
            gettext('event.form.guestVisibility'),
            $guestVisibilityOptions
        ))->setMandatory(true);

        $form->add(new \vc\form\Checkbox(
            'canGuestInvite',
            'canGuestInvite',
            gettext('event.form.canGuestInvite')
        ));

        if (($this->mode == self::MODE_ADD || $this->mode == self::MODE_COPY) &&
            !empty($this->group)) {
            $form->add(new \vc\form\Checkbox(
                null,
                'inviteAllMembers',
                gettext('event.form.inviteAllMembers')
            ));
        }

        $form->add(new \vc\form\Submit(
            gettext('event.form.submit')
        ));

        return $form;
    }

    private function view($form)
    {
        if ($this->mode === self::MODE_ADD) {
            $this->setTitle(gettext('event.form.title.add'));
            $this->getView()->set('shortTitle', gettext('event.form.title.add'));
        } elseif ($this->mode === self::MODE_COPY) {
            $this->setTitle(gettext('event.form.title.copy'));
            $this->getView()->set('shortTitle', gettext('event.form.title.copy'));
        } elseif ($this->mode === self::MODE_EDIT) {
            $this->setTitle(gettext('event.form.title.edit'));
            $this->getView()->set('shortTitle', gettext('event.form.title.edit'));
        } else {
            \vc\lib\Assert::assertUnreachable();
        }

        $this->setForm($form);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('event/edit', true);
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

        $this->init();
        if (empty($formValues['formid'])) {
            throw new \vc\exception\RedirectException($this->path . 'events/calendar/');
        } else {
            if (!empty($formValues['start_date']['year']) &&
                !empty($formValues['start_date']['month']) &&
                !empty($formValues['start_date']['day']) &&
                empty($formValues['end_date']['year']) &&
                empty($formValues['end_date']['month']) &&
                empty($formValues['end_date']['day'])) {
                $formValues['end_date']['year'] = $formValues['start_date']['year'];
                $formValues['end_date']['month'] = $formValues['start_date']['month'];
                $formValues['end_date']['day'] = $formValues['start_date']['day'];
            }

            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $objectHashId = $this->saveObject($form, $request, $formValues);
                    if (empty($objectHashId)) {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('event.save.failed')
                        );
                        throw new \vc\exception\RedirectException(
                            $this->path . 'events/calendar/?notification=' . $notification
                        );
                    } else {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_SUCCESS,
                            gettext('event.save.success')
                        );
                        throw new \vc\exception\RedirectException(
                            $this->path . 'events/view/' . $objectHashId . '/?notification=' . $notification
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
                throw new \vc\exception\RedirectException($this->path . 'events/calendar/');
            }
        }
    }

    private function saveObject($form, $request, $formValues)
    {
        $eventModel = $this->getDbModel('Event');

        if ($this->mode === self::MODE_EDIT && !empty($this->event)) {
            $object = $form->getObject($this->event, $formValues);
        } else {
            $object = $form->getObject(new \vc\object\Event(), $formValues);
        }

        if (!empty($this->group)) {
            $object->groupId = $this->group->id;
        }

        // Reading the location coordinates from the rest data
        if (empty($object->locationLat) && empty($object->locationLng)) {
            $geoComponent = $this->getComponent('Geo');
            $coordinates = $geoComponent->getCoordinates(
                $this->locale,
                $object->locationPostal,
                $object->locationCity,
                null,
                $object->locationCountry
            );
            $object->locationLat = $coordinates[0];
            $object->locationLng = $coordinates[1];
        }

        $pictureSaveComponent = $this->getComponent('PictureSave');
        $pictureSaveComponent->saveObjectPictures(
            \vc\object\Event::$fields,
            $object,
            $this->getSession()->getSetting(\vc\object\Settings::ROTATE_PICS, 1)
        );
        if (empty($object->id)) {
            $objectSaved = $eventModel->insertObject($this->getSession()->getProfile(), $object);

            if ($objectSaved) {
                $savedEvent = $eventModel->loadObject(array('id' => $objectSaved));

                $eventParticipantModel = $this->getDbModel('EventParticipant');

                // Event creator participates by default
                $defaultHost = new \vc\object\EventParticipant();
                $defaultHost->eventId = $objectSaved;
                $defaultHost->profileId = $this->getSession()->getUserId();
                $defaultHost->degree = \vc\object\EventParticipant::STATUS_PARTICIPATING_SURE;
                $defaultHost->isHost = 1;

                $eventParticipantModel->insertObject($this->getSession()->getProfile(), $defaultHost);

                if (empty($object->groupId)) {
                    $forumThread = new \vc\object\ForumThread();
                    $forumThread->contextType = \vc\config\EntityTypes::PROFILE;
                    $forumThread->contextId = $this->getSession()->getUserId();
                    $forumThread->additional = $this->getAdditional($savedEvent);
                    $forumThreadModel = $this->getDbModel('ForumThread');
                    $insertedThread = $forumThreadModel->insertObject($this->getSession()->getProfile(), $forumThread);
                    $eventModel->update(
                        array(
                            'id' =>  $savedEvent->id
                        ),
                        array(
                            'feed_thread_id' => $insertedThread
                        )
                    );

                } else {
                    $groupForumModel = $this->getDbModel('GroupForum');
                    $defaultGroupForum = $groupForumModel->getDefaultForum($this->group->id);

                    $forumThread = new \vc\object\ForumThread();
                    $forumThread->contextType = \vc\config\EntityTypes::GROUP_FORUM;
                    $forumThread->contextId = $defaultGroupForum;
                    $forumThread->additional = $this->getAdditional($savedEvent);
                    $forumThreadModel = $this->getDbModel('ForumThread');
                    $insertedThread = $forumThreadModel->insertObject($this->getSession()->getProfile(), $forumThread);
                    $eventModel->update(
                        array(
                            'id' =>  $savedEvent->id
                        ),
                        array(
                            'feed_thread_id' => $insertedThread
                        )
                    );

                    // Option to invite all members of the group
                    if ($request->getBoolean('inviteAllMembers')) {
                        $groupMemberModel = $this->getDbModel('GroupMember');
                        $groupMemberIds = $groupMemberModel->getFieldList(
                            'profile_id',
                            array('group_id' => $object->groupId)
                        );
                        foreach ($groupMemberIds as $groupMemberId) {
                            if ($groupMemberId !== $this->getSession()->getUserId()) {
                                $eventParticipantObject = new \vc\object\EventParticipant();
                                $eventParticipantObject->eventId = $objectSaved;
                                $eventParticipantObject->profileId = intval($groupMemberId);
                                $eventParticipantObject->degree = \vc\object\EventParticipant::STATUS_INVITED;
                                $eventParticipantModel->insertObject(
                                    $this->getSession()->getProfile(),
                                    $eventParticipantObject
                                );
                            }
                        }
                    }
                }
                return $savedEvent->hashId;
            } else {
                return false;
            }
        } else {
            $objectSaved = $eventModel->updateObject($this->getSession()->getProfile(), $object);
            if ($objectSaved) {
                if (!empty($object->feedThreadId)) {
                    $forumThreadModel = $this->getDbModel('ForumThread');
                    $forumThreadModel->update(
                        array(
                            'id' =>  $object->feedThreadId
                        ),
                        array(
                            'additional' => json_encode($this->getAdditional($object))
                        )
                    );
                }
                return $this->event->hashId;
            } else {
                return false;
            }
        }
    }

    private function getAdditional($event)
    {
        $location = $event->locationCaption;
        if (!empty($event->locationStreet)) {
            $location .= ', ' . $event->locationStreet;
        }
        if (!empty($event->locationPostal) ||
            !empty($event->locationCity)) {
            $location .= ', ';
            if (!empty($event->locationPostal)) {
                $location .= $event->locationPostal . ' ';
            }
            $location .= $event->locationCity;
        }
        return array(
            \vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_URL => 'events/view/' . $event->hashId. '/',
            \vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_PICTURE =>
                empty($event->image)
                    ? '/img/matcha/thumb/default-event.png'
                    : '/events/picture/crop/74/74/' . $event->image,
            \vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_TITLE => $event->name,
            \vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_LOCATION => $location,
            \vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_DATE => strtotime($event->startDate)
        );
    }
}
