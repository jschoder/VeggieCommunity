<?php
namespace vc\object;

class Faq
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'locale' => array(
            'type' => 'text',
            'dbmapping' => 'locale' ),
        'question' => array(
            'type' => 'text',
            'dbmapping' => 'question' ),
        'answer' => array(
            'type' => 'text',
            'dbmapping' => 'answer' ),
    );

    public $id;
    public $locale;
    public $question;
    public $answer;
}
