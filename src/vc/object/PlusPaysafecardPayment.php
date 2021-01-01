<?php
namespace vc\object;

/*

 */
class PlusPaysafecardPayment
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id'),
        'paysafecardId' => array(
            'type' => 'integer',
            'dbmapping' => 'paysafecard_id'),
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
    public $paysafecardId;
    public $createdBy;
    public $createdAt;
}
