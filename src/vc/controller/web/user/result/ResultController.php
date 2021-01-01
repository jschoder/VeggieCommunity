<?php
namespace vc\controller\web\user\result;

class ResultController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('menu.users'));
        $this->getView()->set('activeMenuitem', 'users');

        // Set default values
        $this->getView()->setHeader('robots', 'noindex, nofollow');
        $this->getView()->set('shortTitle', gettext('menu.users'));

        if (count($this->siteParams) > 1) {
            $cityFilter =  ucfirst(urldecode($this->siteParams[0]));
            $nutritionFilter = ucfirst(urldecode($this->siteParams[1]));

            if ($nutritionFilter == gettext('result.filter.vegetarian') ||
               $nutritionFilter == gettext('result.filter.vegan')) {
                $this->getView()->setHeader('robots', 'index, follow');
                $_GET['searchstring'] = $cityFilter;
                if ($nutritionFilter === gettext('result.filter.vegetarian')) {
                    $_GET['nutrition'] = array(2, 3, 6);
                    $customTitle = gettext('result.filter.vegetarian') . ' ' .
                                   gettext('result.filter.in') . ' ' .
                                  $cityFilter;
                    $this->setTitle($customTitle);
                    $this->getView()->set('shortTitle', $customTitle);
                } else {
                    $_GET['nutrition'] = array(4, 5, 7, 8);
                    $customTitle = gettext('result.filter.vegan') . ' ' .
                                   gettext('result.filter.in') . ' ' .
                                   $cityFilter;
                    $this->setTitle($customTitle);
                    $this->getView()->set('shortTitle', $customTitle);
                }
                $_GET['limit'] = 60;
            }
        }

        // Assertions
//        $request->assertNumericParam('age-from', true, 8, 120);
//        $request->assertNumericParam('age-to', true, 8, 120);
        $request->assertNumericArrayParam('country', true);
//        $request->assertNumericParam('distance', true);
//        $request->assertValidArrayParam('gender', array_keys(\vc\config\Fields::getGenderFields()), true);
        // Using the check for numeric values instead of valid array since users may have old bookmarks
