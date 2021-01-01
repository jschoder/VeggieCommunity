<?php
namespace vc\object;

/*

 */
class GroupBan
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'groupId' => array(
            'type' => 'integer',
            'dbmapping' => 'group_id' ),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id' ),
        'bannedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'banned_by' ),
        'bannedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'banned_at',
            'default' => '{{CURRENT_TIME}}'),
        'reason' => array(
            'type' => 'text',
            'dbmapping' => 'reason'),
    );

    public $id;
    public $groupId;
    public $profileId;
    public $bannedBy;
    public $bannedAt;
    public $reason;
}
