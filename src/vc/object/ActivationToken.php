<?php
namespace vc\object;

/*

 */
class ActivationToken
{
    public static $primaryKey = array('profileId', 'token');

    public static $fields = array(
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id',
            'autoincrement' => true ),
        'token' => array(
            'type' => 'text',
            'dbmapping' => 'token',
            'default' => '{{UNIQUE_TOKEN}}',
            'length' => 7 ),
        'createdAt' => array(
            'type' => 'integer',
            'dbmapping' => 'created_at'),
        'usedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'used_at'),
    );

    public $profileId;
    public $token;
    public $createdAt;
    public $usedAt;
}
