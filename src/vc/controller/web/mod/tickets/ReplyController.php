<?php
namespace vc\controller\web\mod\tickets;

class ReplyController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        if (count($this->siteParams) === 0) {
            throw new \vc\exception\NotFoundException();
        }

        $ticketModel = $this->getDbModel('Ticket');
        $ticketMessageModel = $this->getDbModel('TicketMessage');

        $ticketId = intval($this->siteParams[0]);
        $ticket = $ticketModel->loadObject(array('id' => $ticketId));

        $defaults = \vc\config\Support::getDefaults($ticket->lng);

        if (array_key_exists('default', $_GET) &&
            array_key_exists($_GET['default'], $defaults)) {
            $default = $defaults[$_GET['default']]['text'];
        } else {
            $default = '';
        }

        $ticketMessages = $ticketMessageModel->loadObjects(array('ticket_id' => $ticket->id), array(), 'id DESC', 100);

        $template = \vc\config\Support::getMessage($ticket->lng, $default);
        $template = str_replace('%NICKNAME%', $ticket->nickname, $template);

        $this->setTitle('Tickets');

        $this->getView()->set('defaults', $defaults);
        $this->getView()->set('ticket', $ticket);
        $this->getView()->set('ticketMessages', $ticketMessages);
        $this->getView()->set('template', $template);
        echo $this->getView()->render('mod/tickets/reply', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $formValues = $_POST;

        $ticketModel = $this->getDbModel('Ticket');
        $ticketMessageModel = $this->getDbModel('TicketMessage');
        $systemMessageModel = $this->getDbModel('SystemMessage');

        $ticket = $ticketModel->loadObject(array('id' => intval($formValues['ticketId'])));
        if (empty($ticket)) {
            throw new \vc\exception\NotFoundException();
        }

        if ($ticket->lng === 'de') {
            $subject = 'Re: Supportanfrage';
            $ticketName = 'Ticketverlauf';
        } else {
            $subject = 'Re: Support Request';
            $ticketName = 'Tickethistory';
        }
        if (!empty($ticket->hashId)) {
            $subject .= ' [#' . $ticket->hashId . ']';
        }

        $message = $formValues['message'];
        $mailBody = $message . "\n\n";
        if (!empty($ticket->profileId)) {
            $mailBody .= $ticketName . ': ' . 'https://www.veggiecommunity.org/' . $ticket->lng .
                         '/help/support/history/' . $ticket->hashId . '/' . "\n\n";
        }
        $mailBody .= \vc\config\Support::getMessageFooter($ticket->lng);
        $lastTicketMessage = $ticketMessageModel->loadObject(
            array(
                'ticket_id' => $ticket->id,
                'by_admin' => 0
            ),
            array(),
            'id DESC'
        );
        // Attach original message
        if ($lastTicketMessage !== null) {
            if ($ticket->lng === 'de') {
                $formatedCreatedAt = date('d.m.Y H:i:s', strtotime($lastTicketMessage->createdAt));
            } else {
                $formatedCreatedAt = date('m/d/Y H:i:s', strtotime($lastTicketMessage->createdAt));
            }
            $mailBody = str_replace('%CREATED_AT%', $formatedCreatedAt, $mailBody);
            $mailBody .= '> ' . str_replace("\n", "\n> ", trim(strip_tags($lastTicketMessage->body)));
        }

        $added = $systemMessageModel->add(
            $ticket->email,
            $subject,
            $mailBody,
            array(),
            \vc\object\SystemMessage::MAIL_CONFIG_SUPPORT
        );

        $ticketMessage = new \vc\object\TicketMessage();
        $ticketMessage->ticketId = $ticket->id;
        $ticketMessage->byAdmin = 1;
        $ticketMessage->body = $message;
        $ticketMessageModel->insertObject(
            $this->getSession()->getProfile(),
            $ticketMessage
        );

        if ($added) {
            $helpNotificationModel = $this->getDbModel('HelpNotification');
            $helpNotificationModel->add($ticket->profileId, $ticket->id);

            if(!empty($formValues['close']) && $formValues['close'] === '1') {
                $ticketModel->update(
                    array('id' => $ticket->id),
                    array('status' => 0)
                );
            }
        }

        throw new \vc\exception\RedirectException($this->path . 'mod/tickets/');
    }
}
