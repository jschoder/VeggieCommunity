<?php
namespace vc\object;

class RealPicture
{
    public static $primaryKey = array('picture_id', 'real_check_id');

    public static $fields = array(
        'pictureId' => array(
            'type' => 'integer',
            'dbmapping' => 'picture_id'),
        'realCheckId' => array(
            'type' => 'integer',
            'dbmapping' => 'real_check_id')
    );

    public $pictureId;
    public $realCheckId;
}
