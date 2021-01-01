<?php
namespace vc\object;

/*

 */
class Metric
{
    // Not stored directly. Instead using the profile-table
    const TYPE_PROFILE_CREATION = 1;
    const TYPE_PROFILE_MANUAL_DELETION = 2;

    const TYPE_LOGINS_TOTAL = 10;
    // Counting every user only once
    const TYPE_LOGINS_DISTINCT = 11;
    const TYPE_LOGINS_DAYS_SINCE = 12;

    const TYPE_PM_SENT = 20;
    const TYPE_PM_NEW_CONTACTS = 21;

    const TYPE_THREADS = 30;
    const TYPE_THREAD_COMMENTS = 31;

    // All events in the future
    const TYPE_EVENTS_UPCOMING = 40;
    // All events on that day
    const TYPE_EVENTS_TODAY = 41;
    const TYPE_EVENT_PARTICIPATION_LIKELY = 42;
    const TYPE_EVENT_PARTICIPATION_UNLIKELY = 43;

    const TYPE_CHAT_MESSAGES = 50;

    // Standard and above
    const TYPE_ACTIVE_PLUS = 60;

    const PAST_WEEKS = 25;

    public static $primaryKey = array('day', 'type');

    public static $fields = array(
        'day' => array(
            'type' => 'datetime',
            'dbmapping' => 'day'),
        'type' => array(
            'type' => 'integer',
            'dbmapping' => 'type'),
        'value' => array(
            'type' => 'integer',
            'dbmapping' => 'value')
    );

    public $day;
    public $type;
    public $value;
}
