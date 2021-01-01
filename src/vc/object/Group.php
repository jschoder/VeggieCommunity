<?php
namespace vc\object;

/*

 */
class Group
{
    const MEMBER_VISBILITY_MEMBER_ONLY = 1;
    const MEMBER_VISBILITY_SITE_MEMBERS = 2;

    const ACTIVITY_VERY_LOW = 1;
    const ACTIVITY_LOW = 2;
    const ACTIVITY_AVERAGE = 3;
    const ACTIVITY_HIGH = 4;
    const ACTIVITY_VERY_HIGH = 5;

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
        'image' => array(
            'type' => 'image',
            'dbmapping' => 'image',
            'uploadpath' => '/pictures/groups/full',
            'namelength' => 20,
            'width' => \vc\config\Globals::MAX_PICTURE_WIDTH,
            'height' => \vc\config\Globals::MAX_PICTURE_HEIGHT),
        'name' => array(
            'type' => 'text',
            'dbmapping' => 'name' ),
        'description' => array(
            'type' => 'text',
            'dbmapping' => 'description'),
        'rules' => array(
            'type' => 'text',
            'dbmapping' => 'rules' ),
        'modMessage' => array(
            'type' => 'text',
            'dbmapping' => 'mod_message' ),
        'language' => array(
            'type' => 'text',
            'dbmapping' => 'language' ),
        'memberVisibility' => array(
            'type' => 'integer',
            'dbmapping' => 'member_visibility',
            'default' => self::MEMBER_VISBILITY_SITE_MEMBERS),
        'autoConfirmMembers' => array(
            'type' => 'integer',
            'dbmapping' => 'auto_confirm_members',
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
    );

    public $id;
    public $hashId;
    public $image;
    public $name;
    public $description;
    public $rules;
    public $modMessage;
    public $language;
    public $memberVisibility;
    public $autoConfirmMembers;
    public $createdBy;
    public $createdAt;
    public $deletedBy;
    public $deletedAt;
}
