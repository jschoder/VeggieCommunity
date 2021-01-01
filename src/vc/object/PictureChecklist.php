<?php
namespace vc\object;

/*

 */
class PictureChecklist
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id')
    );

    public $id;
}
