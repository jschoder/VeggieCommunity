<?php
namespace vc\object;

/*

 */
class Flag
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'hashId' => array(
            'type' => 'text',
            'dbmapping' => 'hash_id',
            'default' => '{{UNIQUE_TOKEN}}',
            'length' => 7 ),
        'entityType' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_type' ),
        'entityId' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_id' ),
        'aggregateType' => array(
            'type' => 'integer',
            'dbmapping' => 'aggregate_type' ),
        'aggregateId' => array(
            'type' => 'integer',
            'dbmapping' => 'aggregate_id' ),
        'comment' => array(
            'type' => 'text',
            'dbmapping' => 'comment' ),
        'flaggedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'flagged_by',
            'default' => '{{CURRENT_USER}}'),
        'flaggedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'flagged_at',
            'default' => '{{CURRENT_TIME}}'),
        'processedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'processed_by'),
        'processedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'processed_at'),
    );

    public $id;
    public $hashId;
    public $entityType;
    public $entityId;
    public $aggregateType;
    public $aggregateId;
    public $comment;
    public $flaggedBy;
    public $flaggedAt;
    public $processedBy;
    public $processedAt;
}
