<?php
namespace vc\controller\web\user;

class MatchingController extends \vc\controller\web\AbstractWebController
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

        $matchingModel = $this->getDbModel('Matching');
        $matchingObject = $matchingModel->loadObject(array('user_id' => $this->getSession()->getUserId()));

        if ($matchingObject !== null &&
            time() < strtotime($matchingObject->updatedAt) + 86400) {
            if (empty($_GET['notification'])) {
                $this->getView()->set(
                    'notification',
                    array(
                        'type' => self::NOTIFICATION_WARNING,
                        'message' => gettext('matching.form.24hoursBlocked')
                    )
                );
            }
            $canSave = false;
        } else {
            if (empty($_GET['notification']) &&
                !\vc\config\Fields::containsRomanticSearchField($this->getSession()->getProfile()->search)) {
                $this->getView()->set(
                    'notification',
                    array(
                        'type' => self::NOTIFICATION_WARNING,
                        'message' => gettext('matching.form.noRomanticSearches')
                    )
                );
            }
            $canSave = true;
        }

        $form = $this->createForm($matchingObject, $canSave);
        if ($matchingObject !== null) {
            $form->setObject($matchingObject);
        }
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
            throw new \vc\exception\RedirectException($this->path . 'user/matching/');
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if (!empty($formValues['delete'])) {
                    $matchingModel = $this->getDbModel('Matching');
                    $matchingModel->delete(array('user_id' => $this->getSession()->getUserId()));
                    $matchingModel->recalculate($this->getSession()->getUserId());
                    throw new \vc\exception\RedirectException(
                        $this->path . 'user/matching/'
                    );
                }

                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $matchingModel = $this->getDbModel('Matching');

                    // Block posting when last edit is less than 24 hours ago (should not be accessible
                    $lastUpdate = $matchingModel->getField('updated_at', 'user_id', $this->getSession()->getUserId());
                    if ($lastUpdate !== null &&
                        time() < strtotime($lastUpdate) + 86400) {
                        $this->addSuspicion(
                            \vc\model\db\SuspicionDbModel::TYPE_EDIT_MATCHING_24H
                        );

                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('matching.form.24hoursBlocked')
                        );
                        throw new \vc\exception\RedirectException(
                            $this->path . 'user/matching/?notification=' . $notification
                        );
                    }

                    $object = $form->getObject(new \vc\object\Matching(), $formValues);
                    $object->userId = $this->getSession()->getUserId();

                    if ($lastUpdate === null) {
                        $insertId = $matchingModel->insertObject($this->getSession()->getProfile(), $object);
                        $objectSaved = ($insertId !== false);
                    } else {
                        $objectSaved = $matchingModel->updateObject($this->getSession()->getProfile(), $object);
                    }

                    if ($objectSaved !== false) {
                        $matchingModel->recalculate($this->getSession()->getUserId());

                        $notification = $this->setNotification(
                            self::NOTIFICATION_SUCCESS,
                            gettext('matching.save.success')
                        );
                    } else {
                        $notification = $this->setNotification(
                            self::NOTIFICATION_ERROR,
                            gettext('matching.save.failed')
                        );
                    }
                    throw new \vc\exception\RedirectException(
                        $this->path . 'user/matching/?notification=' . $notification
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
                throw new \vc\exception\RedirectException($this->path . 'user/matching/');
            }
        }
    }

    private function createForm($matchingObject, $canSave)
    {
        $fromToOptions = array(1, 2, 3, 4, 5);
        $fromToFields = array();
        $fromToFields[] = new \vc\form\FromTo(
            'adventure',
            'adventure',
            null,
            $fromToOptions,
            gettext('matching.form.adventure.from'),
            gettext('matching.form.adventure.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'bedDs',
            'bed_ds',
            null,
            $fromToOptions,
            gettext('matching.form.bedDs.from'),
            gettext('matching.form.bedDs.to'),
            gettext('matching.form.bedDs.help')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'calm',
            'calm',
            null,
            $fromToOptions,
            gettext('matching.form.calm.from'),
            gettext('matching.form.calm.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'conflict',
            'conflict',
            null,
            $fromToOptions,
            gettext('matching.form.conflict.from'),
            gettext('matching.form.conflict.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'couch',
            'couch',
            null,
            $fromToOptions,
            gettext('matching.form.couch.from'),
            gettext('matching.form.couch.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'driven',
            'driven',
            null,
            $fromToOptions,
            gettext('matching.form.driven.from'),
            gettext('matching.form.driven.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'extroverted',
            'extroverted',
            null,
            $fromToOptions,
            gettext('matching.form.extroverted.from'),
            gettext('matching.form.extroverted.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'individuality',
            'individuality',
            null,
            $fromToOptions,
            gettext('matching.form.individuality.from'),
            gettext('matching.form.individuality.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'logic',
            'logic',
            null,
            $fromToOptions,
            gettext('matching.form.logic.from'),
            gettext('matching.form.logic.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'messy',
            'messy',
            null,
            $fromToOptions,
            gettext('matching.form.messy.from'),
            gettext('matching.form.messy.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'mood',
            'mood',
            null,
            $fromToOptions,
            gettext('matching.form.mood.from'),
            gettext('matching.form.mood.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'optimistic',
            'optimistic',
            null,
            $fromToOptions,
            gettext('matching.form.optimistic.from'),
            gettext('matching.form.optimistic.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'otherDs',
            'other_ds',
            null,
            $fromToOptions,
            gettext('matching.form.otherDs.from'),
            gettext('matching.form.otherDs.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'poly',
            'poly',
            null,
            $fromToOptions,
            gettext('matching.form.poly.from'),
            gettext('matching.form.poly.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'proactive',
            'proactive',
            null,
            $fromToOptions,
            gettext('matching.form.proactive.from'),
            gettext('matching.form.proactive.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'stayhome',
            'stayhome',
            null,
            $fromToOptions,
            gettext('matching.form.stayhome.from'),
            gettext('matching.form.stayhome.to')
        );
        $fromToFields[] = new \vc\form\FromTo(
            'weird',
            'weird',
            null,
            $fromToOptions,
            gettext('matching.form.weird.from'),
            gettext('matching.form.weird.to')
        );

        $fromToWeightOptions = array(
            1 => gettext('matching.form.veryUnimportant'),
            2 => gettext('matching.form.ratherUnimportant'),
            3 => gettext('matching.form.neither'),
            4 => gettext('matching.form.ratherImportant'),
            5 => gettext('matching.form.veryImportant')
        );
        $fromToWeightFields = array();
        $fromToWeightFields[] = new \vc\form\FromTo(
            'fitness',
            'fitness',
            gettext('matching.form.fitness'),
            $fromToWeightOptions
        );
        $fromToWeightFields[] = new \vc\form\FromTo(
            'money',
            'money',
            gettext('matching.form.money'),
            $fromToWeightOptions
        );
        $fromToWeightFields[] = new \vc\form\FromTo(
            'myLooks',
            'my_looks',
            gettext('matching.form.myLooks'),
            $fromToWeightOptions
        );
        $fromToWeightFields[] = new \vc\form\FromTo(
            'theirLooks',
            'their_looks',
            gettext('matching.form.theirLooks'),
            $fromToWeightOptions
        );

        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));

        $form = new \vc\form\Form(
            $formId,
            'GroupCreate',
            $this->path,
            $this->locale,
            'user/matching/'
        );
        shuffle($fromToFields);
        foreach ($fromToFields as $field) {
            $field->setMandatory(true);
            $form->add($field);
        }

        shuffle($fromToWeightFields);
        foreach ($fromToWeightFields as $field) {
            $field->setMandatory(true);
            $form->add($field);
        }

        if ($canSave) {
            $primaryCaption = gettext('matching.form.submit');
        } else {
            $primaryCaption = null;
        }
        if ($matchingObject === null) {
            $secondaryButtons = null;
        } else {
            $secondaryButtons = array(
                'delete' => gettext('matching.form.delete')
            );
        }
        $form->add(new \vc\form\Submit(
            $primaryCaption,
            null,
            $secondaryButtons
        ));

        return $form;
    }

    private function view($form)
    {
        $this->setTitle(gettext('matching.title'));
        $this->setForm($form);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('user/matching', true);
    }
}