//        $request->assertNumericArrayParam('search', true);
//        $request->assertValidArrayParam('nutrition', array_keys(\vc\config\Fields::getNutritionFields()), true);
//        $request->assertValidArrayParam('smoking', array_keys(\vc\config\Fields::getSmokingFields()), true);
//        $request->assertValidArrayParam('alcohol', array_keys(\vc\config\Fields::getAlcoholFields()), true);
//        $request->assertValidArrayParam('religion', array_keys(\vc\config\Fields::getReligionFields()), true);
//        $request->assertValidArrayParam('zodiac', array_keys(\vc\config\Fields::getZodiacFields()), true);
//        $request->assertValidArrayParam('political', array_keys(\vc\config\Fields::getPoliticalFields()), true);
//        $request->assertValidArrayParam('marital', array_keys(\vc\config\Fields::getMaritalFields()), true);
//        $request->assertValidArrayParam('children', array_keys(\vc\config\Fields::getChildrenFields()), true);
//        $request->assertValidArrayParam('relocate', array_keys(\vc\config\Fields::getRelocateFields()), true);
//        $request->assertValidArrayParam('bodytype', array_keys(\vc\config\Fields::getBodyTypeFields()), true);
//        $request->assertValidArrayParam('bodyheight', array_keys(\vc\config\Fields::getBodyHeightFields()), true);
//        $request->assertValidArrayParam('clothing', array_keys(\vc\config\Fields::getClothingFields()), true);
//        $request->assertValidArrayParam('haircolor', array_keys(\vc\config\Fields::getHairColorFields()), true);
//        $request->assertValidArrayParam('eyecolor', array_keys(\vc\config\Fields::getEyeColorFields()), true);
//        $request->assertNumericParam('index', true, 0);
//        $request->assertNumericParam('limit', true, 1);
//        if (array_key_exists('sort', $_GET)) {
//            \vc\lib\Assert::assertValueInArray(
//                'sort',
//                $_GET['sort'],
//                array('last_login', 'last_update', 'first_entry'),
//                true
//            );
//        }

        // Copies the content of the query
        $requestQuery = $_GET;

        $view = 'list';
        if (count($this->siteParams) > 0 &&
           in_array(strtolower($this->siteParams[0]), array('mail', 'rss', 'short'))) {
            $view = strtolower($this->siteParams[0]);
        }

        if ($view == 'list') {
            $this->setFullPage(true);
        } elseif ($view == 'mail' ||
                  $view == 'rss' ||
                  $view == 'short') {
            $this->setFullPage(false);
        }

        $userSearchService = new \vc\model\service\UserSearchService($this);
        $userSearchService->query(
            $view,
            $request,
            $this->locale,
            $this->getSession()->getProfile(),
            $this->getSession()->isAdmin(),
            $this->getSession()->getSettings()
        );
        $profileIds = $userSearchService->getProfileIds();

        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getSmallProfiles($this->locale, $profileIds);
        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures($this->getSession()->getUserId(), $profiles);

        if ($this->getSession()->hasActiveSession()) {
            $matchModel = $this->getDbModel('Match');
            $matchPercentages = $matchModel->getPercentages($this->getSession()->getUserId(), $profileIds);
        } else {
            $matchPercentages = array();
        }

        $this->getView()->set('actions', $this->getActions($requestQuery, $profiles));

        $this->getView()->set('profileIds', $profileIds);
        $this->getView()->set('profiles', $profiles);
        $this->getView()->set('pictures', $pictures);

        $this->getView()->set('matchPercentages', $matchPercentages);

        $this->getView()->set('requestQuery', $requestQuery);
        $this->getView()->set('hasSearchstring', $this->hasGetParameter('searchstring'));

        $this->getView()->set('savedSearchInterval', $userSearchService->getSavedSearchInterval());
        $this->getView()->set('savedSearchType', $userSearchService->getSavedSearchType());
        $this->getView()->set('savedSearchUrl', $userSearchService->getSavedSearchUrl());

        if ($view == 'list') {
            if (!$this->getSession()->hasActiveSession() &&
                $this->hasGetParameter('searchstring') && trim($_GET ['searchstring']) != '') {
                $this->getView()->set('notificationSearchstring', gettext('result.searchstring.limitation'));
            } else {
                $this->getView()->set('notificationSearchstring', null);
            }
            $this->getView()->set('filterSize', $userSearchService->getRequestLimit());

            $requestQuery['index'] = '%INDEX%';
            $requestQuery['limit'] = '%LIMIT%';
            $pagination = new \vc\object\param\NumericPaginationObject(
                $this->path . 'user/result/?' . implodeQuery($requestQuery),
                $userSearchService->getRequestStart(),
                $userSearchService->getCount(),
                $userSearchService->getRequestLimit()
            );

            $this->getView()->set('pagination', $pagination);

            if (empty($cityFilter)) {
                $this->getView()->setHeader('prev', $pagination->getPrev());
                $this->getView()->setHeader('next', $pagination->getNext());
            }

            if ($userSearchService->getCount() == 0) {
                echo $this->getView()->render('user/result/result.empty', true);
            } else {
                if ($this->getSession()->isAdmin() &&
                    $request->hasParameter('userid') &&
                    $userSearchService->getCount() == 1) {
                    $profileIds = $userSearchService->getProfileIds();
                    throw new \vc\exception\RedirectException($this->path . 'user/view/' . $profileIds[0] . '/');
                }
                echo $this->getView()->render('user/result/result.list', true);
            }
        } elseif ($view == 'mail') {
            echo $this->getView()->render('user/result/result.mail', false);
        } elseif ($view == 'rss') {
            echo $this->getView()->render('user/result/result.rss', false);
        } elseif ($view == 'short') {
            $savedSearchDisplay = $this->getSession()->getSetting(\vc\object\Settings::SAVEDSEARCH_DISPLAY);
            if ($savedSearchDisplay == \vc\object\Settings::SAVEDSEARCH_DISPLAY_PICTURE) {
                echo $this->getView()->render('user/result/result.short.picture', false);
            } elseif ($savedSearchDisplay == \vc\object\Settings::SAVEDSEARCH_DISPLAY_TEXT) {
                echo $this->getView()->render('user/result/result.short.text', false);
            } else {
                echo $this->getView()->render('user/result/result.short.infobox', false);
            }
        } else {
            \vc\lib\ErrorHandler::warning('Invalid view ' . $view, __FILE__, __LINE__);
            throw new \vc\exception\NotFoundException('Invalid view ' . $view);
        }
    }

    private function hasGetParameter($key)
    {
        global $_GET;
        if (array_key_exists($key, $_GET)) {
            if (is_array($_GET[$key])) {
                return count($_GET[$key]) > 0;
            } else {
                return !empty($_GET[$key]);
            }
        } else {
            return false;
        }
    }

    private function getActions($query, $profiles)
    {
        $actions = array();

        $action = new \vc\object\Action();
        $action->setClass('newsearch secondary')
               ->setHref($this->path . 'user/search/')
               ->setCaption(gettext('result.newsearch'));
        $actions[] = $action;

        unset($query['index']);
        unset($query['limit']);
        $action = new \vc\object\Action();
        $action->setClass('editsearch secondary')
               ->setHref($this->path . 'user/search/?' . implodeQuery($query))
               ->setCaption(gettext('result.editsearch'));
        $actions[] = $action;

        if (count($profiles) > 0) {
            $action = new \vc\object\Action();
            $action->setClass('openall secondary')
                   ->setOnclick('vc.ui.openAll();return false;')
                   ->setCaption(gettext('result.openall'));
            $actions[] = $action;
        }

        return $actions;
    }
}
