<?php
namespace vc\object;

/*

 */
class Event
{
    const EVENT_VISIBILITY_PUBLIC = 1;
    const EVENT_VISIBILITY_REGISTERED = 2;
    const EVENT_VISIBILITY_GROUP = 3;
    const EVENT_VISIBILITY_FRIENDS = 4;
    const EVENT_VISIBILITY_INVITEE = 5;

    const GUEST_VISIBILITY_REGISTERED = 2;
    const GUEST_VISIBILITY_GROUP = 3;
    const GUEST_VISIBILITY_FRIENDS = 4;
    const GUEST_VISIBILITY_INVITEE = 5;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'hashId' => array(
            'type' => 'text',
            'dbmapping' => 'hash_id',
            'default' => '{{UNIQUE_TOKEN}}',
            'length' => 7 ),
        'groupId' => array(
            'type' => 'integer',
            'dbmapping' => 'group_id' ),
        'name' => array(
            'type' => 'text',
            'dbmapping' => 'name' ),
        'description' => array(
            'type' => 'text',
            'dbmapping' => 'description' ),
        'startDate' => array(
            'type' => 'datetime',
            'dbmapping' => 'start_date'),
        'endDate' => array(
            'type' => 'datetime',
            'dbmapping' => 'end_date'),
        'image' => array(
            'type' => 'image',
            'dbmapping' => 'image',
            'uploadpath' => '/pictures/events/full',
            'namelength' => 20,
            'width' => \vc\config\Globals::MAX_PICTURE_WIDTH,
            'height' => \vc\config\Globals::MAX_PICTURE_HEIGHT),
        'locationCaption' => array(
            'type' => 'text',
            'dbmapping' => 'location_caption'),
        'locationStreet' => array(
            'type' => 'text',
            'dbmapping' => 'location_street'),
        'locationPostal' => array(
            'type' => 'text',
            'dbmapping' => 'location_postal'),
        'locationCity' => array(
            'type' => 'text',
            'dbmapping' => 'location_city'),
        'locationRegion' => array(
            'type' => 'text',
            'dbmapping' => 'location_region'),
        'locationCountry' => array(
            'type' => 'integer',
            'dbmapping' => 'location_country'),
        'locationLat' => array(
            'type' => 'double',
            'dbmapping' => 'location_lat',
            'default' => 0),
        'locationLng' => array(
            'type' => 'double',
            'dbmapping' => 'location_lng',
            'default' => 0),
        'url' => array(
            'type' => 'text',
            'dbmapping' => 'url'),
        'fbUrl' => array(
            'type' => 'text',
            'dbmapping' => 'fb_url'),
        'eventVisibility' => array(
            'type' => 'integer',
            'dbmapping' => 'event_visibility'),
        'guestVisibility' => array(
            'type' => 'integer',
            'dbmapping' => 'guest_visibility'),
        'canGuestInvite' => array(
            'type' => 'integer',
            'dbmapping' => 'can_guest_invite',
            'default' => 0),
        'categoryId' => array(
            'type' => 'integer',
            'dbmapping' => 'category_id',
            'default' => 0),
        'createdBy' => array(
            'type' => 'integer',
            'dbmapping' => 'created_by',
            'default' => '{{CURRENT_USER}}'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
        'deletedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'deleted_by'),
        'deletedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'deleted_at'),
        'feedThreadId' => array(
            'type' => 'integer',
            'dbmapping' => 'feed_thread_id'),
    );

    public $id;
    public $hashId;
    public $groupId;
    public $name;
    public $description;
    public $startDate;
    public $endDate;
    public $image;
    public $locationCaption;
    public $locationStreet;
    public $locationPostal;
    public $locationCity;
    public $locationRegion;
    public $locationCountry;
    public $locationLat;
    public $locationLng;
    public $url;
    public $fbUrl;
    public $eventVisibility;
    public $guestVisibility;
    public $canGuestInvite;
    public $categoryId;
    public $createdBy;
    public $createdAt;
    public $deletedBy;
    public $deletedAt;
    public $feedThreadId;
}
