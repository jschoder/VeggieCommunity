<?php
namespace vc\controller\web\user;

class SearchController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return empty($_GET);
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('menu.users'));
        $this->getView()->set('activeMenuitem', 'users');

        // Assertions
        $request->assertNumericParam('age-from', true, 8, 120);
        $request->assertNumericParam('age-to', true, 8, 120);
        $request->assertNumericArrayParam('country', true);
        $request->assertNumericParam('distance', true);
        $request->assertValidArrayParam('gender', array_keys(\vc\config\Fields::getGenderFields()), true);
        // Using the check for numeric values instead of valid array since users may have old bookmarks
        $request->assertNumericArrayParam('search', true);
        $request->assertValidArrayParam('nutrition', array_keys(\vc\config\Fields::getNutritionFields()), true);
        $request->assertValidArrayParam('smoking', array_keys(\vc\config\Fields::getSmokingFields()), true);
        $request->assertValidArrayParam('alcohol', array_keys(\vc\config\Fields::getAlcoholFields()), true);
        $request->assertValidArrayParam('religion', array_keys(\vc\config\Fields::getReligionFields()), true);
        $request->assertValidArrayParam('zodiac', array_keys(\vc\config\Fields::getZodiacFields()), true);
        $request->assertValidArrayParam('political', array_keys(\vc\config\Fields::getPoliticalFields()), true);
        $request->assertValidArrayParam('marital', array_keys(\vc\config\Fields::getMaritalFields()), true);
        $request->assertValidArrayParam('children', array_keys(\vc\config\Fields::getChildrenFields()), true);
        $request->assertValidArrayParam('relocate', array_keys(\vc\config\Fields::getRelocateFields()), true);
        $request->assertValidArrayParam('bodytype', array_keys(\vc\config\Fields::getBodyTypeFields()), true);
        $request->assertValidArrayParam('bodyheight', array_keys(\vc\config\Fields::getBodyHeightFields()), true);
        $request->assertValidArrayParam('clothing', array_keys(\vc\config\Fields::getClothingFields()), true);
        $request->assertValidArrayParam('haircolor', array_keys(\vc\config\Fields::getHairColorFields()), true);
        $request->assertValidArrayParam('eyecolor', array_keys(\vc\config\Fields::getEyeColorFields()), true);
        $request->assertNumericParam('index', true);
        $request->assertNumericParam('limit', true);
        if (array_key_exists('sort', $_GET)) {
            \vc\lib\Assert::assertValueInArray(
                'sort',
                $_GET['sort'],
                array('last_login', 'last_update', 'first_entry'),
                true
            );
        }

        // Copies the content of the query
        $requestQuery = $_GET;

        $this->getView()->set('requestQuery', $requestQuery);

        $cacheModel = $this->getModel('Cache');
        $this->getView()->set('countries', $cacheModel->getCountries($this->locale, $this->getIp(), true));
        $this->getView()->set('regions', \vc\config\Fields::getRegions());

        // Reading the defaultValues.
        $this->getView()->set('defaultGender', $this->readDecodedParameters("gender", array()));
        $this->getView()->set('defaultAgeFrom', $this->readDecodedParameters("age-from", 8));
        $this->getView()->set('defaultAgeTo', $this->readDecodedParameters("age-to", 120));
        $this->getView()->set('defaultSearchString', $this->readDecodedParameters("searchstring", ""));
        $this->getView()->set('defaultSearch', $this->readDecodedParameters("search", array()));
        $this->getView()->set('defaultNutrition', $this->readDecodedParameters("nutrition", array()));

        $geoIpModel = $this->getDbModel('GeoIp');
        $ipCountry = $geoIpModel->getCountryByIp($this->getIp());
        if (in_array($this->locale, \vc\config\Globals::$defaultCountries)) {
            $defaultCountries = \vc\config\Globals::$defaultCountries[$this->locale];
        } else {
            $defaultCountries = array();
        }
        if (!empty($ipCountry)) {
            $defaultCountries[] = $ipCountry;
        }
        array_unique($defaultCountries);
        $this->getView()->set('defaultCountries', $this->readDecodedParameters('country', $defaultCountries));
        $this->getView()->set('defaultRegions', $this->readDecodedParameters("region", array()));
        $this->getView()->set('defaultDistance', $this->readDecodedParameters("distance", 0));

        if ($this->getSession()->hasActiveSession()) {
            if ($this->getSession()->getSetting(\vc\object\Settings::DISTANCE_UNIT)
                == \vc\object\Settings::DISTANCE_UNIT_KILOMETER) {
                $defaultDistUnit = "km";
            } else {
                $defaultDistUnit = "mi";
            }
        } else {
            if ($this->getLocale() == 'de') {
                $defaultDistUnit = "km";
            } else {
                $defaultDistUnit = "mi";
            }
        }
        $this->getView()->set('defaultDistanceUnit', $this->readDecodedParameters("distanceunit", $defaultDistUnit));
        $this->getView()->set('defaultSmoking', $this->readDecodedParameters("smoking", array()));
        $this->getView()->set('defaultAlcohol', $this->readDecodedParameters("alcohol", array()));
        $this->getView()->set('defaultReligion', $this->readDecodedParameters("religion", array()));
        $this->getView()->set('defaultZodiac', $this->readDecodedParameters("zodiac", array()));
        $this->getView()->set('defaultPolitical', $this->readDecodedParameters("political", array()));
        $this->getView()->set('defaultMarital', $this->readDecodedParameters("marital", array()));
        $this->getView()->set('defaultChildren', $this->readDecodedParameters("children", array()));
        $this->getView()->set('defaultRelocate', $this->readDecodedParameters("relocate", array()));
        $this->getView()->set('defaultBodyType', $this->readDecodedParameters("bodytype", array()));
        $this->getView()->set('defaultBodyHeight', $this->readDecodedParameters("bodyheight", array()));
        $this->getView()->set('defaultClothing', $this->readDecodedParameters("clothing", array()));
        $this->getView()->set('defaultHairColor', $this->readDecodedParameters("haircolor", array()));
        $this->getView()->set('defaultEyeColor', $this->readDecodedParameters("eyecolor", array()));

        $selectedHobbies = $this->readDecodedParameters("hobbies", array());
        $this->getView()->set('selectedHobbies', $selectedHobbies);

        $hobbies = $cacheModel->getHobbies($this->locale);
        $this->getView()->set('hobbies', $hobbies);

        if (array_key_exists("preferencefilter", $_GET)) {
            $this->getView()->set('filterPreference', true);
        } else {
            $this->getView()->set('filterPreference', false);
        }
        if (array_key_exists("ignoreEmptyAgeRange", $_GET)) {
            $this->getView()->set('filterIgnoreEmptyAgeRange', true);
        } else {
            $this->getView()->set('filterIgnoreEmptyAgeRange', false);
        }

        if ($this->getSession()->hasActiveSession()) {
            $matchingModel = $this->getDbModel('Matching');
            $hasMatching = $matchingModel->getCount(array('user_id' => $this->getSession()->getUserId())) > 0;
            $this->getView()->set('hasMatching', $hasMatching);

            if (array_key_exists("matchingfilter", $_GET)) {
                $this->getView()->set('matchingPreference', intval($_GET['matchingfilter']));
            } else {
                $this->getView()->set('matchingPreference', 0);
            }
        }
        if (array_key_exists("photofilter", $_GET)) {
            $this->getView()->set('filterPhoto', true);
        } else {
            $this->getView()->set('filterPhoto', false);
        }
        if (array_key_exists("textfilter", $_GET)) {
            $this->getView()->set('filterText', true);
        } else {
            $this->getView()->set('filterText', false);
        }

        $defaultSort = 'last_update';
        if (!empty($_GET["sort"])) {
            $defaultSort= $_GET["sort"];
        }
        $this->getView()->set('sortByLastLogin', ($defaultSort == 'last_login'));
        $this->getView()->set('sortByLastUpdate', ($defaultSort == 'last_update'));
        $this->getView()->set('sortByFirstEntry', ($defaultSort == 'first_entry'));

        echo $this->getView()->render('user/search', true);
    }

    private function readDecodedParameters($key, $defaultValue)
    {
        global $_GET;
        if (!empty($_GET[$key])) {
            if (is_array($_GET[$key])) {
                $values = array();
                foreach ($_GET[$key] as $value) {
                    $values[] = urldecode($value);
                }
                return $values;
            } else {
                return urldecode($_GET[$key]);
            }
        } else {
            return $defaultValue;
        }
    }
}
