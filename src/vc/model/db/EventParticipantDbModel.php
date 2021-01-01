<?php
namespace vc\model\db;

class EventParticipantDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_event_participant';
    const OBJECT_CLASS = '\\vc\object\\EventParticipant';

    public function isParticipant($eventId, $profileId)
    {
        $query = 'SELECT count(*)
                  FROM vc_event_participant
                  WHERE event_id = ? AND profile_id = ?';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($eventId), intval($profileId))
        );
        $statement->bind_result($count);
        $statement->fetch();
        $statement->close();
        return $count > 0;
    }

    public function getParticipants($eventId)
    {
        $query = 'SELECT profile_id, degree, is_host
                  FROM vc_event_participant
                  WHERE event_id = ? AND degree IN (' .
                      \vc\object\EventParticipant::STATUS_PARTICIPATING_SURE . ',' .
                      \vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY . ',' .
                      \vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY . ',' .
                      \vc\object\EventParticipant::STATUS_ENDORSING .
                ') ORDER BY last_update DESC';
        $statement = $this->getDb()->queryPrepared($query, array(intval($eventId)));

        $statement->bind_result(
            $profileId,
            $degree,
            $isHost
        );
        $participants = array();
        $participants[\vc\object\EventParticipant::STATUS_PARTICIPATING_SURE] = array();
        $participants[\vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY] = array();
        $participants[\vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY] = array();
        $participants[\vc\object\EventParticipant::STATUS_ENDORSING] = array();
        while ($statement->fetch()) {
            $participants[$degree][$profileId] = $isHost;
        }
        $statement->close();

        return $participants;
    }

    public function getParticipantIds($eventId)
    {
        $profileIds = array();
        $query = 'SELECT profile_id FROM vc_event_participant WHERE event_id = ?';
        $statement = $this->getDb()->queryPrepared($query, array(intval($eventId)));
        $statement->bind_result(
            $profileId
        );
        while ($statement->fetch()) {
            $profileIds[] = $profileId;
        }
        $statement->close();

        return $profileIds;
    }

    public function getMyEvents($locale, $profileId)
    {
        $invitorProfileId = array();
        $eventParticipations = $this->loadObjects(
            array(
                'profile_id' => $profileId,
                'degree !=' => \vc\object\EventParticipant::STATUS_PARTICIPATING_NOT
            )
        );

        $myEventParticipations = array();
        foreach ($eventParticipations as $eventParticipation) {
            $myEventParticipations[$eventParticipation->eventId] = $eventParticipation;
            if ($eventParticipation->degree == \vc\object\EventParticipant::STATUS_INVITED) {
                $invitorProfileId[] = $eventParticipation->createdBy;
            }
        }

        $invitorProfileId = array_unique($invitorProfileId);

        $profileModel = $this->getDbModel('Profile');
        $invitorProfiles = $profileModel->getSmallProfiles($locale, $invitorProfileId);

        if (empty($myEventParticipations)) {
            $events = array();
        } else {
            $eventModel = $this->getDbModel('Event');
            $events = $eventModel->loadObjects(
                array(
                    'id' => array_keys($myEventParticipations),
                    'end_date >' => date('Y-m-d 00:00:00')
                ),
                array(),
                'start_date ASC'
            );
        }

        $myEvents = array(
            'calendar' => array(),
            'invitations' => array(),
            'bookmarks' => array(),
            'endorsements' => array(),
        );
        foreach ($events as $event) {
            $eventParticipation = $myEventParticipations[$event->id];
            switch ($eventParticipation->degree) {
                case \vc\object\EventParticipant::STATUS_INVITED:
                    $myEventsKey = 'invitations';
                    break;
                case \vc\object\EventParticipant::STATUS_PARTICIPATING_SURE:
                case \vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY:
                case \vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY:
                    $myEventsKey = 'calendar';
                    break;
                case \vc\object\EventParticipant::STATUS_ENDORSING:
                    $myEventsKey = 'endorsements';
                    break;
                case \vc\object\EventParticipant::STATUS_BOOKMARK:
                    $myEventsKey = 'bookmarks';
                    break;
                default:
                    \vc\lib\ErrorHandler::error(
                        'Invalid participation degree',
                        __FILE__,
                        __LINE__,
                        array(
                            'eventId' => $eventParticipation->eventId,
                            'profileId' => $eventParticipation->profileId,
                            'degree' => $eventParticipation->degree
                        )
                    );
                    $myEventsKey = 'bookmarks';
            }

            $eventArray = array(
                'event' => $event,
                'participation' => $eventParticipation
            );
            if ($eventParticipation->degree == \vc\object\EventParticipant::STATUS_INVITED) {
                foreach ($invitorProfiles as $profile) {
                    if ($eventParticipation->createdBy == $profile->id) {
                        $eventArray['invitor'] = $profile;
                    }
                }
            }
            $myEvents[$myEventsKey][] = $eventArray;
        }

        return $myEvents;
    }

    public function setParticipation($eventId, $userId, $degree)
    {
        $now = date('Y-m-d H:i:s');
        $query = 'INSERT INTO vc_event_participant SET
                     event_id = ?,
                     profile_id = ?,
                     degree = ?,
                     created_by = ?,
                     created_at = ?,
                     last_update = ?
                  ON DUPLICATE KEY UPDATE
                     degree = ?,
                     last_update = ?';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param(
            'iiiissis',
            $eventId,
            $userId,
            $degree,
            $userId,
            $now,
            $now,
            $degree,
            $now
        );
        $executed = $statement->execute();
        $statement->close();
        return $executed;
    }
}
