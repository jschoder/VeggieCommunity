<?php
namespace vc\object;

/*

 */
class SystemMessage
{
    const MAIL_CONFIG_NOTIFY = 1;
    const MAIL_CONFIG_SUPPORT = 2;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'recipient' => array(
            'type' => 'text',
            'dbmapping' => 'recipient' ),
        'subject' => array(
            'type' => 'text',
            'dbmapping' => 'subject' ),
        'body' => array(
            'type' => 'text',
            'dbmapping' => 'body' ),
        'created' => array(
            'type' => 'datetime',
            'dbmapping' => 'created',
            'default' => '{{CURRENT_TIME}}' ),
        'attachments' => array(
            'type' => 'text',
            'dbmapping' => 'attachments'),
        'mailConfig' => array(
            'type' => 'integer',
            'dbmapping' => 'mail_config',
            'default' => 1),
    );

    public $id;
    public $recipient;
    public $subject;
    public $body;
    public $attachments;
    public $created;
    public $mailConfig;
}
