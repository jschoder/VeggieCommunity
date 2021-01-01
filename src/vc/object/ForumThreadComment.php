<?php
namespace vc\object;

/*

 */
class ForumThreadComment
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
            'length' => 7),
        'threadId' => array(
            'type' => 'integer',
            'dbmapping' => 'thread_id' ),
        'body' => array(
            'type' => 'text',
            'dbmapping' => 'body' ),
        'isOfficial' => array(
            'type' => 'integer',
            'dbmapping' => 'is_official',
            'default' => 0),
        'createdBy' => array(
            'type' => 'integer',
            'dbmapping' => 'created_by',
            'default' => '{{CURRENT_USER}}'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
        'updatedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'updated_at'),
        'deletedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'deleted_by'),
        'deletedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'deleted_at'),
    );

    public $id;
    public $hashId;
    public $threadId;
    public $body;
    public $isOfficial;
    public $createdBy;
    public $createdAt;
    public $updatedAt;
    public $deletedBy;
    public $deletedAt;
}
