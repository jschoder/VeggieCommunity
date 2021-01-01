<?php
namespace vc\controller;

class ControllerFactory
{
    private $server;

    private $db;

    private $modelFactory;

    private $componentFactory;

    public function __construct()
    {
        if ($_SERVER['SERVER_NAME'] == 'www.veggiecommunity.dev') {
            $this->server = 'local';
        } else {
            $this->server = 'live';
        }

        $this->db = new \vc\model\db\DbConnection(
            $this->server
        );
        $this->modelFactory = new \vc\model\ModelFactory(
            $this->db,
            $this->server
        );
        $this->componentFactory = new \vc\component\ComponentFactory(
            $this->db,
            $this->server,
            $this->modelFactory
        );
    }

    public function getController()
    {
        // Loading the correct serveraddress
        if (strrpos($_SERVER['REQUEST_URI'], '?') === false) {
            $requestUriWithoutParams = $_SERVER['REQUEST_URI'];
        } else {
            $requestUriWithoutParams = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
        }

        // Basic redirect checks (https / www / ending with slash)
        if (!array_key_exists('HTTPS', $_SERVER) ||
            $_SERVER['HTTPS'] != 'on' ||
            stripos($_SERVER['SERVER_NAME'], 'www.') !== 0 ||
            empty($requestUriWithoutParams) ||
            (
                substr($requestUriWithoutParams, -1) !== '/' &&
                substr($requestUriWithoutParams, -4) !== '.css' &&
                substr($requestUriWithoutParams, -3) !== '.js' &&
                substr($requestUriWithoutParams, -4) !== '.jpg' &&
                substr($requestUriWithoutParams, -4) !== '.xml'
            )) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_URL, true);
                throw new \vc\exception\RedirectException('/en/locked/');
            } else {
                // Force redirect only on GET
                $redirectUrl = 'https://';
                if (stripos($_SERVER['SERVER_NAME'], 'www.') !== 0) {
                    $redirectUrl .= 'www.';
                }
                $redirectUrl .= $_SERVER['SERVER_NAME'];

                if (empty($requestUriWithoutParams)) {
                    $redirectUrl . '/' . $this->getDefaultLocale() . '/';
                } else {
                    $redirectUrl .= $requestUriWithoutParams;

                    if (substr($requestUriWithoutParams, -1) !== '/' &&
                        substr($requestUriWithoutParams, -4) !== '.css' &&
                        substr($requestUriWithoutParams, -3) !== '.js' &&
                        substr($requestUriWithoutParams, -4) !== '.jpg' &&
                        substr($requestUriWithoutParams, -4) !== '.xml') {
                        $redirectUrl .= '/';
                    }
                }

                if (!empty($_SERVER['QUERY_STRING'])) {
                    $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
                }

                throw new \vc\exception\RedirectException($redirectUrl, true);
            }
        }

        if (strpos($requestUriWithoutParams, '..') !== false) {
            $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_DOUBLE_POINT_URL, true);
            throw new \vc\exception\RedirectException('/en/locked/');
        }

        if ($requestUriWithoutParams === '' ||
            $requestUriWithoutParams === '/') {
            $siteParams = array();
        } else {
            $siteParams = explode('/', $requestUriWithoutParams);
            // Removing the first element which is always empty
            // (since the url starts with a slash)
            array_shift($siteParams);
        }

        // Remove empty param at the end
        if (count($siteParams) > 0 && $siteParams[count($siteParams) - 1] == '') {
            unset($siteParams[count($siteParams) - 1]);
        }

        // Path without any subdirectories is invalid. At least the locale (de/en) has to be set.
        if (count($siteParams) == 0) {
            throw new \vc\exception\RedirectException('/' . $this->getDefaultLocale() . '/', true);
        }

        if ($this->isLocaleRequired($requestUriWithoutParams)) {
            $routing = \vc\config\Routing::getLocaleRouting();

            // Read the correct locale from the path and set it to the system
            $locale = array_shift($siteParams); // Remove the first element

            if (!in_array($locale, array('de', 'en'))) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_URL, true);
                    throw new \vc\exception\RedirectException('/en/locked/');
                } else {
                    $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_INVALID_ROUTING, false);
                    throw new \vc\exception\RedirectException('/en/404/', true);
                }
            }

            if (empty($siteParams) ||
                !in_array($siteParams[0], array('404', '500', 'css', 'js'))) {
                // Store current locale for one year if new or changed
                setcookie('locale', $locale, time() + 31536000, '/');
            }

            $path = '/' . $locale . '/';
        } else {
            $routing = \vc\config\Routing::getNoLocaleRouting();
            // Using fixed value since it shouldn't be used at all
            $locale = 'en';
            $path = '/';
        }

        if (array_key_exists('HTTP_USER_AGENT', $_SERVER) &&
           (strpos($_SERVER['HTTP_USER_AGENT'], 'DTS Agent') !== false ||
           (strpos($_SERVER['HTTP_USER_AGENT'], 'Python-urllib/') !== false))) {
            // SpamBot

            // :TODO: migrator2 - move this
            $site = '500';
        } else {
            // Read the selected site (e.g. start, edit, search, result, ...)
            if (count($siteParams) == 0) {
                $site = 'start';
            } else {
                $site = strtolower(array_shift($siteParams));
            }
        }

        if (array_key_exists($site, $routing)) {
            $routingValue = $routing[$site];
            while (is_array($routingValue)) {
                if (count($siteParams) > 0 &&
                   array_key_exists(strtolower($siteParams[0]), $routingValue)) {
                    $subSite = strtolower(array_shift($siteParams));
                    $site = $site . '/' . $subSite;
                    $routingValue = $routingValue[$subSite];
                } elseif (array_key_exists('#default', $routingValue)) {
                    $routingValue = $routingValue['#default'];
                } else {
                    $routingValue = null;
                }
            }

            if ($routingValue === null) {
                $controller = new web\NotFoundController();
            } elseif (strpos($routingValue, '@redirect::') === 0) {
                foreach ($siteParams as $i => $siteParam) {
                    $routingValue = str_replace('{' . $i . '}', $siteParam, $routingValue);
                }

                // Trying to get a redirect url without all necessary parameters
                if (stripos($routingValue, '{') !== false) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_URL, true);
                        throw new \vc\exception\RedirectException('/en/locked/');
                    } else {
                        $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_INVALID_ROUTING, false);
                        throw new \vc\exception\RedirectException('/en/404/', true);
                    }
                } else {
                    // Getting the new target-url
                    $redirectUrl = $path . substr($routingValue, 11);

                    $redirectModel = $this->modelFactory->getDbModel('Redirect');
                    $redirectModel->addRedirect(substr($requestUriWithoutParams, 3));

                    // Attaching all get-parameters from the current page to the redirect
                    if (strlen($_SERVER['REQUEST_URI']) > strlen($requestUriWithoutParams)) {
                        $redirectUrl .= substr($_SERVER['REQUEST_URI'], strlen($requestUriWithoutParams));
                    }
                    throw new \vc\exception\RedirectException($redirectUrl, true);
                }
            } else {
                $controller = new $routingValue;
            }
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_URL, true);
                throw new \vc\exception\RedirectException('/en/locked/');
            } else {
                $this->blockInvalidRequest(\vc\model\db\SuspicionDbModel::TYPE_INVALID_ROUTING, false);
                throw new \vc\exception\RedirectException('/en/404/', true);
            }
        }

        $controller->setup(
            $this->db,
            $this->modelFactory,
            $this->componentFactory,
            $this->server,
            $path,
            $site,
            $siteParams,
            $locale
        );

        return $controller;
    }

    private function isLocaleRequired($path)
    {
        foreach (\vc\config\Routing::getNoLocaleFilter() as $pattern) {
            if (fnmatch($pattern, $path)) {
                return false;
            }
        }
        return true;
    }

    private function getDefaultLocale()
    {
        $sessionModel = $this->modelFactory->getModel('Session');
        if ($sessionModel->hasActiveSession()) {
            $lang = $sessionModel->getSetting(\vc\object\Settings::USER_LANGUAGE);
            if (!empty($lang) && in_array($lang, array('de','en'))) {
                return $lang;
            }
        }

        if (!empty($_COOKIE['locale']) &&
            in_array($_COOKIE['locale'], array('de', 'en'))) {
            return $_COOKIE['locale'];
        } elseif (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            $locales = explode(';', str_replace(',', ';', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
            foreach ($locales as $locale) {
                if (stripos($locale, 'en') || $locale=='en') {
                    return 'en';
                } elseif (stripos($locale, 'de') || $locale=='de') {
                    return 'de';
                }
            }
            return 'en';
        } else {
            return 'en';
        }
    }

    private function blockInvalidRequest($suspicionType, $extendedDebugData)
    {
        if ($extendedDebugData) {
            $debugData = array(
                'SERVER' => var_export($_SERVER, true)
            );
        } else {
            $debugData = array(
                'URI' => $_SERVER['REQUEST_URI']
            );
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $debugData['REFERER'] = $_SERVER['HTTP_REFERER'];
            }
        }

        $sessionModel = $this->modelFactory->getModel('Session');
        $suspicionModel = $this->modelFactory->getDbModel('Suspicion');
        $suspicionModel->addSuspicion(
            $sessionModel->getUserId(),
            $suspicionType,
            \vc\helper\RequestHelper::getIp(),
            $debugData
        );
    }
}
