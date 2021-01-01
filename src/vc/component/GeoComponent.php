<?php
namespace vc\component;

class GeoComponent extends AbstractComponent
{
    // Get new key via https://developers.google.com/maps/web-services/
    // Veggiecommunity:
    const API_KEY ="ABQIAAAAb0sBDX9plMuKZeIS1vMD2hQNV7IioWoY85yKBoVqnJHQ-T6nHxQIxJShb3hqQ7mbSMw3zCuMK820Pg";

    const WORLD_RADIUS_MI = "3963.1";
    const WORLD_RADIUS_KM = "6378";

    public function isIpBlocked($ip, $informModerators = false, $step = null)
    {
        if (!empty($ip) &&
           $ip != '127.0.0.1') {
            $geoIpModel = $this->getDbModel('GeoIp');
            $ipCountry = $geoIpModel->getIso2ByIp($ip);
            if (in_array($ipCountry, \vc\config\Globals::$blockedCountries)) {
                if ($informModerators) {
                    $sessionModel = $this->getModel('Session');
                    $parameters = array_merge($_POST);

                    $suspicionModel = $this->getDbModel('Suspicion');
                    $suspicionModel->addSuspicion(
                        $sessionModel->getUserId(),
                        \vc\model\db\SuspicionDbModel::TYPE_SPAM_ILLEGAL_COUNTRY_LOGIN,
                        $ip,
                        array(
                            'Ip' => $ip,
                            'Country' => $ipCountry,
                        )
                    );

                    $modMessageModel = $this->getDbModel('ModMessage');
                    $modMessageModel->addMessage(
                        $sessionModel->getUserId(),
                        $ip,
                        'User tried to call action ' . $step . ' from banned country:' . "\n" .
                        "User: " . $sessionModel->getUserId() . "\n" .
                        "IP: " . $ip . "\n" .
                        "Country: " . $ipCountry . "\n" .
                        var_export($parameters, true)
                    );
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getCoordinates($locale, $postalCode, $city, $region, $countryId, $useGoogleWebservice = true)
    {
        // Return empty coordinates if postalCode AND city is missing
        if (empty($postalCode) && empty($city)) {
            return array(0, 0);
        }

        $geonames = array();
        if (!empty($postalCode)) {
            $query = 'SELECT place_name, latitude, longitude FROM vc_geoname ' .
                     'WHERE country_id = ' . intval($countryId) .
                     ' AND postal_code="' . $this->getDb()->prepareSQL($postalCode) .  '"';
            $result = $this->getDb()->select($query);
            while ($row = $result->fetch_row()) {
                $geonames[] = array('name' => $row[0],
                                    'latitude' => $row[1],
                                    'longitude' => $row[2]);
            }
        }

        if (count($geonames) == 1) {
            // Perfect fit via postal code
            return array(floatval($geonames[0]['latitude']), floatval($geonames[0]['longitude']));
        } elseif (count($geonames) > 1) {
            // Multiple coordinates for the postal code. Trying to match the name of the city
            // Pick the first one if the city is empty.
            if (empty($city)) {
                return array(floatval($geonames[0]['latitude']), floatval($geonames[0]['longitude']));
            } else {
                $bestGeoname = null;
                $bestGeonameLevenshtein = null;
                foreach ($geonames as $geoname) {
                    $levenshtein = levenshtein($geoname['name'], $city);
                    if ($bestGeoname === null ||
                       $levenshtein < $bestGeonameLevenshtein) {
                        $bestGeoname = $geoname;
                        $bestGeonameLevenshtein = $levenshtein;
                    }
                }
                return array(floatval($bestGeoname['latitude']), floatval($bestGeoname['longitude']));
            }
        } else {
            if (!empty($city)) {
                // We have a city but the postal code wasn't helpful. So we try to get the coordinates based on
                // the name. If there is only one city on the geonames we will use it.
                $query = 'SELECT latitude, longitude FROM vc_geoname' .
                         ' WHERE country_id = ' . intval($countryId) .
                         ' AND place_name="' . $this->getDb()->prepareSQL($city) .  '"';
                $result = $this->getDb()->select($query);
                if ($result->num_rows == 1) {
                    $row = $result->fetch_row();
                    return array(floatval($row[0]), floatval($row[1]));
                }

                // Ask Google if the postalCode wasn't helpful and at least the city is provided
                if ($useGoogleWebservice) {
                    $searchParams = array();
                    $searchParams[] = $city;

                    if (!empty($region)) {
                        $searchParams[] = $region;
                    }

                    // Get the countryname
                    $query = 'SELECT name_' . $locale . ' FROM vc_country ' .
                             'WHERE id = ' . intval($countryId) . ' LIMIT 1';
                    $result = $this->getDb()->select($query);
                    if ($result->num_rows === 0) {
                        return array(0, 0);
                    }
                    $row = $result->fetch_row();
                    $searchParams[] = $row[0];
                    $result->free();

                    $position = $this->getPosition(implode(', ', $searchParams));
                    return $position;
                }
            }
        }

        // Fallback
        return array(0, 0);
    }

    /**
     * get position by seachr
     */
    // :TODO: include country in ALL queries
    private function getPosition($searchstring, $urlencode = true)
    {
        if (empty($searchstring)) {
            return array(0, 0);
        } else {
            if ($urlencode) {
                $searchstring = urlencode($searchstring);
            }

            $address = 'https://maps.googleapis.com/maps/api/geocode/json' .
                       '?address=' . $searchstring  .
                       '&sensor=false' .
                       '&key=AIzaSyDkOoIvFv-NNsQ7_5ba9UFyxlGm1irVZhE';
            $response = \vc\helper\CurlHelper::call($address);
            if (empty($response)) {
                return array(0, 0);
            }
            $decodedResponse = json_decode($response, true);


            if (!array($decodedResponse)) {
                \vc\lib\ErrorHandler::error(
                    "GoogleMap: Invalid result for " . $searchstring . " - " .  var_export($response, true),
                    __FILE__,
                    __LINE__
                );
                return array(0, 0);
            } elseif (!empty($decodedResponse['error_message'])) {
                \vc\lib\ErrorHandler::warning(
                    "GoogleMap: Error message for " . $searchstring . ": " .  $decodedResponse['error_message'],
                    __FILE__,
                    __LINE__
                );
                return array(0, 0);
            } elseif (empty($decodedResponse['results'][0]['geometry']['location']['lat']) ||
                      empty($decodedResponse['results'][0]['geometry']['location']['lng'])) {
                return array(0, 0);
            } else {
                return array($decodedResponse['results'][0]['geometry']['location']['lat'],
                             $decodedResponse['results'][0]['geometry']['location']['lng']);
            }
        }
    }

    /**
     * distance of point1 to poinr2
     */
    // :TODO: migrator2 - replace static function in views
    public static function getDistance($latitude1, $longitude1, $latitude2, $longitude2, $distanceFormat = "km")
    {
        if ($latitude1 == $latitude2 &&
            $longitude1 == $longitude2) {
            return 0;
        }

        $latitude_radius1 = deg2rad($latitude1);
        $longitude_radius1 = deg2rad($longitude1);

        $latitude_radius2 = deg2rad($latitude2);
        $longitude_radius2 = deg2rad($longitude2);

        $dis = acos((sin($latitude_radius1) * sin($latitude_radius2)) +
                    (cos($latitude_radius1) * cos($latitude_radius2) * cos($longitude_radius2 - $longitude_radius1)))
               * self::getWorldRadius($distanceFormat);
        if ($distanceFormat == 'mi') {
            $dis = $dis / 1.6;
        }
        return round($dis);
    }

    /**
     * get radius of earth in wanted format.
     */
    public static function getWorldRadius($distanceFormat = 'km')
    {
        if ($distanceFormat == 'mi') {
            // Radius in miles
            return self::WORLD_RADIUS_MI;
        } else {
            return self::WORLD_RADIUS_KM;
        }
    }
}
