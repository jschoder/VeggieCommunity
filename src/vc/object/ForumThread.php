<?php
namespace vc\object;

/*

 */
class ForumThread
{
    const TYPE_NORMAL = 1;
    const TYPE_ACTVITY_PROFILE_UPDATE = 10;
    const TYPE_ACTVITY_PICTURE_ADDED = 11;
    const TYPE_ACTVITY_FRIEND_ADDED = 12;

    const ADDITIONAL_PICTURE_PATH = 10;
    const ADDITIONAL_PICTURE_FILENAME = 11;

    const ADDITIONAL_LINK_PREVIEW_URL = 20;
    const ADDITIONAL_LINK_PREVIEW_PICTURE = 21;
    const ADDITIONAL_LINK_PREVIEW_TITLE = 22;
    const ADDITIONAL_LINK_PREVIEW_DESCRIPTION = 23;
    const ADDITIONAL_LINK_PREVIEW_LOCATION = 24;
    const ADDITIONAL_LINK_PREVIEW_DATE = 25;

    const ADDITIONAL_ACTIVITY_PROFILE_ID = 30;


    public static $manualThreadTypes = array(
        ForumThread::TYPE_NORMAL
    );

    // New profile pictures can be flagged. They might be offensive
    public static $unflaggableThreadTypes = array(
        ForumThread::TYPE_ACTVITY_PROFILE_UPDATE,
        ForumThread::TYPE_ACTVITY_FRIEND_ADDED
    );

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
        'contextType' => array(
            'type' => 'integer',
            'dbmapping' => 'context_type'),
        'contextId' => array(
            'type' => 'integer',
            'dbmapping' => 'context_id'),
        'threadType' =>  array(
            'type' => 'integer',
            'dbmapping' => 'thread_type',
            'default' => 1),
        'subject' => array(
            'type' => 'text',
            'dbmapping' => 'subject'),
        'body' => array(
            'type' => 'text',
            'dbmapping' => 'body'),
        'additional' => array(
            'type' => 'json.array',
            'dbmapping' => 'additional'),
        'isOfficial' => array(
            'type' => 'integer',
            'dbmapping' => 'is_official',
            'default' => 0),
        'isSticky' => array(
            'type' => 'integer',
            'dbmapping' => 'is_sticky',
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
        'contentUpdatedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'content_updated_at',
            'default' => '{{CURRENT_TIME}}'),
    );

    public $id;
    public $hashId;
    public $contextType;
    public $contextId;
    public $threadType;
    public $subject;
    public $body;
    public $additional;
    public $isOfficial;
    public $isSticky;
    public $createdBy;
    public $createdAt;
    public $updatedAt;
    public $deletedBy;
    public $deletedAt;
    public $contentUpdatedAt;
    public $comments = array();

    public function has($additionalKey)
    {
        return !empty($this->additional) &&
               is_array($this->additional) &&
               array_key_exists($additionalKey, $this->additional);
    }
}
