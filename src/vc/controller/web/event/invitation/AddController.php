<?php
namespace vc\controller\web\event\invitation;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo $this->getView()->element(
                'notification',
                array(
                    'notification' => array(
                        'type' => self::NOTIFICATION_WARNING,
                        'message' => gettext('error.noactivesession')
                    )
                )
            );
            return;
        }

        if (empty($this->siteParams)) {
            echo $this->getView()->element(
                'notification',
                array(
                    'notification' => array(
                        'type' => self::NOTIFICATION_ERROR,
                        'message' => gettext('error.invalidrequest')
                    )
                )
            );
            return;
        }

        $eventModel = $this->getDbModel('Event');
        $eventId = $eventModel->getIdByHashId($this->siteParams[0]);
        if ($eventId === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_EVENT,
                array(
                    'siteParams' => $this->siteParams
                )
            );

            echo $this->getView()->element(
                'notification',
                array(
                    'notification' => array(
                        'type' => self::NOTIFICATION_ERROR,
                        'message' => gettext('error.invalidrequest')
                    )
                )
            );
            return;
        }

        $friendIds = $this->getOwnFriendsConfirmed();

        // Remove the ones who are already members of already invited by current user
        $eventParticipantModel = $this->getDbModel('EventParticipant');
        $eventParticipantIds = $eventParticipantModel->getParticipantIds($eventId);
        $friendIds = array_diff($friendIds, $eventParticipantIds);

        $profileModel = $this->getDbModel('Profile');
        $friendProfiles = $profileModel->getSmallProfiles(
            $this->locale,
            $friendIds,
            "last_update DESC"
        );
        $this->getView()->set('profiles', $friendProfiles);

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures(
            $this->getSession()->getUserId(),
            $friendProfiles
        );
        $this->getView()->set('pictures', $pictures);

        echo $this->getView()->render('event/invitation/add', false);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('group.invitation.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['eventId']) ||
            empty($formValues['profileId']) ||
            !is_array($formValues['profileId'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $eventModel = $this->getDbModel('Event');
        $eventId = $eventModel->getIdByHashId($formValues['eventId']);
        if ($eventId === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_EVENT,
                array(
                    'siteParams' => $this->siteParams
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $eventParticipantModel = $this->getDbModel('EventParticipant');
        $success = true;

        $currentUser = $this->getSession()->getProfile();
        foreach ($formValues['profileId'] as $profileId) {
            if (in_array(intval($profileId), $this->getOwnFriendsConfirmed())) {
                $eventParticipantObject = new \vc\object\EventParticipant();
                $eventParticipantObject->eventId = $eventId;
                $eventParticipantObject->profileId = intval($profileId);
                $eventParticipantObject->degree = \vc\object\EventParticipant::STATUS_INVITED;
                $success = $success && $eventParticipantModel->insertObject($currentUser, $eventParticipantObject);
            }
        }

        echo \vc\view\json\View::renderStatus($success);
    }
}
