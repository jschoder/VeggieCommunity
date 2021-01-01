<?php
namespace vc\model\service;

class UserSearchService
{
    private $controller;

    private $profileIds = null;

    private $count = null;

    private $savedSearchInterval = null;

    private $savedSearchType = null;

    private $savedSearchUrl = null;

    private $requestStart;

    private $requestLimit;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function query($view, \vc\controller\Request $request, $locale, $currentUser, $isAdmin, $settings)
    {
        $this->requestStart = $request->getInt('index');
        if ($this->requestStart < 0) {
            $this->requestStart = 0;
        }
        $this->requestLimit = $request->getInt('limit');
        if ($this->requestLimit <= 0) {
            $this->requestLimit = 12;
        }

        $sort = $request->getText('sort', 'last_update');
        if (!in_array($sort, array('last_login', 'last_update', 'first_entry'))) {
            $sort = 'last_update';
        }

        // Creating the where-clauses.
        $where = array();
        $joins = array();
        $joinParams = array();
        $whereParams = array();

        if ($isAdmin && $request->hasParameter('userid')) {
            $where[] = 'vc_profile.id = ? OR vc_profile.nickname LIKE ? OR vc_profile.email LIKE ? OR vc_profile.email = ?';
            $whereParams[] = $request->getInt('userid');
            $whereParams[] = trim($request->getText('userid'));
            $whereParams[] = trim($request->getText('userid'));
            $whereParams[] = sha1(trim($request->getText('userid')));
        } else {
            $where[] = 'active>0';
        }

        // More complex questions are later in the list to enhance the performance
        $profileModel = $this->controller->getDbModel('Profile');
        if ($request->hasArrayParameter('gender')) {
            $params = $request->getIntArray('gender');
            $where[] = 'gender IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('country')) {
            $params = $request->getIntArray('country');
            $where[] = 'country IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('region')) {
            $params = $request->getTextArray('region');
            $where[] = 'region IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('nutrition')) {
            $params = $request->getIntArray('nutrition');
            $where[] = 'nutrition IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('smoking')) {
            $params = $request->getIntArray('smoking');
            $where[] = 'smoking IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('alcohol')) {
            $params = $request->getIntArray('alcohol');
            $where[] = 'alcohol IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('religion')) {
            $params = $request->getIntArray('religion');
            $where[] = 'religion IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('zodiac')) {
            $params = $request->getIntArray('zodiac');
            $where[] = 'zodiac IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('marital')) {
            $params = $request->getIntArray('marital');
            $where[] = 'marital IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('children')) {
            $params = $request->getIntArray('children');
            $where[] = 'children IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('relocate')) {
            $params = $request->getIntArray('relocate');
            $where[] = 'relocate IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('bodytype')) {
            $params = $request->getIntArray('bodytype');
            $where[] = 'bodytype IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('bodyheight')) {
            $params = $request->getIntArray('bodyheight');
            $where[] = 'bodyheight IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('clothing')) {
            $params = $request->getIntArray('clothing');
            $where[] = 'clothing IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('haircolor')) {
            $params = $request->getIntArray('haircolor');
            $where[] = 'haircolor IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('eyecolor')) {
            $params = $request->getIntArray('eyecolor');
            $where[] = 'eyecolor IN (' . $profileModel->fillQuery(count($params)) . ')';
            $whereParams = array_merge($whereParams, $params);
        }
        if ($request->hasArrayParameter('search')) {
            $params = $request->getIntArray('search');
            $joins[] = 'INNER JOIN (' .
                       'SELECT DISTINCT profile_id FROM vc_profile_field_search WHERE field_value IN (' .
                        $profileModel->fillQuery(count($params)) .
                       ')) join_field_search ON ' .
                       ' vc_profile.id = join_field_search.profile_id';
            $joinParams = array_merge($joinParams, $params);
        }
        if ($request->hasArrayParameter('political')) {
            $params = $request->getIntArray('political');
            $joins[] = 'INNER JOIN (' .
                       'SELECT DISTINCT profile_id FROM vc_profile_field_political WHERE field_value IN (' .
                        $profileModel->fillQuery(count($params)) .
                       ')) join_field_political ON ' .
                       ' vc_profile.id = join_field_political.profile_id';
            $joinParams = array_merge($joinParams, $params);
        }

        $savedSearchProfile = null;
        $searchid = $request->getInt('searchid');
        if (!empty($searchid)) {
            $query = 'SELECT profileid, message_interval, message_type, url, last_message '
                   . ' FROM vc_search WHERE id=' . $searchid;
            $result = $this->controller->getDb()->select($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_row();
                $profiles = $profileModel->getProfiles($locale, array($row[0]));

                // Empty result page for deleted profiles.
                if (count($profiles) === 0) {
                    return;
                }

                $savedSearchProfile = $profiles[0];
                $this->savedSearchInterval = $row[1];
                $this->savedSearchType = $row[2];
                $this->savedSearchUrl = $row[3];

                if ($this->savedSearchType == 1) {
                    // new_profiles
                    $where[] = 'DATEDIFF(first_entry, ?) > 0';
                    $whereParams[] = $row[4];
                    $sort = 'first_entry';
                } elseif ($this->savedSearchType == 2) {
                    // updated_profiles
                    $where[] = 'DATEDIFF(last_update, ?) > 0';
                    $whereParams[] = $row[4];
                    $sort = 'last_update';
                }
            }
            $result->free();
        }

        if ($request->hasParameter('age-from') && $request->hasParameter('age-to')) {
            $ageFrom = $request->getInt('age-from');
            $ageTo = $request->getInt('age-to');
            $where[] = 'age BETWEEN ? AND ?';
            $whereParams[] = $ageFrom;
            $whereParams[] = $ageTo;
            if ($ageFrom !== 8 || $ageTo !== 120) {
                $where[] = 'hide_age = 0';
            }
        }
        if ($request->hasParameter('distance') &&
            $request->hasParameter('distanceunit') &&
            ($currentUser !== null || $savedSearchProfile != null)) {
            $distance = $request->getInt('distance');
            if ($distance > 0) {
                if ($currentUser !== null) {
                    $latitude_radius = deg2rad($currentUser->latitude);
                    $longitude_radius = deg2rad($currentUser->longitude);
                } else {
                    $latitude_radius = deg2rad($savedSearchProfile->latitude);
                    $longitude_radius = deg2rad($savedSearchProfile->longitude);
                }
                $sin_latitude = sin($latitude_radius);
                $cos_latitude = cos($latitude_radius);

                $sin_latitude = number_format($sin_latitude, 16, '.', '');
                $cos_latitude = number_format($cos_latitude, 16, '.', '');
                $longitude_radius = str_replace(',', '.', $longitude_radius);
                $where[] = 'latitude != 0 OR longitude != 0';
                $where[] = 'acos(LEAST(1, (sin_latitude * ?) + (cos_latitude * ? * cos(longitude_radius - ?)))) * ? <= ?';

                $whereParams[] = $sin_latitude;
                $whereParams[] = $cos_latitude;
                $whereParams[] = $longitude_radius;
                $whereParams[] = \vc\component\GeoComponent::getWorldRadius($request->getText('distanceunit'));
                $whereParams[] = $distance;
            }
        }
        if ($request->hasParameter('searchstring')) {
            $searchString = trim($request->getText('searchstring'));
            if (!empty($searchString)) {
                $joins[] = 'INNER JOIN vc_searchstring_index ON ' .
                           'vc_searchstring_index.locale = ? AND ' .
                           'vc_searchstring_index.visibility = ? AND ' .
                           'vc_searchstring_index.profileid = vc_profile.id';
                $joinParams[] = $locale;
                $joinParams[] = $currentUser !== null ? 0 : 1;

                foreach (explode(' ', $searchString) as $phrase) {
                    $trimedPhrase = trim($phrase);
                    if (!empty($trimedPhrase)) {
                        $where[] = 'vc_searchstring_index.searchtext LIKE ?';
                        $whereParams[] = '%' . $trimedPhrase . '%';
                    }
                }
            }
        }
        
        if ($currentUser !== null || ($view == 'mail' && $savedSearchProfile != null)) {
            if ($request->hasParameter('hobbies')) {
                $params = $request->getIntArray('hobbies');
                $where[] = 'vc_profile.id IN (' .
                           'SELECT profileid FROM vc_profile_hobby WHERE hobbyid IN (' .
                           $profileModel->fillQuery(count($params)) .
                           '))';
                $whereParams = array_merge($whereParams, $params);
            }

            if ($currentUser !== null) {
                $blockedId = $currentUser->id;
            } else {
                $blockedId = $savedSearchProfile->id;
            }
            $cacheComponent = $this->controller->getModel('Cache');
            $relations = $cacheComponent->getProfileRelations($blockedId);
            if (!empty($relations[\vc\model\CacheModel::RELATIONS_BLOCKED])) {
                $where[] = 'vc_profile.id NOT IN (' .
                           implode(',', $relations[\vc\model\CacheModel::RELATIONS_BLOCKED]) .
                           ')';
            }
        }
        if ($request->hasParameter('preferencefilter')) {
            if ($currentUser !== null || ($view == 'mail' && $savedSearchProfile != null)) {
                if ($currentUser !== null) {
                    $profileId = $currentUser->id;
                } else {
                    $profileId = $savedSearchProfile->id;
                }
                $where[] = 'vc_profile.id in (
                    SELECT DISTINCT p2.id
                    FROM vc_profile p1
                    INNER JOIN vc_profile_field_search fs1 ON p1.id = ? AND p1.id = fs1.profile_id
                    INNER JOIN vc_search_match m ON m.user_1_gender = p1.gender AND m.user_1_search = fs1.field_value
                    INNER JOIN vc_profile_field_search fs2 ON m.user_2_search = fs2.field_value
                    INNER JOIN vc_profile p2 ON
                    p2.id = fs2.profile_id AND
                    p2.active > 0 AND
                    p1.id != p2.id AND
                    m.user_2_gender = p2.gender AND
                    (
                        (
                            fs1.field_value IN (1, 5, 10) AND
                            p1.age BETWEEN p2.age_from_friends AND p2.age_to_friends AND
                            p2.age BETWEEN p1.age_from_friends AND p1.age_to_friends
                        )
                        OR
                        (
                            fs1.field_value NOT IN (1, 5, 10) AND
                            p1.age BETWEEN p2.age_from_romantic AND p2.age_to_romantic AND
                            p2.age BETWEEN p1.age_from_romantic AND p1.age_to_romantic
                        )
                    ))';
                $whereParams[] = $profileId;
            }
        }
        if ($request->hasParameter('ignoreEmptyAgeRange')) {
            if ($currentUser !== null || ($view == 'mail' && $savedSearchProfile != null)) {
                $where[] = '(vc_profile.age_from_romantic > 18 OR vc_profile.age_to_romantic <> 120)';
            }
        }

