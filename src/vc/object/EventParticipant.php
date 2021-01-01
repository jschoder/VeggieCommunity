<?php
namespace vc\object;

/*

 */
class EventParticipant
{
    const STATUS_INVITED = 1;
    const STATUS_PARTICIPATING_SURE = 10;
    const STATUS_PARTICIPATING_LIKELY = 13;
    const STATUS_PARTICIPATING_UNLIKELY = 17;
    const STATUS_PARTICIPATING_NOT = 20;
    const STATUS_ENDORSING = 30;
    const STATUS_BOOKMARK = 50;

    public static $primaryKey = array('eventId', 'profileId');

    public static $fields = array(
        'eventId' => array(
            'type' => 'integer',
            'dbmapping' => 'event_id' ),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id' ),
        'degree' => array(
            'type' => 'integer',
            'dbmapping' => 'degree' ),
        'isHost' => array(
            'type' => 'integer',
            'dbmapping' => 'is_host',
            'default' => 0),
        'createdBy' => array(
            'type' => 'integer',
            'dbmapping' => 'created_by',
            'default' => '{{CURRENT_USER}}'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
        'lastUpdate' => array(
            'type' => 'datetime',
            'dbmapping' => 'last_update',
            'default' => '{{CURRENT_TIME}}'),
    );

    public $eventId;
    public $profileId;
    public $degree;
    public $isHost;
    public $createdBy;
    public $createdAt;
    public $lastUpdate;
}
