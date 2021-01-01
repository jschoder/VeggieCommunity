<?php
namespace vc\view;

class FieldHelper
{
    private static $instance;

    private $fields;

    public function __construct()
    {
        $this->fields = new \stdClass();
        $this->fields->gender = \vc\config\Fields::getGenderFields();
        $this->fields->search = \vc\config\Fields::getSearchFields();
        $this->fields->zodiac = \vc\config\Fields::getZodiacFields();
        $this->fields->nutrition = array();
        $this->fields->nutrition[2] = \vc\config\Fields::getNutritionFields(2);
        $this->fields->nutrition[4] = \vc\config\Fields::getNutritionFields(4);
        $this->fields->nutrition[6] = \vc\config\Fields::getNutritionFields(6);
        $this->fields->nutrition[8] = \vc\config\Fields::getNutritionFields(8);
        $this->fields->religion = \vc\config\Fields::getReligionFields();
        $this->fields->children = \vc\config\Fields::getChildrenFields();
        $this->fields->smoking = \vc\config\Fields::getSmokingFields();
        $this->fields->alcohol = \vc\config\Fields::getAlcoholFields();
        $this->fields->political = \vc\config\Fields::getPoliticalFields();
        $this->fields->marital = \vc\config\Fields::getMaritalFields();
        $this->fields->bodyheight = \vc\config\Fields::getBodyHeightFields();
        $this->fields->bodytype = \vc\config\Fields::getBodyTypeFields();
        $this->fields->clothing = \vc\config\Fields::getClothingFields();
        $this->fields->haircolor = \vc\config\Fields::getHairColorFields();
        $this->fields->eyecolor = \vc\config\Fields::getEyeColorFields();
        $this->fields->relocate = \vc\config\Fields::getRelocateFields();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new FieldHelper();
        }
        return self::$instance;
    }

    public function setSmallProfileOutput(\vc\object\SmallProfile &$profile)
    {
        $profile->output = new \stdClass();
        if (array_key_exists($profile->gender, $this->fields->gender)) {
            $profile->output->gender = $this->fields->gender[$profile->gender];
        } else {
            $profile->output->gender = null;
        }
        if (array_key_exists($profile->nutrition, $this->fields->nutrition)) {
            $profile->output->nutrition = $this->fields->nutrition[$profile->gender][$profile->nutrition];
        } else {
            $profile->output->nutrition = null;
        }
    }

    public function setProfileOutput(\vc\object\Profile &$profile)
    {
        $this->setSmallProfileOutput($profile);
        if (array_key_exists($profile->zodiac, $this->fields->zodiac)) {
            $profile->output->zodiac = $this->fields->zodiac[$profile->zodiac];
        } else {
            $profile->output->zodiac = null;
        }
        $profile->output->search = array();
        foreach ($profile->search as $search) {
            if (array_key_exists($search, $this->fields->search)) {
                $profile->output->search[] = $this->fields->search[$search];
            }
        }
        if (array_key_exists($profile->smoking, $this->fields->smoking)) {
            $profile->output->smoking = $this->fields->smoking[$profile->smoking];
        } else {
            $profile->output->smoking = '';
        }
        if (array_key_exists($profile->alcohol, $this->fields->alcohol)) {
            $profile->output->alcohol = $this->fields->alcohol[$profile->alcohol];
        } else {
            $profile->output->alcohol = '';
        }
        if (array_key_exists($profile->religion, $this->fields->religion)) {
            $profile->output->religion = $this->fields->religion[$profile->religion];
        } else {
            $profile->output->religion = '';
        }
        if (array_key_exists($profile->children, $this->fields->children)) {
            $profile->output->children = $this->fields->children[$profile->children];
        } else {
            $profile->output->children = '';
        }
        $profile->output->political = array();
        foreach ($profile->political as $political) {
            if (array_key_exists($political, $this->fields->political)) {
                $profile->output->political[] = $this->fields->political[$political];
            }
        }
        if (array_key_exists($profile->marital, $this->fields->marital)) {
            $profile->output->marital = $this->fields->marital[$profile->marital];
        } else {
            $profile->output->marital = '';
        }
        if (array_key_exists($profile->bodyheight, $this->fields->bodyheight)) {
            $profile->output->bodyheight = $this->fields->bodyheight[$profile->bodyheight];
        } else {
            $profile->output->bodyheight = '';
        }
        if (array_key_exists($profile->bodytype, $this->fields->bodytype)) {
            $profile->output->bodytype = $this->fields->bodytype[$profile->bodytype];
        } else {
            $profile->output->bodytype = '';
        }
        if (array_key_exists($profile->clothing, $this->fields->clothing)) {
            $profile->output->clothing = $this->fields->clothing[$profile->clothing];
        } else {
            $profile->output->clothing = '';
        }
        if (array_key_exists($profile->haircolor, $this->fields->haircolor)) {
            $profile->output->haircolor = $this->fields->haircolor[$profile->haircolor];
        } else {
            $profile->output->haircolor = '';
        }
        if (array_key_exists($profile->eyecolor, $this->fields->eyecolor)) {
            $profile->output->eyecolor = $this->fields->eyecolor[$profile->eyecolor];
        } else {
            $profile->output->eyecolor = '';
        }
        if (array_key_exists($profile->relocate, $this->fields->relocate)) {
            $profile->output->relocate = $this->fields->relocate[$profile->relocate];
        } else {
            $profile->output->relocate = '';
        }
    }
}
