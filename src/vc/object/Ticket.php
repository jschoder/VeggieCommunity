<?php
namespace vc\object;

class Ticket
{
    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 10;
    const STATUS_SPAM = 20;

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
            'length' => 5 ),
        'lng' => array(
            'type' => 'text',
            'dbmapping' => 'lng' ),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id',
            'default' => '{{CURRENT_USER}}' ),
        'nickname' => array(
            'type' => 'text',
            'dbmapping' => 'nickname' ),
        'email' => array(
            'type' => 'text',
            'dbmapping' => 'email' ),
        'category' => array(
            'type' => 'integer',
            'dbmapping' => 'category'),
        'subject' => array(
            'type' => 'text',
            'dbmapping' => 'subject' ),
        'debuginfo' => array(
            'type' => 'text',
            'dbmapping' => 'debuginfo' ),
        'status' => array(
            'type' => 'integer',
            'dbmapping' => 'status',
            'default' => 1),
    );

    public $id;
    public $hashId;
    public $lng;
    public $profileId;
    public $nickname;
    public $email;
    public $category;
    public $subject;
    public $debuginfo;
    public $status;
}
