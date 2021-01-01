<?php
namespace vc\object;

/*

 */
class WebsocketUser
{
    public static $primaryKey = array();

    public static $fields = array(
        'userId' => array(
            'type' => 'integer',
            'dbmapping' => 'user_id'),
        'websocketKey' => array(
            'type' => 'text',
            'dbmapping' => 'websocket_key'),
        'createdAt' => array(
            'type' => 'integer',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}')
    );

    public $userId;
    public $key;
    public $createdAt;
}
