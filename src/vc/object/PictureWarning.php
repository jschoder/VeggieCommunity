<?php
namespace vc\object;

class PictureWarning
{
    public static $primaryKey = array('pictureId');

    public static $fields = array(
        'pictureId' => array(
            'type' => 'integer',
            'dbmapping' => 'picture_id'),
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'ticketId' => array(
            'type' => 'integer',
            'dbmapping' => 'ticket_id'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}'),
        'createdBy' => array(
            'type' => 'integer',
            'dbmapping' => 'created_by',
            'default' => '{{CURRENT_USER}}'),
        'ownPicConfirmedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'own_pic_confirmed_at',
            'default' => null),
        'deletedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'deleted_at',
            'default' => null),
        'closedBy' => array(
            'type' => 'integer',
            'dbmapping' => 'closed_by',
            'default' => null)
    );

    public $pictureId;
    public $profileId;
    public $ticketId;
    public $createdAt;
    public $createdBy;
    public $ownPicConfirmedAt;
    public $deletedAt;
    public $closedBy;
}
