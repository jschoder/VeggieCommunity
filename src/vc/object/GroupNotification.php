<?php
namespace vc\object;

/*

 */
class GroupNotification
{
    const TYPE_GROUP_MEMBER_REQUESTS = 11;
    const TYPE_GROUP_INVITATION = 12;
    const TYPE_GROUP_MEMBER_CONFIRMED = 13;

    const TYPE_FORUM_NEW_THREAD = 20;
    const TYPE_FORUM_NEW_COMMENT = 21;

    const TYPE_GROUP_CREATION_ACCEPTED = 100;

    public static $primaryKey = array('profileId', 'notificationType', 'entityId');

    public static $fields = array(
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'notificationType' => array(
            'type' => 'integer',
            'dbmapping' => 'notification_type'),
        'entityId' => array(
            'type' => 'integer',
            'dbmapping' => 'entity_id'),
        'userId' => array(
            'type' => 'integer',
            'dbmapping' => 'user_id'),
        'seenAt' => array(
            'type' => 'date',
            'dbmapping' => 'seen_at'),
        'lastUpdate' => array(
            'type' => 'date',
            'dbmapping' => 'last_update'),
    );

    public $profileId;
    public $notificationType;
    public $entityId;
    public $userId;
    public $seenAt;
    public $lastUpdate;
}
