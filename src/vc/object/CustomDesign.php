<?php
namespace vc\object;

class CustomDesign
{
    const COLOR_BACKGROUND = 1;
    
    const COLOR_PRIMARY = 10;
    const COLOR_PRIMARY_HIGHLIGHT = 11;
    const COLOR_PRIMARY_DARK = 12;
    
    const COLOR_SECONDARY = 20;
    const COLOR_COMPLIMENTARY = 21;
    const COLOR_COMPLIMENTARY_DARK = 22;
    const COLOR_MOD = 23;
    
    const COLOR_ITEM_BACKGROUND = 30;
    const COLOR_ITEM_BORDER = 31;
    
    const COLOR_NOTIFY_SUCCESS = 40;
    const COLOR_NOTIFY_WARN = 41;
    const COLOR_NOTIFY_ERROR = 42;
    
    const COLOR_FONT_DEFAULT = 50;
    const COLOR_FONT_NAV = 51;
    const COLOR_FONT_SECONDARY = 52;
    const COLOR_FONT_ERROR = 53;
    const COLOR_FONT_CONTRAST = 54;
    const COLOR_FONT_CONTRAST_DARK = 55;
    
    public static $groups = array(
        'background' => array(
            1,
        ),
        'maincolors' => array(
            10,
            11,
            12,
        ),
        'secondary' => array(
            20,
            21,
            22,
            23,
        ),
        'elements' => array(
            30,
            31,
        ),
        'notifications' => array(
            40,
            41,
            42,
        ),
        'fonts' => array(
            50,
            51,
            52,
            53,
            54,
            55,
        )
    );
    
    public static $keys = array(
        self::COLOR_BACKGROUND => 'background',
    
        self::COLOR_PRIMARY => 'primaryColor',
        self::COLOR_PRIMARY_HIGHLIGHT => 'primaryHighlightColor',
        self::COLOR_PRIMARY_DARK => 'primaryDarkColor',
    
        self::COLOR_SECONDARY => 'secondaryColor',
        self::COLOR_COMPLIMENTARY => 'complimentaryColor',
        self::COLOR_COMPLIMENTARY_DARK => 'complimentaryDarkColor',
        self::COLOR_MOD => 'modColor',
    
        self::COLOR_ITEM_BACKGROUND => 'itemBackgroundColor',
        self::COLOR_ITEM_BORDER => 'itemBorderColor',
    
        self::COLOR_NOTIFY_SUCCESS => 'notifySuccessColor',
        self::COLOR_NOTIFY_WARN => 'notifyWarnColor',
        self::COLOR_NOTIFY_ERROR => 'notifyErrorColor',
    
        self::COLOR_FONT_DEFAULT => 'defaultFontColor',
        self::COLOR_FONT_NAV => 'navFontColor',
        self::COLOR_FONT_SECONDARY => 'secondaryFontColor',
        self::COLOR_FONT_ERROR => 'errorFontColor',
        self::COLOR_FONT_CONTRAST => 'primaryContrastFontColor',
        self::COLOR_FONT_CONTRAST_DARK => 'primaryContrastFontDarkColor',
    );
    
    public static $primaryKey = array();

    public static $fields = array(
        'profileId' => array(
            'type' => 'integer',
            'dbmapping' => 'profile_id'),
        'colors' => array(
            'type' => 'text',
            'dbmapping' => 'colors' ),
        'css' => array(
            'type' => 'text',
            'dbmapping' => 'css' ),
    );

    public $profileId;
    public $colors;
    public $css;
}
