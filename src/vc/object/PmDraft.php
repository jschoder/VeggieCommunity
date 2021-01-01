<?php
namespace vc\object;

class PmDraft
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'senderId' => array(
            'type' => 'integer',
            'dbmapping' => 'sender_id'),
        'recipientId' => array(
            'type' => 'integer',
            'dbmapping' => 'recipient_id'),
        'body' => array(
            'type' => 'text',
            'dbmapping' => 'body'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
    );

    public $entityType;
    public $senderId;
    public $recipientId;
    public $body;
    public $createdAt;
}
