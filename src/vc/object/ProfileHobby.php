<?php
namespace vc\object;

/*

 */
class ProfileHobby
{
    public static $primaryKey = array('profileid', 'hobbyid');

    public static $fields = array(
        'profileid' => array(
            'type' => 'integer',
            'dbmapping' => 'profileid'),
        'hobbyid' => array(
            'type' => 'integer',
            'dbmapping' => 'hobbyid'),
    );

    public $profileid;
    public $hobbyid;
}
