<?php
namespace vc\controller\web\help;

class ReplyController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {

        var_dump($_POST);

        if (!$request->hasParameter('ticket_id') ||
            !$request->hasParameter('body') ||
            !$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\NotFoundException();
        }

        $body = trim($request->getText('body'));
        if (empty($body)) {
            throw new \vc\exception\RedirectException(
                $this->path . 'help/support/history/' . $request->getText('ticket_id') . '/'
            );
        }
        $ticketModel = $this->getDbModel('Ticket');
        $ticketMessageModel = $this->getDbModel('TicketMessage');

        $ticket = $ticketModel->loadObject(array('hash_id' => $request->getText('ticket_id')));
        if ($ticket === null || $ticket->profileId !== $this->getSession()->getUserId()) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $request->getValues()
                )
            );
            throw new \vc\exception\NotFoundException();
        }

        $ticketMessage = new \vc\object\TicketMessage();
        $ticketMessage->ticketId = $ticket->id;
        $ticketMessage->byAdmin = 0;
        $ticketMessage->email = null;
        $ticketMessage->body = $body;
        $messageSaved = $ticketMessageModel->insertObject($this->getSession()->getProfile(), $ticketMessage);


        $ticketModel->update(
            array(
                'id' => $ticket->id
            ),
            array(
                'status' => \vc\object\Ticket::STATUS_OPEN
            )
        );

        if ($messageSaved) {
            $notification = $this->setNotification(
                self::NOTIFICATION_SUCCESS,
                gettext('help.history.reply.success')
            );
        } else {
            $notification = $this->setNotification(
                self::NOTIFICATION_ERROR,
                gettext('help.history.reply.failed')
            );
        }
        throw new \vc\exception\RedirectException(
            $this->path . 'help/support/history/' . $ticket->hashId . '/?notification=' . $notification
        );
    }
}
