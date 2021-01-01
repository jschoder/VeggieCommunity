<?php
namespace vc\object;

/*

 */
class WebsocketMessage
{
    public static $primaryKey = array('contextType', 'contextId');

    public static $fields = array(
        'contextType' => array(
            'type' => 'integer',
            'dbmapping' => 'context_type'),
        'contextId' => array(
            'type' => 'integer',
            'dbmapping' => 'context_id')
    );

    public $contextType;
    public $contextId;
}
