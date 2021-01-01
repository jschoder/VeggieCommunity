<?php
namespace vc\object;

class SavedPicture extends Picture
{
    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_REGISTERED = 2;
    const VISIBILITY_FRIENDS_FAVORITES = 3;
    const VISIBILITY_FRIENDS = 4;

    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'profileid' => array(
            'type' => 'integer',
            'dbmapping' => 'profileid'),
        'filename' => array(
            'type' => 'text',
            'dbmapping' => 'filename'),
        'description' => array(
            'type' => 'text',
            'dbmapping' => 'description',
            'default' => ''),
        'visibility' => array(
            'type' => 'integer',
            'dbmapping' => 'visibility',
            'default' => 1),
        'weight' => array(
            'type' => 'integer',
            'dbmapping' => 'weight',
            'default' => 0),
        'defaultpic' => array(
            'type' => 'integer',
            'dbmapping' => 'defaultpic',
            'default' => 0),
        'width' => array(
            'type' => 'integer',
            'dbmapping' => 'width'),
        'height' => array(
            'type' => 'integer',
            'dbmapping' => 'height'),
        'smallwidth' => array(
            'type' => 'integer',
            'dbmapping' => 'smallwidth'),
        'smallheight' => array(
            'type' => 'integer',
            'dbmapping' => 'smallheight'),
        'creation' => array(
            'type' => 'datetime',
            'dbmapping' => 'creation',
            'default' => '{{CURRENT_TIME}}')
    );

    public $id;
    public $profileid;
    public $filename;
    public $description;
    public $visibility;
    public $weight;
    public $defaultpic;
    public $width;
    public $height;
    public $smallwidth;
    public $smallheight;
    public $creation;

    public function getID()
    {
        return $this->id;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function isDefaultPic()
    {
        return $this->defaultpic;
    }
}
