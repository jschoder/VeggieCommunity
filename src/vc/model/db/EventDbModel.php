<?php
namespace vc\model\db;

class EventDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_event';
    const OBJECT_CLASS = '\\vc\object\\Event';

    public function getEventsByParticipant($userId)
    {
        $query = 'SELECT hash_id, start_date, name FROM vc_event
                  INNER JOIN vc_event_participant ON vc_event.id = vc_event_participant.event_id
                  WHERE vc_event_participant.profile_id = ?
                        AND vc_event_participant.degree IN (10,13,17)
                        AND vc_event.deleted_at IS NULL
                        AND vc_event.end_date > NOW()
                  ORDER BY vc_event.start_date';
        $statement = $this->getDb()->queryPrepared($query, array(intval($userId)));
        $events = array();
        $statement->bind_result(
            $hashId,
            $startDate,
            $name
        );
        while ($statement->fetch()) {
            $events[$hashId] = array(strtotime($startDate), $name);
        }
        $statement->close();
        return $events;
    }

    /**
     *
     * @param integer $profileId
     * @param mixed $event Either the eventObject or the id of the event.
     * @param object $eventParticipant
     * @return boolean
     */
    public function canSeeEvent($profileId, $event, $eventParticipant = false)
    {
        if (!is_object($event)) {
            $event = $this->loadObject(array('id' => $event));

            if ($event === null) {
                \vc\lib\ErrorHandler::error(
                    'Can\'t find event in canSeeEvent',
                    __FILE__,
                    __LINE__,
                    array(
                        'event' => $event
                    )
                );
                return false;
            }
        }
        switch ($event->eventVisibility) {
            case \vc\object\Event::EVENT_VISIBILITY_PUBLIC:
                return true;
            case \vc\object\Event::EVENT_VISIBILITY_REGISTERED:
                if (empty($profileId)) {
                    return false;
                } else {
                    return true;
                }
                break;
            case \vc\object\Event::EVENT_VISIBILITY_GROUP:
                if (empty($profileId) || empty($event->groupId)) {
                    return false;
                } else {
                    $groupMemberModel = $this->getDbModel('GroupMember');
                    $isMember = $groupMemberModel->isMember($event->groupId, $profileId);
                    return $isMember !== null && $isMember !== false;
                }
            case \vc\object\Event::EVENT_VISIBILITY_FRIENDS:
                return false;
            case \vc\object\Event::EVENT_VISIBILITY_INVITEE:
                if ($eventParticipant === false) {
                    $query = 'SELECT count(*) FROM vc_event_participant WHERE event_id = ? AND profile_id = ?';
                    $statement = $this->getDb()->queryPrepared($query, array(intval($event->id), intval($profileId)));
                    $statement->bind_result(
                        $participantCount
                    );
                    $statement->fetch();
                    $statement->close();
                    return ($participantCount > 0);
                } else {
                    return $eventParticipant !== null;
                }
                break;
            default:
                return false;
        }
    }

    public function getCalendarEvents($profileId, $year, $month)
    {
        if ($month < 10) {
            $firstDay = $year . '-0' . $month . '-01';
        } else {
            $firstDay = $year . '-' . $month . '-01';
        }
        $firstDayTime = strtotime($firstDay);
        $lastDay = date('Y-m-t', strtotime($firstDay));
        $lastDayTime = strtotime($lastDay);
        $events = $this->loadObjects(
            array(
                'end_date >=' => $firstDay . ' 00:00:00',
                'start_date <=' => $lastDay . ' 23:59:59',
                'deleted_at IS NULL'
            ),
            array(),
            'start_date ASC'
        );
        $oneDayInterval = new \DateInterval('P1D');
        $nextDayOverlap = \vc\config\Globals::SAME_DAY_HOUR * 3600;

        if (empty($profileId) || empty($events)) {
            $participatingEventIds = array();
        } else {
            $participatingEventIds = array();

            $eventIds = array();
            foreach ($events as $event) {
                $eventIds[] = $event->id;
            }
            $query = 'SELECT event_id FROM vc_event_participant ' .
                     'WHERE profile_id = ? AND event_id IN (' . trim(str_repeat('?, ', count($eventIds)), ', ') . ')';
            array_unshift($eventIds, $profileId);

            $statement = $this->getDb()->queryPrepared($query, $eventIds);
            $statement->bind_result($eventId);
            while ($statement->fetch()) {
                $participatingEventIds[] = $eventId;
            }
            $statement->close();
        }

        $days = array();
        foreach ($events as $event) {
            switch ($event->eventVisibility) {
                case \vc\object\Event::EVENT_VISIBILITY_PUBLIC:
                    $eventIsVisible = true;
                    break;
                case \vc\object\Event::EVENT_VISIBILITY_REGISTERED:
                    if (empty($profileId)) {
                        $eventIsVisible = false;
                    } else {
                        $eventIsVisible = true;
                    }
                    break;
                case \vc\object\Event::EVENT_VISIBILITY_GROUP:
                    $eventIsVisible = false;
                    break;
                case \vc\object\Event::EVENT_VISIBILITY_FRIENDS:
                    $eventIsVisible = false;
                    break;
                case \vc\object\Event::EVENT_VISIBILITY_INVITEE:
                    $eventIsVisible = in_array($event->id, $participatingEventIds);
                    break;
                default:
                    return false;
            }

            if ($eventIsVisible) {
                $start = strtotime($event->startDate);
                $end = strtotime($event->endDate);
                $dayDateTime = new \DateTime(date('Y-m-d 00:00:00', $start));
                while ($dayDateTime->getTimestamp() <= $start ||
                       $dayDateTime->getTimestamp() + $nextDayOverlap < $end) {
                    $timestamp = $dayDateTime->getTimestamp();
                    if ($timestamp >= $firstDayTime && $timestamp <= $lastDayTime) {
                        $day = $dayDateTime->format('j');
                        if (!array_key_exists($day, $days)) {
                            $days[$day] = array();
                        }
                        $days[$day][] = $event;
                    }
                    $dayDateTime->add($oneDayInterval);
                }
            }
        }
        return $days;
    }
}
