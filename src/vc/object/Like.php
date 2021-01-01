<?php
namespace vc\object;

/*

 */
class Like
{
    public static $primaryKey = array('entityType', 'entityId', 'profileId');

    public static $fields = array(
        'entityType' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_type'),
        'entityId' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_id'),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'upDown' => array(
            'type' => 'integer',
            'dbmapping' => 'up_down'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
    );

    public $entityType;
    public $entityId;
    public $profileId;
    public $upDown;
    public $createdAt;
}
