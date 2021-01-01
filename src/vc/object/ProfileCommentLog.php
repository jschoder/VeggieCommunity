<?php
namespace vc\object;

class ProfileCommentLog
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id'),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
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
    );

    public $id;
    public $profileId;
    public $comment;
    public $createdBy;
    public $createdAt;
}
