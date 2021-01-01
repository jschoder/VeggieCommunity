<?php
namespace vc\object;

class FullLog
{
    const GET = 1;
    const POST = 2;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'ip' => array(
            'type' => 'text',
            'dbmapping' => 'ip'),
        'url' => array(
            'type' => 'text',
            'dbmapping' => 'url'),
        'parameters' => array(
            'type' => 'text',
            'dbmapping' => 'parameters'),
        'method' => array(
            'type' => 'integer',
            'dbmapping' => 'method'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}')
    );

    public $id;
    public $profileId;
    public $ip;
    public $url;
    public $parameters;
    public $method;
    public $createdAt;
}
