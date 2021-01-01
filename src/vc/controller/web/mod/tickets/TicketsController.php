<?php
namespace vc\controller\web\mod\tickets;

class TicketsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        if (count($this->siteParams) > 0) {
            $statusFilter = intval($this->siteParams[0]);
            $orderBy = 'id DESC';
        } else {
            $statusFilter = \vc\object\Ticket::STATUS_OPEN;
            $orderBy = 'id ASC';
        }

        $ticketModel = $this->getDbModel('Ticket');
        $tickets = $ticketModel->loadObjects(array('status' => $statusFilter), array(), $orderBy, 100);

        $this->setTitle('Tickets');

        $this->getView()->set('statusFilter', $statusFilter);
        $this->getView()->set('tickets', $tickets);
        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/tickets/tickets', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        if (array_key_exists('tickets', $_POST)) {
            $ticketModel = $this->getDbModel('Ticket');

            foreach ($_POST['tickets'] as $ticketId => $newStatus) {
                if (is_numeric($newStatus)) {
                    $ticketModel->update(
                        array('id' => $ticketId),
                        array('status' => $newStatus)
                    );
                }
            }
        }
        throw new \vc\exception\RedirectException($this->path . 'mod/tickets/');
    }
}