        if ($request->hasParameter('matchingfilter')) {
            $matching = $request->getInt('matchingfilter');
            if (!empty($matching)) {
                if ($currentUser !== null || ($view == 'mail' && $savedSearchProfile != null)) {
                    $joins[] = 'INNER JOIN vc_match ON (' .
                               '(vc_match.min_user_id = vc_profile.id AND vc_match.max_user_id = ?) OR' .
                               '(vc_match.min_user_id = ? AND vc_match.max_user_id = vc_profile.id)' .
                               ') AND percentage >= ?';
                    if ($currentUser !== null) {
                        $joinParams[] = $currentUser->id;
                        $joinParams[] = $currentUser->id;
                    } else {
                        $joinParams[] = $savedSearchProfile->id;
                        $joinParams[] = $savedSearchProfile->id;
                    }
                    $joinParams[] = $matching;
                }
            }
        }
        if ($request->getBoolean('photofilter')) {
            // :TODO: JOE - join?
            $where[] = 'vc_profile.id in (SELECT DISTINCT profileid FROM vc_picture)';
        }
        if ($request->getBoolean('textfilter')) {
            $where[] = 'questionairelength > 100';
        }

        if ($view == 'rss') {
            $this->requestStart = 0;
            $this->requestLimit = 24;
        } elseif ($view == 'mail') {
            $this->requestStart = 0;
            $this->requestLimit = 120;
        } elseif ($view == 'short') {
            $this->requestStart = 0;
            $this->requestLimit = intval($settings->getValue(\vc\object\Settings::SAVEDSEARCH_COUNT));
        }

