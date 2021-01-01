<?php
namespace vc\controller\web\event;

class MyEventsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('events.myevents.title'));

        $eventParticipantModel = $this->getDbModel('EventParticipant');
        $myEvents = $eventParticipantModel->getMyEvents($this->locale, $this->getSession()->getUserId());

        $eventCategories = \vc\config\Fields::getEventCategories();

        $this->getView()->set('myCalendar', $myEvents['calendar']);
        $this->getView()->set('invitations', $myEvents['invitations']);
        $this->getView()->set('bookmarks', $myEvents['bookmarks']);
        $this->getView()->set('endorsements', $myEvents['endorsements']);
        $this->getView()->set('eventCategories', $eventCategories);
        echo $this->getView()->render('event/myevents', true);
    }
}
