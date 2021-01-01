<?php
namespace vc\object;

/*

 */
class GroupInvitation
{
    public static $primaryKey = array('groupId', 'profileId', 'createdBy');

    public static $fields = array(
        'groupId' => array(
            'type' => 'integer',
            'dbmapping' => 'group_id' ),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id' ),
        'comment' => array(
            'type' => 'text',
            'dbmapping' => 'comment'),
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
    );

    public $groupId;
    public $profileId;
    public $comment;
    public $createdBy;
    public $createdAt;
    public $updatedAt;
}
