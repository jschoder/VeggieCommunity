<?php
namespace vc\object;

/*

 */
class BannedPicture
{
    public static $primaryKey = array();

    public static $fields = array(
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'filehash' => array(
            'type' => 'text',
            'dbmapping' => 'filehash' ),
    );

    public $profileId;
    public $filehash;
}
