<?php
namespace vc\object;

/*

 */
class BlockedLogin
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'userId' => array(
            'type' => 'integer',
            'dbmapping' => 'user_id' ),
        'blockedTill' => array(
            'type' => 'datetime',
            'dbmapping' => 'blocked_till'),
        'reason' => array(
            'type' => 'text',
            'dbmapping' => 'reason'),
        'blockedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'blocked_by')
    );

    public $userId;
    public $blockedTill;
    public $reason;
    public $blockedBy;
}
