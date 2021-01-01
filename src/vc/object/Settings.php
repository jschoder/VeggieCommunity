<?php
namespace vc\object;

class Settings
{
    const DESIGN = 1;

    const NEW_MAIL_NOTIFICATION = 12;
    const NEW_FRIEND_NOTIFICATION = 13;
    const FRIEND_CHANGED_NOTIFICATION = 15;
    const GROUP_MEMBER_NOTIFICATION = 16;
    const USER_LANGUAGE = 31;

    const SAVEDSEARCH_DISPLAY = 32;
        const SAVEDSEARCH_DISPLAY_INFOBOX = 1;
        const SAVEDSEARCH_DISPLAY_PICTURE = 2;
        const SAVEDSEARCH_DISPLAY_TEXT = 3;
    const SAVEDSEARCH_COUNT = 33;

    const DISTANCE_UNIT = 27;
        // Possible values for distanceUnit
        const DISTANCE_UNIT_KILOMETER = 1;
        const DISTANCE_UNIT_MILE = 2;
    const PROFILE_WATERMARK = 45;
    const ROTATE_PICS = 44;
    const SEARCHENGINE = 35;
    const VISIBLE_ONLINE = 30;
    const VISIBLE_LAST_VISITOR = 28;
    const PLUS_MARKER = 34;
//    const NEWSLETTER = 29;
//    const AUTO_EXPAND_PROFILES = 32;
    const AGE_RANGE_FILTER = 46;
    const PM_FILTER_INCOMING = 42;
    const PM_FILTER_OUTGOING = 43;
    const TRACKING = 47;

    const PRESS_INTERVIEW_PARTNER = 40;
    const BETA_USER = 41;


    public $profileid;
    public $values = array();

    // :TODO: migratior2 - default values
    public function hasValue($field)
    {
        return array_key_exists($field, $this->values);
    }

    public function getValue($field, $default = null)
    {
        if (array_key_exists($field, $this->values)) {
            return $this->values[$field];
        } else {
            return $default;
        }
    }
}
