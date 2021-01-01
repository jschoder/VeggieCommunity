<?php
namespace vc\object;

/*

 */
class GroupMember
{
    public static $primaryKey = array('groupId', 'profileId');

    public static $fields = array(
        'groupId' => array(
            'type' => 'integer',
            'dbmapping' => 'group_id'),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id',
            'default' => '{{CURRENT_USER}}'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
        'confirmedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'confirmed_by'),
        'confirmedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'confirmed_at'),
    );

    public $groupId;
    public $profileId;
    public $createdAt;
    public $confirmedBy;
    public $confirmedAt;
}
