<?php
namespace vc\controller\web\event;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        if (!$request->hasParameter('id')) {
            throw new \vc\exception\NotFoundException();
        }

        $eventModel = $this->getDbModel('Event');
        $eventModel->update(
            array(
                'deleted_by' => $this->getSession()->getUserId(),
                'deleted_at' => date('Y-m-d H:i:s')
            ),
            array(
                'hash_id' => $request->getText('id'),
                'created_by' => $this->getSession()->getUserId()
            )
        );

        // :TODO: JOE - add undo

        $notification = $this->setNotification(
            self::NOTIFICATION_SUCCESS,
            gettext('event.delete.success')
        );
        throw new \vc\exception\RedirectException(
            $this->path . 'events/calendar/?notification=' . $notification
        );
    }
}
