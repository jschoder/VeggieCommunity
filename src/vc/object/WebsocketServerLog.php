<?php
namespace vc\object;

/*

 */
class WebsocketServerLog
{
    const LOG_LEVEL_DEBUG = 1;
    const LOG_LEVEL_INFO = 2;
    const LOG_LEVEL_WARN = 3;
    const LOG_LEVEL_ERROR = 4;

    public static $primaryKey = array();

    public static $fields = array(
        'messageType' => array(
            'type' => 'integer',
            'dbmapping' => 'message_type'),
        'message' => array(
            'type' => 'text',
            'dbmapping' => 'message'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}')
    );

    public $messageType;
    public $message;
    public $createdAt;
}
