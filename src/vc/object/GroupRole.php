<?php
namespace vc\object;

/*

 */
class GroupRole
{
    const ROLE_MODERATOR = 1;
    const ROLE_ADMIN = 2;

    public static $primaryKey = array('groupId', 'profileId');

    public static $fields = array(
        'groupId' => array(
            'type' => 'integer',
            'dbmapping' => 'group_id'),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'role' => array(
            'type' => 'integer',
            'dbmapping' => 'role'),
        'grantedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'granted_by',
            'default' => '{{CURRENT_USER}}'),
        'grantedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'granted_at',
            'default' => '{{CURRENT_TIME}}'),
    );

    public $groupId;
    public $profileId;
    public $role;
    public $grantedBy;
    public $grantedAt;
}
