<?php
namespace vc\controller\web\user\search;

class SavedController extends \vc\controller\web\AbstractWebController
{

    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $defaultValues = array(
            'searches' => array()
        );
        $searchModel = $this->getDbModel('Search');
        $savedSearches = $searchModel->loadObjects(
            array('profileid' => $this->getSession()->getUserId()),
            array(),
            'weight ASC, id ASC'
        );
        foreach ($savedSearches as $savedSearch) {
            $defaultValues['searches'][$savedSearch->id] = array(
                'name' => $savedSearch->name,
                'interval' => $savedSearch->messageInterval,
                'type' => $savedSearch->messageType
            );
        }

        $form = $this->createForm($savedSearches);
        $form->setDefaultValues($defaultValues);
        $this->view($form, empty($savedSearches));
    }

    private function createForm($savedSearches)
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'SavedSearches',
            $this->path,
            $this->locale,
            'user/search/saved/'
        );

        $multiple = new \vc\form\Multiple(
            'searches',
            null,
            null
        );
        $form->add($multiple);
        $multiple->setSortable();

        $multiple->add(new \vc\form\Text(
            'searches.name',
            'name',
            gettext('result.savesearch.name'),
            256,
            gettext('result.savesearch.name')
        ))->setSmall(true);
        $multiple->add(new \vc\form\Select(
            'searches.interval',
            'interval',
            null,
            array(
                '0' => gettext('result.savesearch.message.none'),
                '1' => gettext('result.savesearch.message.daily'),
                '7' => gettext('result.savesearch.message.weekly'),
                '30' => gettext('result.savesearch.message.monthly')
            )
        ));
        $multiple->add(new \vc\form\Select(
            'searches.type',
            'type',
            null,
            array(
                '1' => gettext('result.savesearch.message.new_profiles'),
                '2' => gettext('result.savesearch.message.updated_profiles')
            )
        ));

        $infoTexts = array();
        foreach ($savedSearches as $savedSearch) {
            $url = $this->path . 'user/result/?' . str_replace('&', '&amp;', $savedSearch->url);
            $infoTexts[$savedSearch->id] = '&nbsp;<a href="' . $url . '">' .
                                           gettext('savedsearch.openResult') .
                                           '</a>&nbsp;';
        }

        $multiple->add(new \vc\form\InfoText(null, $infoTexts));
        $multiple->add(new \vc\form\Checkbox(
            'searches.delete',
            'delete',
            gettext('savedsearch.deleteSearch'),
            true
        ));

        $form->add(new \vc\form\Submit(gettext('savedsearch.submit')));
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
            throw new \vc\exception\RedirectException($this->path . 'user/search/saved/');
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {

                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                    }

                    $searchModel = $this->getDbModel('Search');
                    foreach ($formValues['searches'] as $searchId => $search) {
                        if (empty($search['delete'])) {
                            $updateValues = array();
                            if (!empty($search['name'])) {
                                $updateValues['name'] = $search['name'];
                            }
                            if (!empty($search['interval'])) {
                                $updateValues['message_interval'] = $search['interval'];
                            }
                            if (!empty($search['type'])) {
                                $updateValues['message_type'] = $search['type'];
                            }
                            if (!empty($search['weight'])) {
                                $updateValues['weight'] = $search['weight'];
                            }

                            if (!empty($updateValues)) {
                                $searchModel->update(
                                    array(
                                        'id' => intval($searchId),
                                        'profileid' => $this->getSession()->getUserId()
                                    ),
                                    $updateValues
                                );
                            }
                        } else {
                            $searchModel->delete(
                                array(
                                    'id' => intval($searchId)
                                )
                            );
                        }
                    }

                    $notification = $this->setNotification(
                        self::NOTIFICATION_SUCCESS,
                        gettext('savedsearch.update.success')
                    );
                    throw new \vc\exception\RedirectException(
                        $this->path . 'user/search/saved/?notification=' . $notification
                    );
                } else {
                    $this->getView()->set(
                        'notification',
                        array('type' => self::NOTIFICATION_WARNING, 'message' => gettext('form.validationFailed'))
                    );
                    $form->setDefaultValues($formValues);
                    $this->view($form, false);
                }
            } else {
                throw new \vc\exception\RedirectException($this->path . 'user/search/saved/');
            }
        }
    }

    private function view($form, $isEmpty)
    {
        $this->setTitle(gettext('menu.search.saved'));

        $this->setForm($form);
        $this->getView()->set('form', $form);
        $this->getView()->set('isEmpty', $isEmpty);
        echo $this->getView()->render('user/search/saved', true);
    }
}
