<?php
namespace vc\object;

class Profile extends SmallProfile
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'nickname' => array(
            'type' => 'text',
            'dbmapping' => 'nickname'),
        'gender' => array(
            'type' => 'integer',
            'dbmapping' => 'gender'),
        'birth' => array(
            'type' => 'date',
            'dbmapping' => 'birth',
            'update' => false),
        'age' => array(
            'type' => 'integer',
            'dbmapping' => 'age',
            'update' => false),
        'hideAge' => array(
            'type' => 'integer',
            'dbmapping' => 'hide_age',
            'default' => 0),
        'ageFromFriends' => array(
            'type' => 'integer',
            'dbmapping' => 'age_from_friends',
            'default' => 8),
        'ageToFriends' => array(
            'type' => 'integer',
            'dbmapping' => 'age_to_friends',
            'default' => 120),
        'ageFromRomantic' => array(
            'type' => 'integer',
            'dbmapping' => 'age_from_romantic',
            'default' => 18),
        'ageToRomantic' => array(
            'type' => 'integer',
            'dbmapping' => 'age_to_romantic',
            'default' => 120),
        'zodiac' => array(
            'type' => 'integer',
            'dbmapping' => 'zodiac',
            'default' => 0),
        'email' => array(
            'type' => 'text',
            'dbmapping' => 'email' ),
        'password' => array(
            'type' => 'password',
            'dbmapping' => 'password',
            'update' => false),
        'salt' => array(
            'type' => 'password',
            'dbmapping' => 'salt',
            'update' => false),
        'postalcode' => array(
            'type' => 'text',
            'dbmapping' => 'postalcode'),
        'city' => array(
            'type' => 'text',
            'dbmapping' => 'residence'),
        'region' => array(
            'type' => 'text',
            'dbmapping' => 'region'),
        'country' => array(
            'type' => 'integer',
            'dbmapping' => 'country'),
        'latitude' => array(
            'type' => 'double',
            'dbmapping' => 'latitude',
            'default' => 0),
        'longitude' => array(
            'type' => 'double',
            'dbmapping' => 'longitude',
            'default' => 0),
        'sinLatitude' => array(
            'type' => 'double',
            'dbmapping' => 'sin_latitude',
            'default' => 0),
        'cosLatitude' => array(
            'type' => 'double',
            'dbmapping' => 'cos_latitude',
            'default' => 0),
        'longitudeRadius' => array(
            'type' => 'double',
            'dbmapping' => 'longitude_radius',
            'default' => 0),
        'search' => array(
            'type' => 'integer',
            'dbmapping' => 'search'),
        'nutrition' => array(
            'type' => 'integer',
            'dbmapping' => 'nutrition'),
        'nutritionFreetext' => array(
            'type' => 'text',
            'dbmapping' => 'nutrition_freetext',
            'default' => ''),
        'smoking' => array(
            'type' => 'integer',
            'dbmapping' => 'smoking',
            'default' => 0),
        'alcohol' => array(
            'type' => 'integer',
            'dbmapping' => 'alcohol',
            'default' => 0),
        'religion' => array(
            'type' => 'integer',
            'dbmapping' => 'religion',
            'default' => 0),
        'children' => array(
            'type' => 'integer',
            'dbmapping' => 'children',
            'default' => 0),
        'political' => array(
            'type' => 'integer',
            'dbmapping' => 'political',
            'default' => 0),
        'marital' => array(
            'type' => 'integer',
            'dbmapping' => 'marital',
            'default' => 0),
        'bodyheight' => array(
            'type' => 'integer',
            'dbmapping' => 'bodyheight',
            'default' => 0),
        'bodytype' => array(
            'type' => 'integer',
            'dbmapping' => 'bodytype',
            'default' => 0),
        'clothing' => array(
            'type' => 'integer',
            'dbmapping' => 'clothing',
            'default' => 0),
        'haircolor' => array(
            'type' => 'integer',
            'dbmapping' => 'haircolor',
            'default' => 0),
        'eyecolor' => array(
            'type' => 'integer',
            'dbmapping' => 'eyecolor',
            'default' => 0),
        'relocate' => array(
            'type' => 'integer',
            'dbmapping' => 'relocate',
            'default' => 0),
        'word1' => array(
            'type' => 'text',
            'dbmapping' => 'word1'),
        'word2' => array(
            'type' => 'text',
            'dbmapping' => 'word2'),
        'word3' => array(
            'type' => 'text',
            'dbmapping' => 'word3'),

        'homepage' => array(
            'type' => 'text',
            'dbmapping' => 'homepage'),
        'favlink1' => array(
            'type' => 'text',
            'dbmapping' => 'favlink1'),
        'favlink2' => array(
            'type' => 'text',
            'dbmapping' => 'favlink2'),
        'favlink3' => array(
            'type' => 'text',
            'dbmapping' => 'favlink3'),

        'tabQuestionaire1Hide' => array(
            'type' => 'integer',
            'dbmapping' => 'tabQuestionaire1Hide',
            'default' => 0),
        'tabQuestionaire2Hide' => array(
            'type' => 'integer',
            'dbmapping' => 'tabQuestionaire2Hide',
            'default' => 0),
        'tabQuestionaire3Hide' => array(
            'type' => 'integer',
            'dbmapping' => 'tabQuestionaire3Hide',
            'default' => 0),
        'tabQuestionaire4Hide' => array(
            'type' => 'integer',
            'dbmapping' => 'tabQuestionaire4Hide',
            'default' => 0),
        'tabQuestionaire5Hide' => array(
            'type' => 'integer',
            'dbmapping' => 'tabQuestionaire5Hide',
            'default' => 0),

        'facebookId' => array(
            'type' => 'integer',
            'dbmapping' => 'facebook_id',
            'update' => false),

        'firstEntry' => array(
            'type' => 'datetime',
            'dbmapping' => 'first_entry'),
        'lastUpdate' => array(
            'type' => 'datetime',
            'dbmapping' => 'last_update'),
        'lastLogin' => array(
            'type' => 'datetime',
            'dbmapping' => 'last_login',
            'update' => false),
        'lastChatLogin' => array(
            'type' => 'datetime',
            'dbmapping' => 'last_chat_login',
            'update' => false),
        'reminderDate' => array(
            'type' => 'datetime',
            'dbmapping' => 'reminder_date'),
        'deleteDate' => array(
            'type' => 'datetime',
            'dbmapping' => 'delete_date'),

        'realMarker' => array(
            'type' => 'integer',
            'dbmapping' => 'real_marker',
            'default' => 0),
        'plusMarker' => array(
            'type' => 'integer',
            'dbmapping' => 'plus_marker',
            'default' => 0),
        'admin' => array(
            'type' => 'integer',
            'dbmapping' => 'admin',
            'default' => 0),
    );



    public $birth;
    public $zodiac;
    public $email;

    public $postalcode;
    public $sinLatitude;
    public $cosLatitude;
    public $longitudeRadius;

    public $search;

    public $ageFromFriends;
    public $ageToFriends;
    public $ageFromRomantic;
    public $ageToRomantic;

    public $smoking;
    public $alcohol;
    public $religion;
    public $children;
    public $political;
    public $marital;
    public $bodyheight;
    public $bodytype;
    public $clothing;
    public $haircolor;
    public $eyecolor;
    public $relocate;

    public $word1;
    public $word2;
    public $word3;

    public $homepage;
    public $favlink1;
    public $favlink2;
    public $favlink3;

    public $tabQuestionaire1Hide;
    public $tabQuestionaire2Hide;
    public $tabQuestionaire3Hide;
    public $tabQuestionaire4Hide;
    public $tabQuestionaire5Hide;

    public $facebookId;

    public $firstEntry;
    public $lastLogin;
    public $lastChatLogin;
    public $reminderDate;
    public $deleteDate;

    public $plusMarker;
    public $admin;
}
