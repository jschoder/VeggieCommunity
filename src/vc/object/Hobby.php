<?php
namespace vc\object;

/*

 */
class Hobby
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'groupId' => array(
            'type' => 'integer',
            'dbmapping' => 'groupid'),
        'nameDe' => array(
            'type' => 'text',
            'dbmapping' => 'name_de' ),
        'descriptionDe' => array(
            'type' => 'text',
            'dbmapping' => 'description_de'),
        'nameEn' => array(
            'type' => 'text',
            'dbmapping' => 'name_en' ),
        'descriptionEn' => array(
            'type' => 'text',
            'dbmapping' => 'description_en'),
    );

    public $id;
    public $groupId;
    public $nameDe;
    public $descriptionDe;
    public $nameEn;
    public $descriptionEn;
}
