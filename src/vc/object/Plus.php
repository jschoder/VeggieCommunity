<?php
namespace vc\object;

/*

 */
class Plus
{
    const PLUS_TYPE_MINI = 1;
    const PLUS_TYPE_STANDARD = 2;
    const PLUS_TYPE_COMMERCIAL_USE = 3;

    public static $packages = array(
        self::PLUS_TYPE_MINI => array('xs', 1.0),
        self::PLUS_TYPE_STANDARD => array('m', 2.5),
//        self::PLUS_TYPE_COMMERCIAL_USE => array('xl', 10)
    );

    const PAYMENT_TYPE_TRIAL = 1;
    const PAYMENT_TYPE_GIFT = 2;
    // Restvalue happens if a user pays for plus but for a different type
    const PAYMENT_TYPE_RESTVALUE = 5;
    const PAYMENT_TYPE_PAYPAL = 10;
    const PAYMENT_TYPE_PAYSAFECARD = 11;
    const PAYMENT_TYPE_SOFORT = 12;

//    const RECURRING_INTERVAL_MONTHLY = 1;
//    const RECURRING_INTERVAL_BIMONTHLY = 2;
//    const RECURRING_INTERVAL_QUARTERLY = 3;
//    const RECURRING_INTERVAL_YEARLY = 4;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id'),
        'userId' => array(
            'type' => 'integer',
            'dbmapping' => 'user_id'),
        'plusType' => array(
            'type' => 'integer',
            'dbmapping' => 'plus_type'),
        'startDate' => array(
            'type' => 'datetime',
            'dbmapping' => 'start_date'),
        'endDate' => array(
            'type' => 'datetime',
            'dbmapping' => 'end_date'),
//        'recurring' => array(
//            'type' => 'integer',
//            'dbmapping' => 'recurring'),
        'paymentType' => array(
            'type' => 'integer',
            'dbmapping' => 'payment_type'),
        'paymentId' => array(
            'type' => 'integer',
            'dbmapping' => 'payment_id')
    );

    public $id;
    public $userId;
    public $plusType;
    public $startDate;
    public $endDate;
//    public $recurring;
    public $paymentType;
    public $paymentId;
}
