<?php
namespace vc\controller\web\help;

class HistoryController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (empty($this->siteParams) || !$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\NotFoundException();
        }

        $ticketModel = $this->getDbModel('Ticket');
        $ticketMessageModel = $this->getDbModel('TicketMessage');

        $ticket = $ticketModel->loadObject(array('hash_id' => $this->siteParams[0]));
        if ($ticket === null || $ticket->profileId !== $this->getSession()->getUserId()) {
            throw new \vc\exception\NotFoundException();
        }

        $ticketMessages = $ticketMessageModel->loadObjects(array('ticket_id' => $ticket->id));

        $helpNotificationModel = $this->getDbModel('HelpNotification');
        $helpNotificationModel->delete(array(
            'profile_id' => $this->getSession()->getUserId(),
            'ticket_id '=> $ticket->id
        ));


        $this->getView()->set('ticket', $ticket);
        $this->getView()->set('ticketMessages', $ticketMessages);

        $this->getView()->set('ticketCategories', \vc\config\Fields::getTicketCategories());

        echo $this->getView()->render('help/history', true);
    }
}
