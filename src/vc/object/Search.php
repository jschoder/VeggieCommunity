<?php
namespace vc\object;

class Search
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'profileid' => array(
            'type' => 'integer',
            'dbmapping' => 'profileid'),
        'name' => array(
            'type' => 'text',
            'dbmapping' => 'name' ),
        'url' => array(
            'type' => 'text',
            'dbmapping' => 'url' ),
        'messageInterval' => array(
            'type' => 'integer',
            'dbmapping' => 'message_interval'),
        'messageType' => array(
            'type' => 'integer',
            'dbmapping' => 'message_type'),
        'lastMessage' => array(
            'type' => 'datetime',
            'dbmapping' => 'last_message',
            'default' => '{{CURRENT_TIME}}')
    );

    public $id;
    public $profileid;
    public $name;
    public $url;
    public $messageInterval;
    public $messageType;
    public $lastMessage;
}
