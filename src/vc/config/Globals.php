<?php
namespace vc\config;

class Globals
{
    const VERSION = '3.21.0';

    const COPYRIGHT_YEAR = 2018;

    // 12 years before current year
    const MINIMUM_BIRTH_YEAR = 2005;

    const QUERY_DEBUG = FALSE;

    // Profiles

    const MAX_PICTURES_DEFAULT = 8;

    const MAX_PICTURES_PLUS = 32;

    const MAX_PICTURE_WIDTH = 1280;

    const MAX_PICTURE_HEIGHT = 800;

    const MAX_UPLOAD_FILE_SIZE = 6291457; // 6 MB (+ 1 Byte)

    // MySite

    const LAST_VISITORS_XS = 12;

    const LAST_VISITORS_XL = 84;

    // Notification

    // Show even seen notifications if the seenAt is less than X hours ago
    const NOTIFICATION_TIME_FRAME = 3;

    // Events

    // Counts as the same day if the end hour is below or equal to this value
    // e.g. 2015-12-31 18:00:00 - 2016-01-01 06:00:00
    const SAME_DAY_HOUR = 6;

    // Groups
    // Has to be dividable by 1 (duh!), 2 and 3 to fit different columns
    const GROUP_ITEMS_SEARCH = 24;

    const MAX_FORUMS_PER_GROUP = 3;

    // Forum
    const THREADS_PER_PAGE = 20;

    const DEFAULT_THREAD_COMMENT_COUNT = 5;

    const RELOAD_THREAD_COMMENT_COUNT = 20;

    // PM

    const MESSAGE_SPAM_LIMIT = 10;

    const THREAD_COUNT_INITIAL = 30;

    const THREAD_COUNT_LOAD = 50;

    const MESSAGE_COUNT_INITIAL = 20;

    const MESSAGE_COUNT_LOAD = 50;

    // Days since the last login of the user to remind him/her of unread messages
    const UNREAD_MESSAGE_REMINDER_LOGIN_DAYS = 14;

    // Minimum age of an unread message to lead to a reminder
    const UNREAD_MESSAGE_REMINDER_MESSAGE_AGE = 7;

    // Interval by which the messages are send (e..g. every 7 days)
    const UNREAD_MESSAGE_REMINDER_INTERVAL = 7;

    // Suspicion

    const SUSPICION_MOD_WARN_LEVEL = 100;

    const SUSPICION_BLOCK_LEVEL = 150;

    const SUSPICION_PAST = 86400;

    const TOKEN_EXPIRE_HOURS = 72;

    public static $db = array(
        'local' => array(
            'db' => '',
            'host' => '',
            'user' => '',
            'password' => ''
        ),
        'live' => array(
            'db' => '',
            'host' => '',
            'user' => '',
            'password' => ''
        ),
        'test' => array(
            'db' => '',
            'host' => '',
            'user' => '',
            'password' => ''
        )
    );

    public static $defaultCountries = array(
        'de' => array(49, 43, 41),
        'en' => array(1, 2, 44)
    );

    public static $blockedCountries = array(
        'SN', // Senegal
        'CI', // Ivory Coast
        'GH', // Ghana
        'TG', // Togo
        'UA' // Ukraine
    );

    public static $blockedCountryLoginWhitelist = array('');

    public static $blockedCountryUserWhitelist = array();

    public static $mail = array(
        // SystemMessage::MAIL_CONFIG_NOTIFY
        1 => array(
            'local' => array(
                'hostname' => '',
                'from' => '',
                'host' => '',
                'username' => '',
                'password' => ''
            ),
            'live' => array(
                'hostname' => null,
                'from' => '',
                'host' => '',
                'username' => '',
                'password' => ''
        ),
        // SystemMessage::MAIL_CONFIG_SUPPORT
        2 => array(
            'local' => array(
                'hostname' => '',
                'from' => '',
                'host' => '',
                'username' => '',
                'password' => ''
            ),
            'live' => array(
                'hostname' => null,
                'from' => '',
                'host' => '',
                'username' => '',
                'password' => ''
            )
        )
    );

    public static $apps = array(
        'local' => array(
            'facebook' => array(
                'appid' => '',
                'secret' => ''
            ),
            'paypal' => array(
                'active' => true,
                'sandbox' => true,
                'clientId' => '',
                'secret' => ''
            ),
            'paysafecard' => array(
                'active' => true,
            ),
            'sofort' => array(
                'active' => true,
            )

        ),
        'live' => array(
            'facebook' => array(
                'appid' => '',
                'secret' => ''
            ),
            'paypal' => array(
                'active' => true,
                'sandbox' => false,
                'clientId' => '',
                'secret' => ''
            ),
            'paysafecard' => array(
                'active' => true,
            ),
            'sofort' => array(
                'active' => true,
            )
        ),
    );

    public static $jsFiles = array(
        'precompressed' => array(
            'jquery/jquery-1.11.3.min.js',
            'jquery/jquery-1-ui.min.js',
            'jquery/jquery-2-ui-touch-punch.js',
            'lib/enscroll.min.js',
            'lib/jquery.minicolors.min.js',
            'lib/mustache.min.js',
            'fancybox/jquery.fancybox.pack.js'
        ),
        'uncompressed' => array(
            'errorhandler.js',
            'lib/anchor.js',
            'lib/clockpicker.js',
            'xhr.js',
            'default.js',
            'localStorage.js',
            'friends.js',
            'favorites.js',
            'ui.js',
            'fileupload.js',
            'websocket.js',
            'pm.js',
            'forum.js',
            'groups.js',
            'events.js',
            'timeago.js',
            'facebook.js'
        )
    );

    public static $filterKeywords = array(
        // Using the name sx instead of sex in order to prevent blocking from spam-filters
        'sx' => [
            'er[r]?ogen[e]?',
            'feucht',
            'fick[e]?[n]?',
            'geiles stück',
            'kitzler',
            'meinen schwanz',
            'nude',
            'orgasmus',
            'sex',
            'ständer',
            'titten'
        ],
        'insult' => [
            'arschloch',
            'asshole',
            'bi[a]?tch',
            'cuck',
            'fuck you',
            'hure',
            'nutte',
            'schwuchtel',
            'schlampe',
            'verpiss dich',
            '(v|f)otze',
            'wichser',
            'wixer'
        ]
    );

    public static $websocket = array(
        'local' => array(
            'host' => 'www.veggiecommunity.dev',
            'port' => 1414,
            'active' => true
        ),
        'live' => array(
            'host' => '',
            'port' => 1414,
            'active' => false
        )
    );
}
