<?php
namespace vc\object;

/*

 */
class GroupForum
{
    const CONTENT_VISIBILITY_MEMBER = 1;
    const CONTENT_VISIBILITY_REGISTERED = 2;
    const CONTENT_VISIBILITY_PUBLIC = 3;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'groupId' => array(
            'type' => 'integer',
            'dbmapping' => 'group_id' ),
        'hashId' => array(
            'type' => 'text',
            'dbmapping' => 'hash_id',
            'default' => '{{UNIQUE_TOKEN}}',
            'length' => 7 ),
        'name' => array(
            'type' => 'text',
            'dbmapping' => 'name' ),
        'contentVisibility' => array(
            'type' => 'integer',
            'dbmapping' => 'content_visibility',
            'default' => 2),
        'isMain' => array(
            'type' => 'boolean',
            'dbmapping' => 'is_main',
            'default' => 0),
        'weight' => array(
            'type' => 'integer',
            'dbmapping' => 'weight'),
        'deletedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'deleted_by'),
        'deletedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'deleted_at'),
    );

    public $id;
    public $groupId;
    public $hashId;
    public $name;
    public $contentVisibility;
    public $isMain;
    public $weight;
    public $deletedBy;
    public $deletedAt;
}
