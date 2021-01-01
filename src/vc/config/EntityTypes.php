<?php
namespace vc\config;

class EntityTypes
{
    const PROFILE = 1;
    const PROFILE_PICTURE = 2;

    const GROUP = 10;
    const GROUP_MEMBER_REQUEST = 15;
    const GROUP_INVITATION = 16;
    const GROUP_FORUM = 20;

    const FORUM_THREAD = 21;
    const FORUM_COMMENT = 22;

    const EVENT = 40;

    const PM = 50;

    const FRIEND = 60;

    // Websocket specific variables
    const STATUS = 100;

    const CHAT = 110;
}
