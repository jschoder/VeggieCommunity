<?php
namespace vc\object;

/*

 */
class ModMessage
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'userId' => array(
            'type' => 'integer',
            'dbmapping' => 'user_id' ),
        'ip' => array(
            'type' => 'text',
            'dbmapping' => 'ip' ),
        'message' => array(
            'type' => 'text',
            'dbmapping' => 'message' ),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
    );

    public $id;
    public $userId;
    public $ip;
    public $message;
    public $createdAt;
}
