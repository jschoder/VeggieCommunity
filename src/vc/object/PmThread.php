<?php
namespace vc\object;

class PmThread
{
    // Used in PmThreadDbModel to mark threads, where the current user has received, sent or both.
    const STATUS_BIDIRECTIONAL = 1;
    const STATUS_RECEIVED = 2;
    const STATUS_SENT = 3;

    public static $primaryKey = array('minUserId', 'maxUserId');

    public static $fields = array(
        'minUserId' => array(
            'type' => 'integer',
            'dbmapping' => 'min_user_id'),
        'maxUserId' => array(
            'type' => 'integer',
            'dbmapping' => 'max_user_id'),
        'minUserLastPmId' => array(
            'type' => 'integer',
            'dbmapping' => 'min_user_last_pm_id'),
        'minUserLastUpdate' => array(
            'type' => 'date',
            'dbmapping' => 'min_user_last_update'),
        'minUserIsNew' => array(
            'type' => 'integer',
            'dbmapping' => 'min_user_is_new'),
        'maxUserLastPmId' => array(
            'type' => 'integer',
            'dbmapping' => 'max_user_last_pm_id'),
        'maxUserLastUpdate' => array(
            'type' => 'date',
            'dbmapping' => 'max_user_last_update'),
        'maxUserIsNew' => array(
            'type' => 'integer',
            'dbmapping' => 'max_user_is_new'),
    );

    public $minUserId;
    public $maxUserId;
    public $minUserLastPmId;
    public $minUserLastUpdate;
    public $minUserIsNew;
    public $maxUserLastPmId;
    public $maxUserLastUpdate;
    public $maxUserIsNew;
}
