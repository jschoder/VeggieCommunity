<?php

namespace vc\config;

class Fields
{
    public static function getGenderFields()
    {
        return array(
                    2 => gettext('profile.gender.male'),
                    4 => gettext('profile.gender.female'),
                    6 => gettext('profile.gender.other'),
                    8 => gettext('profile.gender.none'));
    }

    /**
     * Short id: SEARCH
     */
    public static function getSearchFields()
    {
        return array(
                    1 => gettext('profile.search.penpal'),
                    5 => gettext('profile.search.pal'),
                    10 => gettext('profile.search.activist'),

                    21 => gettext('profile.search.letsee.male'),
                    25 => gettext('profile.search.letsee.female'),
                    29 => gettext('profile.search.letsee.queer'),

                    41 => gettext('profile.search.date.male'),
                    45 => gettext('profile.search.date.female'),
                    49 => gettext('profile.search.date.queer'),

                    61 => gettext('profile.search.relationship.male'),
                    65 => gettext('profile.search.relationship.female'),
                    69 => gettext('profile.search.relationship.queer'),

                    81 => gettext('profile.search.polyamorous.male'),
                    85 => gettext('profile.search.polyamorous.female'),
                    89 => gettext('profile.search.polyamorous.queer'),

                    101 => gettext('profile.search.marriage.male'),
                    105 => gettext('profile.search.marriage.female'),
                    109 => gettext('profile.search.marriage.queer'));
    }

    public static function containsFriendsSearchField($search)
    {
        return in_array(1, $search) ||
               in_array(5, $search) ||
               in_array(10, $search);
    }

    public static function containsRomanticSearchField($search)
    {
        return in_array(21, $search) ||
               in_array(25, $search) ||
               in_array(29, $search) ||
               in_array(41, $search) ||
               in_array(45, $search) ||
               in_array(49, $search) ||
               in_array(61, $search) ||
               in_array(65, $search) ||
               in_array(69, $search) ||
               in_array(81, $search) ||
               in_array(85, $search) ||
               in_array(89, $search) ||
               in_array(101, $search) ||
               in_array(105, $search) ||
               in_array(109, $search);
    }