        $queryParams = array_merge($joinParams, $whereParams);

        // Getting the (limited) found profiles from the database
        $query = 'SELECT vc_profile.id FROM vc_profile ' .
                 implode(' ', $joins) .
                 ' WHERE (' . implode(') AND (', $where) . ')' .
                 ' ORDER BY ' . $sort . ' DESC' .
                 ' LIMIT ?,?';
        $queryParams[] = $this->requestStart;
        $queryParams[] = $this->requestLimit;
        

        /*
        if ($currentUser !== null && $currentUser->id === 70065) {
            var_dump($query);
            var_dump($queryParams);
            exit();
        }
        */

//        echo $query . "\n\n\n";
//        var_export($queryParams);
//        exit();

        $statement = $this->controller->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result($profileId);
        $this->profileIds = array();
        while ($statement->fetch()) {
            $this->profileIds[] = $profileId;
        }
        $statement->close();

        // Removing the order/limit-parameters
        array_pop($queryParams);
        array_pop($queryParams);

        // Getting the count of total found profiles in the database.
        $query =  'SELECT count(*) FROM vc_profile ' .
                  implode(' ', $joins) .
                  ' WHERE (' . implode($where, ') AND (') . ')';
        $statement = $this->controller->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result($count);
        $statement->fetch();
        $this->count = $count;
        $statement->close();
    }

    public function getProfileIds()
    {
        return $this->profileIds;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getSavedSearchInterval()
    {
        return $this->savedSearchInterval;
    }

    public function getSavedSearchType()
    {
        return $this->savedSearchType;
    }

    public function getSavedSearchUrl()
    {
        return $this->savedSearchUrl;
    }

    public function getRequestStart()
    {
        return $this->requestStart;
    }

    public function getRequestLimit()
    {
        return $this->requestLimit;
    }
}
