<?php
namespace vc\object;

/*

 */
class Subscription
{
    public static $primaryKey = array('profileId', 'entityType', 'entityId');

    public static $fields = array(
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'entityType' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_type'),
        'entityId' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_id'),
    );

    public $profileId;
    public $entityType;
    public $entityId;
}
