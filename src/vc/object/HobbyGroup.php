<?php
namespace vc\object;

/*

 */
class HobbyGroup
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'nameDe' => array(
            'type' => 'text',
            'dbmapping' => 'name_de'),
        'nameEn' => array(
            'type' => 'text',
            'dbmapping' => 'name_en' ),
    );

    public $id;
    public $nameDe;
    public $nameEn;
}
