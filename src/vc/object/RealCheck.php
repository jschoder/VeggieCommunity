<?php
namespace vc\object;

class RealCheck
{
    const STATUS_OPEN = 1;
    const STATUS_SUBMITTED = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_CONFIRMED = 4;
    const STATUS_DENIED = 5;
    const STATUS_REOPENED = 6;
    const STATUS_REMOVED = 7;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id',
            'default' => '{{CURRENT_USER}}'),
        'code' => array(
            'type' => 'text',
            'dbmapping' => 'code'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
        'picture' => array(
            'type' => 'image',
            'dbmapping' => 'picture',
            'uploadpath' => '/pictures/real/full',
            'namelength' => 20,
            'width' => \vc\config\Globals::MAX_PICTURE_WIDTH,
            'height' => \vc\config\Globals::MAX_PICTURE_HEIGHT,
            'default' => null),
        'checkedBy' => array(
            'type' => 'datetime',
            'dbmapping' => 'checked_by',
            'default' => null),
        'status' => array(
            'type' => 'integer',
            'dbmapping' => 'status',
            'default' => self::STATUS_OPEN)
    );

    public $id;
    public $profileId;
    public $code;
    public $createdAt;
    public $picture;
    public $checkedBy;
    public $status;
}
