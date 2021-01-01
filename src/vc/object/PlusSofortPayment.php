<?php
namespace vc\object;

/*

 */
class PlusSofortPayment
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id'),
        'sofortId' => array(
            'type' => 'integer',
            'dbmapping' => 'sofort_id'),
        'createdBy' => array(
            'type' => 'integer',
            'dbmapping' => 'created_by',
            'default' => '{{CURRENT_USER}}'),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}')
    );

    public $id;
    public $sofortId;
    public $createdBy;
    public $createdAt;
}
