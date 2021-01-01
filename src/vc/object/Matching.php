<?php
namespace vc\object;

/*

 */
class Matching
{
    public static $primaryKey = array('userId');

    public static $fields = array(
        'userId' => array(
            'type' => 'integer',
            'dbmapping' => 'user_id'),

        'adventure' => array(
            'type' => 'integer',
            'dbmapping' => 'adventure'),
        'bedDs' => array(
            'type' => 'integer',
            'dbmapping' => 'bed_ds'),
        'calm' => array(
            'type' => 'integer',
            'dbmapping' => 'calm'),
        'conflict' => array(
            'type' => 'integer',
            'dbmapping' => 'conflict'),
        'couch' => array(
            'type' => 'integer',
            'dbmapping' => 'couch'),
        'driven' => array(
            'type' => 'integer',
            'dbmapping' => 'driven'),
        'extroverted' => array(
            'type' => 'integer',
            'dbmapping' => 'extroverted'),
        'individuality' => array(
            'type' => 'integer',
            'dbmapping' => 'individuality'),
        'logic' => array(
            'type' => 'integer',
            'dbmapping' => 'logic'),
        'messy' => array(
            'type' => 'integer',
            'dbmapping' => 'messy'),
        'mood' => array(
            'type' => 'integer',
            'dbmapping' => 'mood'),
        'optimistic' => array(
            'type' => 'integer',
            'dbmapping' => 'optimistic'),
        'otherDs' => array(
            'type' => 'integer',
            'dbmapping' => 'other_ds'),
        'poly' => array(
            'type' => 'integer',
            'dbmapping' => 'poly'),
        'proactive' => array(
            'type' => 'integer',
            'dbmapping' => 'proactive'),
        'stayhome' => array(
            'type' => 'integer',
            'dbmapping' => 'stayhome'),
        'weird' => array(
            'type' => 'integer',
            'dbmapping' => 'weird'),

        'fitness' => array(
            'type' => 'integer',
            'dbmapping' => 'fitness'),
        'money' => array(
            'type' => 'integer',
            'dbmapping' => 'money'),
        'myLooks' => array(
            'type' => 'integer',
            'dbmapping' => 'my_looks'),
        'theirLooks' => array(
            'type' => 'integer',
            'dbmapping' => 'their_looks'),

        'updatedAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'updated_at',
            'default' => '{{CURRENT_TIME}}')
    );

    public $userId;

    public $adventure;
    public $bedDs;
    public $calm;
    public $conflict;
    public $couch;
    public $driven;
    public $extroverted;
    public $individuality;
    public $logic;
    public $messy;
    public $mood;
    public $optimistic;
    public $otherDs;
    public $poly;
    public $proactive;
    public $stayhome;
    public $weird;

    public $fitness;
    public $money;
    public $myLooks;
    public $theirLooks;

    public $updatedAt;
}
