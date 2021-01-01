<?php
namespace vc\object;

/*

 */
class PlusPaypalPayment
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id'),
        'paymentId' => array(
            'type' => 'text',
            'dbmapping' => 'payment_id'),
        'payerId' => array(
            'type' => 'text',
            'dbmapping' => 'payer_id'),
        'paypalCreateTime' => array(
            'type' => 'datetime',
            'dbmapping' => 'paypal_create_time'),
        'paypalUpdateTime' => array(
            'type' => 'datetime',
            'dbmapping' => 'paypal_update_time'),
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
    public $paymentId;
    public $payerId;
    public $paypalCreateTime;
    public $paypalUpdateTime;
    public $createdBy;
    public $createdAt;
}
