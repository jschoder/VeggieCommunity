<?php
namespace vc\controller\web\help;

class SupportController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $currentUser = $this->getSession()->getProfile();

        $form = $this->createForm();
        $defaultFormValues = array();

        $defaultFormValues['category'] = 101;
        if (count($this->siteParams) > 0) {
            if ($this->siteParams[0] == 'reportuser' && count($this->siteParams) > 1) {
                $defaultFormValues['category'] = \vc\config\EntityTypes::PROFILE;
                $defaultFormValues['subject'] = gettext('help.subject.reportuser');
                $defaultFormValues['comment'] = str_replace(
                    '%PROFILE%',
                    $this->siteParams[1],
                    gettext('help.report.profile')
                );
            }
            if ($this->siteParams[0] == 'reportgroup' && count($this->siteParams) > 1) {
                $defaultFormValues['category'] = \vc\config\EntityTypes::GROUP;
                $defaultFormValues['subject'] = gettext('help.subject.reportgroup');
                $defaultFormValues['comment'] = str_replace(
                    '%GROUP%',
                    $this->siteParams[1],
                    gettext('help.report.group')
                );
            }
        }

        if ($currentUser != null) {
            $defaultFormValues['nickname'] = $currentUser->nickname;
            $defaultFormValues['address'] = $currentUser->email;
        }

        $form->setDefaultValues($defaultFormValues);
        $this->view($form);
    }

    public function createForm()
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'Support',
            $this->path,
            $this->locale,
            'help/support/',
            1.0
        );
        $form->add(new \vc\form\Select(
            'category',
            'category',
            gettext('help.category'),
            \vc\config\Fields::getTicketCategories()
        ))->setMandatory(true);
        $form->add(new \vc\form\Text(
            'subject',
            'subject',
            gettext('help.subject'),
            50
        ))->setMandatory(true);
        $form->add(new \vc\form\Text(
            'nickname',
            'nickname',
            gettext('help.nickname'),
            20
        ))->setMandatory(true);
        $form->add(new \vc\form\Text(
            'address',
            'address',
            gettext('help.address'),
            70
        ))->setMandatory(true)
          ->addValidator(new \vc\form\validation\MinLengthValidator(6))
          ->addValidator(new \vc\form\validation\EMailValidator());
        $form->add(new \vc\form\Text(
            'comment',
            'comment',
            gettext('help.message'),
            1000,
            null,
            \vc\form\Text::TEXTAREA
        ))->setMandatory(true);
        $form->add(new \vc\form\Submit(
            gettext('help.submit')
        ));

        return $form;
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (strpos($_POST['comment'], '<a href=') !== false) {
            $suspicionLevel = $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_HTML_IN_SUPPORT_REQUEST,
                array('formvalues' => $_POST)
            );
            if ($suspicionLevel >= \vc\config\Globals::SUSPICION_BLOCK_LEVEL) {
                throw new \vc\exception\RedirectException($this->path . 'locked/');
            }
        }

        $formValues = $_POST;
        if (empty($formValues['formid'])) {
            throw new \vc\exception\RedirectException($this->path . 'help/support/');
        } else {
            $form = $this->getForm($formValues['formid']);
            if ($form instanceof \vc\form\Form) {
                if ($form->validate($this->getDb(), $formValues)) {
                    if ($form->somethingInHoneytrap($formValues)) {
                        $this->handleHoneypot($form->getHoneypot(), $formValues);
                        $objectSaved = true;
                    } else {
                        $suspicionModel = $this->getDbModel('Suspicion');
                        $suspicionLevel = $suspicionModel->getSuspicionLevel(
                            $this->getSession()->getUserId(),
                            $this->getIp(),
                            time() - \vc\config\Globals::SUSPICION_PAST
                        );

                        $ticketModel = $this->getDbModel('Ticket');
                        $ticketMessageModel = $this->getDbModel('TicketMessage');

                        $ticket = new \vc\object\Ticket();
                        $ticket->lng = $this->locale;
                        $ticket->nickname = $formValues['nickname'];
                        $ticket->email = $formValues['address'];
                        $ticket->category = $formValues['category'];
                        $ticket->subject = $formValues['subject'];
                        $ticket->debuginfo = 'SUSPICION: ' . ($suspicionLevel === null ? 0 : $suspicionLevel) . "\n" .
                                            \vc\lib\ErrorHandler::getSystemDebugInfo(false);
                        if (!empty($_SESSION['pic_errors'])) {
                            $ticket->debuginfo .= "\nPic Errors: - " . implode("\n- ", $_SESSION['pic_errors']);
                        }
                        $objectSaved = $ticketModel->insertObject(
                            $this->getSession()->getProfile(),
                            $ticket
                        );

                        if ($objectSaved !== false) {
                            $ticketMessage = new \vc\object\TicketMessage();
                            $ticketMessage->ticketId = $objectSaved;
                            $ticketMessage->body = $formValues['comment'];

                            $ticketMessageModel->insertObject(
                                $this->getSession()->getProfile(),
                                $ticketMessage
                            );
                        }

                        $websocketMessageModel = $this->getDbModel('WebsocketMessage');
                        $websocketMessageModel->triggerMods(\vc\config\EntityTypes::STATUS);
                    }
                    
                    if ($objectSaved) {
                        $notificationKey = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext('help.saved'));
                    } else {
                        $notificationKey = $this->setNotification(self::NOTIFICATION_ERROR, gettext('help.failed'));
                    }
                    throw new \vc\exception\RedirectException(
                        $this->path . 'help/support/?notification=' . $notificationKey
                    );
                } else {
                    $this->view($form);
                }
            } else {
                throw new \vc\exception\RedirectException($this->path . 'help/support/');
            }
        }
    }

    private function view($form)
    {
        $this->setTitle(gettext('menu.help'));
        $this->getView()->set('activeMenuitem', 'help');
        $this->getView()->setHeader('robots', 'noindex, follow');

        $this->setForm($form);
        $this->getView()->set('form', $form);

        if ($this->getSession()->hasActiveSession()) {
            $ticketModel = $this->getDbModel('Ticket');
            $ticketHistory = $ticketModel->getHistory($this->getSession()->getUserId());
            $this->getView()->set('ticketHistory', $ticketHistory);

            $helpNotificationModel = $this->getDbModel('HelpNotification');
            $activeTicketNotifications = $helpNotificationModel->getFieldList(
                'ticket_id',
                array('profile_id' => $this->getSession()->getUserId())
            );
            $this->getView()->set('activeTicketNotifications', $activeTicketNotifications);

            $this->getView()->set('ticketCategories', \vc\config\Fields::getTicketCategories());
        }

        echo $this->getView()->render('help/support', true);
    }
}
