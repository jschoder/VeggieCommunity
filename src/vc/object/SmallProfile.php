<?php
namespace vc\object;

/*
 * The small profiles are used on pages where performance is relevant AND not every field is
 * required (e.g. start/search)
 */
class SmallProfile
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
        'age' => array(
            'type' => 'integer',
            'dbmapping' => 'age'),
        'hideAge' => array(
            'type' => 'integer',
            'dbmapping' => 'hide_age'),
        'nutrition' => array(
            'type' => 'integer',
            'dbmapping' => 'nutrition'),
        'nutritionFreetext' => array(
            'type' => 'text',
            'dbmapping' => 'nutrition_freetext'),
        'city' => array(
            'type' => 'text',
            'dbmapping' => 'residence'),
        'region' => array(
            'type' => 'text',
            'dbmapping' => 'region'),
        'countryname' => array(
            'type' => 'text',
            'dbmapping' => 'vc_country.name_en',
            'join' => 'INNER JOIN vc_country ON vc_country.id = vc_profile.country'),
        'latitude' => array(
            'type' => 'integer',
            'dbmapping' => 'latitude',
            'default' => 0),
        'longitude' => array(
            'type' => 'integer',
            'dbmapping' => 'longitude',
            'default' => 0),
        'lastUpdate' => array(
            'type' => 'datetime',
            'dbmapping' => 'last_update'),
        'active' => array(
            'type' => 'integer',
            'dbmapping' => 'active'),
        'realMarker' => array(
            'type' => 'integer',
            'dbmapping' => 'real_marker'),
        'plusMarker' => array(
            'type' => 'integer',
            'dbmapping' => 'plus_marker'),
    );

    public $id;
    public $nickname;
    public $gender;
    public $age;
    public $hideAge;

    public $nutrition;
    public $nutritionFreetext;

    public $city;
    public $region;
    public $country;
    public $countryname;
    public $countryIso;

    public $latitude;
    public $longitude;

    public $lastUpdate;
    public $active;
    public $realMarker;
    public $plusMarker;

    public function getHtmlLocation($shortLocation = false, $currentProfile = null)
    {
        $location = '';
        if ($shortLocation && $currentProfile !== null) {
            if (!empty($this->city)) {
                $location .= $this->city;
            }
            if (empty($location) ||
                $currentProfile->region != $this->region ||
                $currentProfile->country != $this->country) {
                if (!empty($this->region)) {
                    if (empty($location)) {
                        $location .= $this->region;
                    } else {
                        $regions = \vc\config\Fields::getRegions();
                        if (array_key_exists($this->country, $regions)) {
                            $regionCode =  array_search($this->region, $regions[$this->country]);
                            if ($regionCode === false) {
                                $regionCode = $this->region;
                            }
                        } else {
                            $regionCode = $this->region;
                        }
                        $location .= ', ' . $regionCode;
                    }
                }

                if (!empty($this->countryname)) {
                    if (empty($location)) {
                        $location .= $this->countryname;
                    } else {
                        $location .= ', ' . $this->countryIso;
                    }
                }
            }
        } else {
            if (!empty($this->city)) {
                $location .= $this->city . ', ';
            }
            if (!empty($this->region)) {
                $location .= $this->region . ', ';
            }
            // Only inactive for really old profiles
            if (!empty($this->countryname)) {
                $location .= $this->countryname;
            }
        }
        return $location;
    }

    public function getToolTipText($includeNutrition = true)
    {
        $text = $this->nickname;
        if ($this->age > 0 && $this->hideAge !== true) {
            $text .= " (" . $this->age . ")";
        }
        if ($includeNutrition && $this->nutrition > 0) {
            $text = $text . ', ' .
                    \vc\config\Fields::getNutritionCaption(
                        $this->nutrition,
                        $this->nutritionFreetext,
                        $this->gender
                    );
        }
        $text .= ', ' . $this->getHtmlLocation();
        if ($this->realMarker && $this->plusMarker) {
            $text .= ' (' . gettext('profile.real') . ' + ' . gettext('profile.plusMember') . ')';
        } elseif ($this->realMarker) {
            $text .= ' (' . gettext('profile.real') . ')';
        } elseif ($this->plusMarker) {
            $text .= ' (' . gettext('profile.plusMember') . ')';
        }
        return $text;
    }
}
