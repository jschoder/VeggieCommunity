<?php
namespace vc\object;

/*

 */
class Update
{
    const ACTION_ADD = 1;
    const ACTION_EDIT = 2;
    const ACTION_REMOVE = 3;

    public static $primaryKey = array('entityId', 'entityType', 'action', 'contextType', 'contextId');

    public static $fields = array(
        'entityId' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_id' ),
        'entityType' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_type' ),
        'action' => array(
            'type' => 'integer',
            'dbmapping' => 'action' ),
        'contextType' => array(
            'type' => 'integer',
            'dbmapping' => 'context_type' ),
        'contextId' => array(
            'type' => 'integer',
            'dbmapping' => 'context_id' ),
        'lastUpdate' => array(
            'type' => 'datetime',
            'dbmapping' => 'last_update',
            'default' => '{{CURRENT_TIME}}' ),
    );

    public $entityId;
    public $entityType;
    public $action;
    public $contextType;
    public $contextId;
    public $lastUpdate;
}
