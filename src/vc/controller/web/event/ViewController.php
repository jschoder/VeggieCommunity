<?php
namespace vc\controller\web\event;

class ViewController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (count($this->siteParams) === 0) {
            throw new \vc\exception\NotFoundException();
        }
        if (count($this->siteParams) > 2 && $this->siteParams[1] == 'discussion') {
            $this->getView()->setHeader('robots', 'noindex, follow');

            if (intval($this->siteParams[2]) === 0) {
                throw new \vc\exception\NotFoundException();
            }
        }

        if ($this->getSession()->hasActiveSession()) {
            $termsModel = $this->getDbModel('Terms');
            if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
                throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
            }
        }

        $eventModel = $this->getDbModel('Event');
        $eventObject = $eventModel->loadObject(array('hash_id' => $this->siteParams[0]));
        if ($eventObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array('SiteParams' => $this->siteParams)
            );
            throw new \vc\exception\NotFoundException();
        }
        if ($eventObject->deletedAt !== null) {
            throw new \vc\exception\NotFoundException();
        }

        if ($this->getSession()->hasActiveSession()) {
            $eventParticipantModel = $this->getDbModel('EventParticipant');
            $eventParticipant = $eventParticipantModel->loadObject(
                array(
                    'profile_id' => $this->getSession()->getUserId(),
                    'event_id' => $eventObject->id
                )
            );
        } else {
            $eventParticipant = null;
        }
        $this->getView()->set('eventParticipant', $eventParticipant);

        if (!$eventModel->canSeeEvent(
            $this->getSession()->getUserId(),
            $eventObject,
            $eventParticipant
        )) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_INVISIBLE_EVENT,
                array('SiteParams' => $this->siteParams)
            );
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle($eventObject->name);
        $this->getView()->setHeader(
            'description',
            str_replace(array("\n", "\r"), array(' ', ''), $eventObject->description)
        );
        if (empty($eventObject->image)) {
            $this->getView()->setHeader(
                'image',
                'https://www.veggiecommunity.org' . $this->imagesPath . 'default-event.png'
            );
        } else {
            $this->getView()->setHeader(
                'image',
                'https://www.veggiecommunity.org/events/picture/crop/200/200/' . $eventObject->image
            );
        }
        $this->getView()->set('event', $eventObject);

        $this->getView()->set('locationRegionName', '');
        if (!empty($eventObject->locationRegion)) {
            $regions = \vc\config\Fields::getRegions();
            $this->getView()->set(
                'locationRegionName',
                $regions[$eventObject->locationCountry][$eventObject->locationRegion]
            );
        }

        $this->getView()->set('locationCountryName', '');
        if (!empty($eventObject->locationCountry)) {
            $cacheModel = $this->getModel('Cache');
            $countries = $cacheModel->getCountries($this->locale);
            $indexedCountries = array();
            foreach ($countries as $country) {
                if ($country[0] == $eventObject->locationCountry) {
                    $this->getView()->set('locationCountryName', $country[1]);
                }
            }
        }

        $defaultTab = 'info';
        if (count($this->siteParams) > 1 &&
            $this->siteParams[1] == 'discussion') {
            $defaultTab = 'discussion';
        }
        $this->getView()->set('defaultTab', $defaultTab);

        $eventCategories = \vc\config\Fields::getEventCategories();
        $this->getView()->set('eventCategories', $eventCategories);

        $canSeeGuests = $this->canSeeGuests($eventObject, $eventParticipant);
        if ($canSeeGuests) {
            $this->loadGuests($eventObject);
        } else {
            $this->getView()->set('participants', array());
        }
        $this->loadForum($eventObject, $eventParticipant);

        echo $this->getView()->render('event/view', true);
    }

    private function canSeeGuests($event, $eventParticipant)
    {
        switch ($event->guestVisibility) {
            case \vc\object\Event::GUEST_VISIBILITY_REGISTERED:
                return $this->getSession()->hasActiveSession();
            case \vc\object\Event::GUEST_VISIBILITY_GROUP:
                $groupMemberModel = $this->getDbModel('GroupMember');
                $isMember = $groupMemberModel->isMember($event->groupId, $this->getSession()->getUserId());
                return $isMember !== null && $isMember !== false;
            case \vc\object\Event::GUEST_VISIBILITY_FRIENDS:
                return false;
            case \vc\object\Event::GUEST_VISIBILITY_INVITEE:
                return $eventParticipant !== null;
            default:
                return false;
        }
    }

    private function loadGuests($eventObject)
    {
        $eventParticipantModel = $this->getDbModel('EventParticipant');
        $participants = $eventParticipantModel->getParticipants($eventObject->id);

        $participantProfileIds = array();
        foreach ($participants as $degree => $profiles) {
            $participantProfileIds = array_merge($participantProfileIds, array_keys($profiles));
        }

        $profileModel = $this->getDbModel('Profile');
        $pictureModel = $this->getDbModel('Picture');
        $participantProfiles = $profileModel->getSmallProfiles($this->locale, $participantProfileIds);
        $indexedParticipantProfiles = array();
        foreach ($participantProfiles as $profile) {
            $indexedParticipantProfiles[$profile->id] = $profile;
        }
        $participantPictures = $pictureModel->readPictures(
            $this->getSession()->getUserId(),
            $participantProfiles
        );
        $this->getView()->set('participants', $participants);
        $this->getView()->set('participantProfiles', $indexedParticipantProfiles);
        $this->getView()->set('participantPictures', $participantPictures);
    }

    private function loadForum($eventObject, $eventParticipant)
    {
        $forumService = new \vc\model\service\forum\EventForumService(
            $this->modelFactory,
            $this->path,
            $eventObject,
            $this->getSession()->getUserId(),
            $eventParticipant,
            $this->getSession()->isAdmin()
        );

        if (count($this->siteParams) > 2 && $this->siteParams[1] == 'discussion') {
            $page = intval($this->siteParams[2]);
        } else {
            $page = 0;
        }

        $forumService->loadThreads(
            $this,
            $this->getView(),
            $this->getSession()->getUserId(),
            $page
        );

        if (empty($forumService->getThreads())) {
            $this->getView()->setHeader('robots', 'noindex, follow');
        }
    }
}
