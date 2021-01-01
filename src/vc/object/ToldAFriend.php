<?php
namespace vc\object;

/*

 */
class ToldAFriend
{
    const STATUS_UNSENT = 0;
    const STATUS_SENT = 1;
    const STATUS_DENIED = 2;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'profileid' => array(
            'type' => 'integer',
            'dbmapping' => 'profileid'),
        'sender' => array(
            'type' => 'text',
            'dbmapping' => 'sender'),
        'reciever1' => array(
            'type' => 'text',
            'dbmapping' => 'reciever1'),
        'reciever2' => array(
            'type' => 'text',
            'dbmapping' => 'reciever2'),
        'reciever3' => array(
            'type' => 'text',
            'dbmapping' => 'reciever3'),
        'reciever4' => array(
            'type' => 'text',
            'dbmapping' => 'reciever4'),
        'reciever5' => array(
            'type' => 'text',
            'dbmapping' => 'reciever5'),
        'reciever6' => array(
            'type' => 'text',
            'dbmapping' => 'reciever6'),
        'subject' => array(
            'type' => 'text',
            'dbmapping' => 'subject'),
        'body' => array(
            'type' => 'text',
            'dbmapping' => 'body'),
        'created' => array(
            'type' => 'datetime',
            'dbmapping' => 'created',
            'default' => '{{CURRENT_TIME}}'),
        'isSent' => array(
            'type' => 'integer',
            'dbmapping' => 'is_sent')
    );

    public $id;
    public $profileid;
    public $sender;
    public $reciever1;
    public $reciever2;
    public $reciever3;
    public $reciever4;
    public $reciever5;
    public $reciever6;
    public $subject;
    public $body;
    public $created;
    public $isSent;
}
