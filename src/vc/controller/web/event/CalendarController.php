<?php
namespace vc\controller\web\event;

class CalendarController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('event.calendar.title'));

        if (count($this->siteParams) > 1) {
            $year = intval($this->siteParams[0]);
            $month  = intval($this->siteParams[1]);

            $currentYear = date('Y');
            if ($year < $currentYear - 1 || $year > $currentYear + 2) {
                throw new \vc\exception\NotFoundException();
            }
        } else {
            $year = date('Y');
            $month = date('n');
        }

        if ($year < 2016 || $year > 2050 || $month < 1 || $month > 12) {
            throw new \vc\exception\NotFoundException();
        }

        if ($this->getSession()->hasActiveSession()) {
            $termsModel = $this->getDbModel('Terms');
            if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
                throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
            }
        }

        $eventModel = $this->getDbModel('Event');
        $events = $eventModel->getCalendarEvents($this->getSession()->getUserId(), $year, $month);

        $eventCategories = \vc\config\Fields::getEventCategories();

        $pagination = new \vc\object\param\MonthPaginationObject(
            $this->path . 'events/calendar/%YEAR%/%MONTH%/',
            $year,
            $month
        );
        $pagination->setDefaultUrl($this->path . 'events/calendar/');
        $pagination->setDefaultUrlParams(array('%YEAR%' => date('Y'), '%MONTH%' => date('n')));
        $this->getView()->set('pagination', $pagination);

        $this->getView()->setHeader(
            'canonical',
            'https://www.veggiecommunity.org/' . $this->locale . '/events/calendar/'
        );
        $this->getView()->setHeader('prev', $pagination->getPrev());
        $this->getView()->setHeader('next', $pagination->getNext());

        $this->getView()->set('year', $year);
        $this->getView()->set('month', $month);
        $this->getView()->set('events', $events);
        $this->getView()->set('eventCategories', $eventCategories);
        echo $this->getView()->render('event/calendar', true);
    }
}