    /**
     * Short id: ZODIAC
     */
    public static function getZodiacFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.zodiac.aquarius'),
                    3 => gettext('profile.zodiac.pisces'),
                    4 => gettext('profile.zodiac.aries'),
                    5 => gettext('profile.zodiac.taurus'),
                    6 => gettext('profile.zodiac.gemini'),
                    7 => gettext('profile.zodiac.cancer'),
                    8 => gettext('profile.zodiac.leo'),
                    9 => gettext('profile.zodiac.virgo'),
                    10 => gettext('profile.zodiac.libra'),
                    11 => gettext('profile.zodiac.scorpio'),
                    12 => gettext('profile.zodiac.sagittarius'),
                    13 => gettext('profile.zodiac.capricorn'),
                    14 => gettext('profile.zodiac.dont_believe'));
    }

    /**
     * Short id: NUTRIT
     */
    public static function getNutritionFields($gender = null)
    {
        switch ($gender) {
            case 2:
                $gettextKeyPostfix = '.m';
                break;
            case 4:
                $gettextKeyPostfix = '.f';
                break;
            case 6:
                $gettextKeyPostfix = '.o';
                break;
            case 8:
                $gettextKeyPostfix = '.a';
                break;
            default:
                $gettextKeyPostfix = '';
        }
        return array(
                    // 1 => gettext('profile.nutrition.vegetarian_at_home' . $gettextKeyPostfix), - inactive
                    2 => gettext('profile.nutrition.almost_vegetarian' . $gettextKeyPostfix),
                    3 => gettext('profile.nutrition.vegetarian' . $gettextKeyPostfix),
                    4 => gettext('profile.nutrition.almost_vegan' . $gettextKeyPostfix),
                    5 => gettext('profile.nutrition.vegan' . $gettextKeyPostfix),
                    10 => gettext('profile.nutrition.raw_food_insticto' . $gettextKeyPostfix),
                    6 => gettext('profile.nutrition.raw_food_vegetarian' . $gettextKeyPostfix),
                    7 => gettext('profile.nutrition.raw_food_vegan' . $gettextKeyPostfix),
                    8 => gettext('profile.nutrition.frutarian' . $gettextKeyPostfix),
                    // 9 => gettext('profile.nutrition.macrobiotic' . $gettextKeyPostfix), - inactive
                    11 => gettext('profile.nutrition.freegan' . $gettextKeyPostfix));
    }

    public static function getNutritionCaption($nutrition, $nutrition_freetext, $gender)
    {
        $caption = '';
        if (empty($gender)) {
            $nutrition_fields = self::getNutritionFields();
        } else {
            $nutrition_fields = self::getNutritionFields($gender);
        }

        if (array_key_exists($nutrition, $nutrition_fields)) {
            $caption .= $nutrition_fields[$nutrition];
            if (!empty($nutrition_freetext)) {
                $caption .= ' (' . $nutrition_freetext . ')';
            }
        }
        return $caption;
    }

    /**
     * Short id: RELIGI
     */
    public static function getReligionFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.religion.not_religious'),
                    3 => gettext('profile.religion.atheist'),
                    4 => gettext('profile.religion.christ'),
                    5 => gettext('profile.religion.moslem'),
                    6 => gettext('profile.religion.jew'),
                    7 => gettext('profile.religion.hindu'),
                    8 => gettext('profile.religion.buddhist'),
                    9 => gettext('profile.religion.natural'),
                    10 => gettext('profile.religion.spiritual'),
                    11 => gettext('profile.religion.other'));
    }

    /**
     * Short id: CHILD
     */
    public static function getChildrenFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    8 => gettext('profile.children.dontknowyet'),
                    2 => gettext('profile.children.no'),
                    6 => gettext('profile.children.no.want'),
                    5 => gettext('profile.children.no.dontwant'),
                    7 => gettext('profile.children.no.dontwantown'),
                    3 => gettext('profile.children.yes'),
                    9 => gettext('profile.children.yes.want'),
                    10 => gettext('profile.children.yes.dontwant'),
                    4 => gettext('profile.children.grown_up'));
    }

    /**
     * Short id: SMOKE
     */
    public static function getSmokingFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.smoking.none'),
                    3 => gettext('profile.smoking.occasionally'),
                    4 => gettext('profile.smoking.in_company'),
                    6 => gettext('profile.smoking.wanttoquit'),
                    5 => gettext('profile.smoking.smoker'));
    }

    /**
     * Short id: ALCOH
     */
    public static function getAlcoholFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.alcohol.none'),
                    7 => gettext('profile.alcohol.rare'),
                    3 => gettext('profile.alcohol.occasionally'),
                    4 => gettext('profile.alcohol.in_company'),
                    5 => gettext('profile.alcohol.at_food'),
                    6 => gettext('profile.alcohol.often'),
            );
    }

    /**
     * Short id: POLITC
     */
    public static function getPoliticalFields()
    {
        return array(
                    2 => gettext('profile.political.ultra_conservative'),
                    6 => gettext('profile.political.conservative'),
                    10 => gettext('profile.political.centrist'),
                    14 => gettext('profile.political.liberal'),
                    18 => gettext('profile.political.neo liberal'),
                    22 => gettext('profile.political.ultra_liberal'),
                    26 => gettext('profile.political.left'),
                    30 => gettext('profile.political.far_left'),
                    34 => gettext('profile.political.anarchist'),
                    42 => gettext('profile.political.green'),
                    46 => gettext('profile.political.libertarian'),
                    99 => gettext('profile.political.other'),
                    100 => gettext('profile.political.unpolitical'));
    }

    /**
     * Short id: MARTIL
     */
    public static function getMaritalFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.marital.single'),
                    3 => gettext('profile.marital.divorced'),
                    4 => gettext('profile.marital.widowed'),
                    5 => gettext('profile.marital.inlove'),
                    6 => gettext('profile.marital.relationship'),
                    11 => gettext('profile.marital.polyamorous'),
                    7 => gettext('profile.marital.openrelationship'),
                    8 => gettext('profile.marital.engaged'),
                    9 => gettext('profile.marital.married'),
                    10 => gettext('profile.marital.complicated'),
                    12 => gettext('profile.marital.notinterested'));
    }

    /**
     * Short id: HEIGHT
     */
    public static function getBodyHeightFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2=>'4\'2"(127cm)',
                    3=>'4\'3"(130cm)',
                    4=>'4\'4"(132cm)',
                    5=>'4\'5"(135cm)',
                    6=>'4\'6"(137cm)',
                    7=>'4\'7"(140cm)',
                    8=>'4\'8"(142cm)',
                    9=>'4\'9"(145cm)',
                    10=>'4\'10"(146cm)',
                    11=>'4\'11"(150cm)',
                    12=>'5\'0"(152cm)',
                    13=>'5\'1"(155cm)',
                    14=>'5\'2"(157cm)',
                    15=>'5\'3"(160cm)',
                    16=>'5\'4"(163cm)',
                    17=>'5\'5"(165cm)',
                    18=>'5\'6"(168cm)',
                    19=>'5\'7"(170cm)',
                    20=>'5\'8"(173cm)',
                    21=>'5\'9"(175cm)',
                    22=>'5\'10"(178cm)',
                    23=>'5\'11"(180cm)',
                    24=>'6\'0"(183cm)',
                    25=>'6\'1"(185cm)',
                    26=>'6\'2"(188cm)',
                    27=>'6\'3"(190cm)',
                    28=>'6\'4"(193cm)',
                    29=>'6\'5"(196cm)',
                    30=>'6\'6"(198cm)',
                    31=>'6\'7"(201cm)',
                    32=>'6\'8"(203cm)',
                    33=>'6\'9"(206cm)',
                    34=>'6\'10"(208cm)',
                    35=>'6\'11"(211cm)',
                    36=>'7\'0"(213cm)');
    }

    /**
     * Short id: BDTYPE
     */
    public static function getBodyTypeFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.bodytype.thin'),
                    3 => gettext('profile.bodytype.athletic'),
                    4 => gettext('profile.bodytype.average'),
                    5 => gettext('profile.bodytype.muscular'),
                    6 => gettext('profile.bodytype.extrapounds'),
                    9 => gettext('profile.bodytype.fat'),
                    7 => gettext('profile.bodytype.overweight'),
                    8 => gettext('profile.bodytype.other'));
    }

    /**
     * Short id: CLOTH
     */
    public static function getClothingFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.clothing.alternative'),
                    3 => gettext('profile.clothing.casual'),
                    4 => gettext('profile.clothing.sporty'),
                    5 => gettext('profile.clothing.playful'),
                    6 => gettext('profile.clothing.trendy'),
                    7 => gettext('profile.clothing.modern'),
                    8 => gettext('profile.clothing.simple'),
                    9 => gettext('profile.clothing.classic'),
                    10 => gettext('profile.clothing.conservative'),
                    11 => gettext('profile.clothing.elegant'),
                    12 => gettext('profile.clothing.glamorous'));
    }

    /**
     * Short id: HAIR
     */
    public static function getHairColorFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.haircolor.black'),
                    3 => gettext('profile.haircolor.darkbrown'),
                    4 => gettext('profile.haircolor.mediumbrown'),
                    5 => gettext('profile.haircolor.lightbrown'),
                    6 => gettext('profile.haircolor.darkblonde'),
                    7 => gettext('profile.haircolor.blonde'),
                    8 => gettext('profile.haircolor.red'),
                    9 => gettext('profile.haircolor.gray'),
                    10 => gettext('profile.haircolor.bald'),
                    11 => gettext('profile.haircolor.other'));
    }

    /**
     * Short id: EYE
     */
    public static function getEyeColorFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.eyecolor.blue'),
                    3 => gettext('profile.eyecolor.brown'),
                    4 => gettext('profile.eyecolor.hazel'),
                    5 => gettext('profile.eyecolor.green'),
                    6 => gettext('profile.eyecolor.black'),
                    7 => gettext('profile.eyecolor.gray'),
                    8 => gettext('profile.eyecolor.other'));
    }

    /**
     * Short id: RELCOT
     */
    public static function getRelocateFields()
    {
        return array(
                    1 => gettext('profile.no_statement'),
                    2 => gettext('profile.relocate.yes'),
                    3 => gettext('profile.relocate.no'),
                    4 => gettext('profile.relocate.dontknow'));
    }

    public static function getRegions()
    {
        $regions = array();
        // Germany
        $regions[49]=array(
            'BW' => 'Baden-Württemberg',
            'BY' => 'Bayern',
            'BE' => 'Berlin',
            'BB' => 'Brandenburg',
            'HB' => 'Bremen',
            'HH' => 'Hamburg',
            'HE' => 'Hessen',
            'MV' => 'Mecklenburg-Vorpommern',
            'NI' => 'Niedersachsen',
            'NW' => 'Nordrhein-Westfalen',
            'RP' => 'Rheinland-Pfalz',
            'SL' => 'Saarland',
            'SN' => 'Sachsen',
            'ST' => 'Sachsen-Anhalt',
            'SH' => 'Schleswig-Holstein',
            'TH' => 'Thüringen'
        );
        // Austria
        $regions[43]=array(
            'AT1' => 'Burgenland',
            'AT2' => 'Kärnten',
            'AT3' => 'Niederösterreich',
            'AT4' => 'Oberösterreich',
            'AT5' => 'Salzburg',
            'AT6' => 'Steiermark',
            'AT7' => 'Tirol',
            'AT8' => 'Vorarlberg',
            'AT9' => 'Wien'
        );
        // Switzerland
        $regions[41]=array(
            'AG' => 'Aargau',
            'AI' => 'Appenzell',
            'BS' => 'Basel',
            'BE' => 'Bern',
            'FR' => 'Freiburg',
            'GE' => 'Genf',
            'GL' => 'Glarus',
            'GR' => 'Graubünden',
            'LU' => 'Luzern',
            'NE' => 'Neuenburg',
            'NW' => 'Nidwalden',
            'OW' => 'Obwalden',
            'SG' => 'Sankt Gallen',
            'SH' => 'Schaffhausen',
            'SZ' => 'Schwyz',
            'SO' => 'Solothurn',
            'TI' => 'Tessin',
            'TG' => 'Thurgau',
            'UR' => 'Uri',
            'VD' => 'Waadt',
            'VS' => 'Wallis',
            'ZG' => 'Zug',
            'ZH' => 'Zürich'
        );
        // USA
        $regions[1]=array(
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming'
        );
        // Canada
        $regions[2]=array(
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland',
            'NT' => 'Northwest Territories',
            'NS' => 'Nova Scotia',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon Territory'
        );
        // Great Britian
        $regions[44]=array(
            'ENG' => 'England',
            'SCT' => 'Scotland',
            'WAL' => 'Wales',
            'NIR' => 'Northern Ireland'
        );
        return $regions;
    }

    public static function getEventCategoryTree()
    {
        return array(
            10 => array(11, 12, 13, 14, 15),
            40 => array(41, 42, 43, 44, 45, 46, 49),
            60 => array(61, 62, 63, 64),
            80 => array(81, 82),
            100 => array(101, 102)
        );
    }

    public static function getEventCategories()
    {
        return array(
            10 => array(
                'title' => 'event.category.private',
                'color' => 'a10078',
                'class' => 'private'
            ),
            11 => array(
                'title' => 'event.category.private.party',
                'color' => '9768d1',
                'class' => 'privateParty'
            ),
            12 => array(
                'title' => 'event.category.private.goingout',
                'color' => '7b52ab',
                'class' => 'privateGoingout'
            ),
            13 => array(
                'title' => 'event.category.private.music',
                'color' => '553285',
                'class' => 'privateMusic'
            ),
            14 => array(
                'title' => 'event.category.private.birthday',
                'color' => 'de264c',
                'class' => 'privateBirthday'
            ),
            15 => array(
                'title' => 'event.category.private.sport',
                'color' => 'f0788c',
                'class' => 'privateSport'
            ),
            40 => array(
                'title' => 'event.category.activism',
                'color' => '42a627',
                'class' => 'activism'
            ),
            41 => array(
                'title' => 'event.category.activism.demonstration',
                'color' => '7cb042',
                'class' => 'activismDemonstration'
            ),
            42 => array(
                'title' => 'event.category.activism.vigil',
                'color' => '81c14f',
                'class' => 'activismVigil'
            ),
            43 => array(
                'title' => 'event.category.activism.infostall',
                'color' => '96be5d',
                'class' => 'activismInfostall'
            ),
            44 => array(
                'title' => 'event.category.activism.lecture',
                'color' => '3f912f',
                'class' => 'activismLecture'
            ),
            45 => array(
                'title' => 'event.category.activism.conference',
                'color' => '1b7223',
                'class' => 'activismConference'
            ),
            46 => array(
                'title' => 'event.category.activism.benefit',
                'color' => '126537',
                'class' => 'activismBenefit'
            ),
            49 => array(
                'title' => 'event.category.activism.online',
                'color' => 'bad83c',
                'class' => 'activismOnline'
            ),
            60 => array(
                'title' => 'event.category.meeting',
                'color' => '2c1dff',
                'class' => 'categoryMeeting'
            ),
            61 => array(
                'title' => 'event.category.meeting.gamingevening',
                'color' => '1c3ffd',
                'class' => 'meetingGamingEvening'
            ),
            62 => array(
                'title' => 'event.category.meeting.roundtable',
                'color' => '1b76ff',
                'class' => 'meetingRoundtable'
            ),
            63 => array(
                'title' => 'event.category.meeting.networking',
                'color' => '15a9ff',
                'class' => 'meetingNetworking'
            ),
            64 => array(
                'title' => 'event.category.meeting.activistmeetup',
                'color' => '0eeaff',
                'class' => 'meetingActivistMeetup'
            ),
            80 => array(
                'title' => 'event.category.commercial',
                'color' => 'f58803',
                'class' => 'commercial'
            ),
            81 => array(
                'title' => 'event.category.commercial.opening',
                'color' => 'da5103',
                'class' => 'commercialOpening'
            ),
            82 => array(
                'title' => 'event.category.tradefair',
                'color' => 'be350f',
                'class' => 'tradefair'
            ),
            100 => array(
                'title' => 'event.category.other',
                'color' => 'aea79f',
                'class' => 'other'
            ),
            101 => array(
                'title' => 'event.category.other.festival',
                'color' => 'caa170',
                'class' => 'festival'
            ),
            102 => array(
                'title' => 'event.category.other.summerfete',
                'color' => '80a1bc',
                'class' => 'summerfete'
            ),
        );
    }

    public static function getTicketCategories()
    {
        return array(
            101 => gettext('help.category.general'),
            102 => gettext('help.category.problems'),
            103 => gettext('help.category.improvement'),
            104 => gettext('help.category.wishlist'),
            EntityTypes::PROFILE => gettext('help.category.reportuser'),
            EntityTypes::GROUP =>gettext('help.category.reportgroup'),
            EntityTypes::PROFILE_PICTURE => gettext('help.category.pictures')
        );
    }
}
