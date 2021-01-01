<?php

namespace vc\object;

/*
 */
class ActivityReport
{
    const HISTORY_PREVENTION = '120 MINUTE';

    const TYPE_CUSTOM_MESSAGE = 1;
    const TYPE_PROFILE_UPDATED = 2;
    const TYPE_PICTURE_ADDED = 3;
    const TYPE_FRIEND_ADDED = 4;

    public $id;
    public $profileid;
    public $activityType;
    public $created;
    public $message;
    public $relatedProfileid;

    //----------------------------------------------------------------------------------------------

    public function __construct($id, $profileid, $activityType, $created, $message, $relatedProfileid)
    {
        $this->id = $id;
        $this->profileid = $profileid;
        $this->activityType = $activityType;
        $this->created = $created;
        $this->message = $message;
        $this->relatedProfileid = $relatedProfileid;
    }
}
