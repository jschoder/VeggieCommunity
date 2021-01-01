<?php
namespace vc\model;

class CacheModel extends AbstractModel
{
//    const PM_THREADS = 'pm-threads';
//    const PM_MESSAGES = 'pm-messages';
    const JS_CSS = 'JS_CSS';
    const RELATIONS = 'RELATIONS';
        const RELATIONS_FRIENDS_CONFIRMED = 'FRIENDS.CONFIRMED';
        const RELATIONS_FRIENDS_TO_CONFIRM = 'FRIENDS.TO_CONFIRM';
        const RELATIONS_FRIENDS_WAIT_FOR_CONFIRM = 'FRIENDS.WAIT_FOR_CONFIRM';
        const RELATIONS_FAVORITES = 'FAVORITES';
        const RELATIONS_BLOCKED = 'BLOCKED';
        const RELATIONS_GROUPS = 'GROUPS';
        const RELATIONS_EVENTS = 'EVENTS';
    const FORMS = 'FORMS';
    const PAGE = 'PAGE';
    const HOBBIES = 'HOBBIES';
    const COUNTRIES = 'COUNTRIES';
    const SETTINGS = 'SETTINGS';
    const MODULE = 'MODULE';

    public static $expires = array(
        self::JS_CSS => 86400,
        self::RELATIONS => 86400,
        self::FORMS => 86400,
        self::PAGE => 1800,
        self::HOBBIES => 86400,
        self::COUNTRIES => 86400,
        self::SETTINGS => 86400,
    );

    /**
     * @var type \Predis\Client
     */
    private $redis;

    public function __construct()
    {
        try {
            $options = array(
                'profile' => '2.4',
                'prefix'  => 'vc:' . \vc\config\Globals::VERSION . ':',
            );
            $this->redis = new \Predis\Client('tcp://127.0.0.1', $options);
        } catch (\Exception $ex) {
            $this->redis = null;
        }
    }

    public function getProfileRelations($profileId, $reset = false)
    {
        $relations = $this->get(self::RELATIONS . ':' . $profileId);
        if ($reset || $relations === null) {
            $friendModel = $this->getDbModel('Friend');
            $fullFriends = $friendModel->getFullFriendList($profileId);
            $relations = array(
                self::RELATIONS_FRIENDS_CONFIRMED => array(),
                self::RELATIONS_FRIENDS_TO_CONFIRM => array(),
                self::RELATIONS_FRIENDS_WAIT_FOR_CONFIRM => array()
            );
            foreach ($fullFriends as $friend) {
                if ($friend[2] === 1) {
                    if ($friend[0] === $profileId) {
                        $relations[self::RELATIONS_FRIENDS_CONFIRMED][] = $friend[1];
                    } else {
                        $relations[self::RELATIONS_FRIENDS_CONFIRMED][] = $friend[0];
                    }
                } else {
                    if ($friend[0] === $profileId) {
                        $relations[self::RELATIONS_FRIENDS_WAIT_FOR_CONFIRM][] = $friend[1];
                    } else {
                        $relations[self::RELATIONS_FRIENDS_TO_CONFIRM][] = $friend[0];
                    }
                }
            }

            $favoriteModel = $this->getDbModel('Favorite');
            $relations[self::RELATIONS_FAVORITES] = $favoriteModel->getFavoriteIds($profileId);

            $blockedModel = $this->getDbModel('Blocked');
            $relations[self::RELATIONS_BLOCKED] = $blockedModel->getBlocked($profileId);

            $groupModel = $this->getDbModel('Group');
            $relations[self::RELATIONS_GROUPS] = $groupModel->getGroupsByConfirmedMember($profileId);

            $eventModel = $this->getDbModel('Event');
            $relations[self::RELATIONS_EVENTS] = $eventModel->getEventsByParticipant($profileId);
            
            $this->set(
                self::RELATIONS . ':' . $profileId,
                serialize($relations),
                self::$expires[self::RELATIONS]
            );
            return $relations;

        } else {
            return unserialize($relations);
        }
    }

    public function resetProfileRelations($profileId)
    {
        $this->reset(self::RELATIONS . ':' . $profileId);
    }

    public function getJsCssCache($type, $locale, $design, $customKey = null)
    {
        return $this->get($type . '::' . sha1($locale . '-' . $design . '-' . $customKey));
    }

    public function setJsCssCache($type, $locale, $design, $content, $customKey = null)
    {
        $this->set(
            $type . '::' . sha1($locale . '-' . $design . '-' . $customKey),
            $content,
            self::$expires[self::JS_CSS]
        );
    }

    public function setForm($form, $profileId, $ipAddress)
    {
        $contents = array(
            $profileId,
            $ipAddress,
            $form
        );
        $cacheFile = $this->getFormFile($form->getId(), true);
        file_put_contents($cacheFile, serialize($contents));
    }

    public function getForm($formId, $profileId, $ipAddress)
    {
        $cacheFile = $this->getFormFile($formId, false);
        if (file_exists($cacheFile)) {
            $fileContents = file_get_contents($cacheFile);
            $contents = unserialize($fileContents);
            // Either the ip or the user has to be the same
            if ($contents[0] === $profileId ||
                $contents[1] === $ipAddress) {
                return $contents[2];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private function getFormFile($formId, $createDirectory)
    {
        if ($createDirectory) {
            if (!file_exists(CACHE_DIR)) {
                mkdir(CACHE_DIR);
            }
            if (!file_exists(CACHE_DIR . '/forms/')) {
                mkdir(CACHE_DIR . '/forms/');
            }
        }
        return CACHE_DIR . '/forms/' . $formId . '.tmp';
    }

    public function getModuleCache($cacheName, $cacheKey)
    {
        return $this->get(self::MODULE . ':' . $cacheName . '-' . $cacheKey);
    }

    public function setModuleCache($cacheName, $cacheKey, $content, $cacheExpires)
    {
        return $this->set(
            self::MODULE . ':' . $cacheName . '-' . $cacheKey,
            $content,
            $cacheExpires
        );
    }

    public function setResetModuleCache($cacheName, $cacheKey)
    {
        $this->reset(self::MODULE . ':' . $cacheName . '-' . $cacheKey);
    }

    public function getPageCache($url, $design)
    {
        $cacheFile = $this->getPageFile($url, $design, false);
        if (file_exists($cacheFile)) {
            $lastModification = filemtime($cacheFile);
            if ($lastModification + self::$expires[self::PAGE] < time()) {
                return null;
            } else {
                return file_get_contents($cacheFile);
            }
        } else {
            return null;
        }
    }

    public function setPageCache($url, $design, $content)
    {
        $cacheFile = $this->getPageFile($url, $design, true);
        file_put_contents($cacheFile, $content);
    }

    private function getPageFile($url, $design, $createDirectory)
    {
        if ($createDirectory) {
            if (!file_exists(CACHE_DIR)) {
                mkdir(CACHE_DIR);
            }
            if (!file_exists(CACHE_DIR . '/pages/')) {
                mkdir(CACHE_DIR . '/pages/');
            }
        }
        return CACHE_DIR . '/pages/' . sha1($url) . '-' . $design . '-' . \vc\config\Globals::VERSION . '.tmp';
    }

    public function getHobbies($locale)
    {
        $hobbies = $this->get(self::HOBBIES . ':' . $locale);
        if ($hobbies === null) {
            $hobbyModel = $this->getDbModel('Hobby');
            $hobbies = $hobbyModel->getIndexedHobbies($locale);
            $this->set(
                self::HOBBIES . ':' . $locale,
                serialize($hobbies),
                self::$expires[self::HOBBIES]
            );
            return $hobbies;
        } else {
            return unserialize($hobbies);
        }
    }

    public function getCountries($locale, $ip = null, $defaultCountries = false)
    {
        if (empty($ip)) {
            $ipCountry = null;
        } else {
            $geoIpModel = $this->getDbModel('GeoIp');
            $ipCountry = $geoIpModel->getCountryByIp($ip);
        }

        $cacheKey = self::COUNTRIES . ':' . $locale . '-' . $ipCountry . '-' . ($defaultCountries ? '1' : '0');
        $cachedCountries = $this->get($cacheKey);
        if ($cachedCountries === null) {
            $countryModel = $this->getDbModel('Country');
            $countries = $countryModel->getCountries($locale, $ipCountry, $defaultCountries);
            $this->set(
                $cacheKey,
                serialize($countries),
                self::$expires[self::COUNTRIES]
            );
            return $countries;
        } else {
            return unserialize($cachedCountries);
        }
    }

    public function getSettings($profileId)
    {
        $cachedSettings = $this->get(self::SETTINGS . ':' . $profileId);
        if ($cachedSettings === null) {
            $settingsModel = $this->getDbModel('Settings');
            $settings = $settingsModel->getSettings($profileId);
            $this->set(
                self::SETTINGS . ':' . $profileId,
                serialize($settings),
                self::$expires[self::SETTINGS]
            );
            return $settings;
        } else {
            return unserialize($cachedSettings);
        }
    }

    public function resetSettingsCache($profileId)
    {
        $this->reset(self::SETTINGS . ':' . $profileId);
    }

    public function set($key, $value, $expire = null)
    {
        try {
            if ($this->redis !== null) {
                $this->redis->set($key, $value);
                if(!empty($expire)) {
                    $this->redis->expire($key, $expire);
                }
            }
        } catch (\Exception $e) {
            \vc\lib\ErrorHandler::error(
                'Can\'t set redis cache.',
                __FILE__,
                __LINE__,
                array(
                    'key' => $key,
                    'value' => $value,
                    'expire' => $expire,
                    'exception.class' => get_class($e),
                    'exception.message' => $e->getMessage(),
                    'exception.trace' => var_export($e->getTraceAsString(), true)
                )
            );
        }
    }

    public function get($key)
    {
        try {
            if ($this->redis === null) {
                return null;
            } else {
                return $this->redis->get($key);
            }
        } catch (\Exception $e) {
            \vc\lib\ErrorHandler::error(
                'Can\'t get redis cache.',
                __FILE__,
                __LINE__,
                array(
                    'key' => $key,
                    'exception.class' => get_class($e),
                    'exception.message' => $e->getMessage(),
                    'exception.trace' => var_export($e->getTraceAsString(), true)
                )
            );
            return null;
        }
    }

    private function reset($keys)
    {
        try {
            if ($this->redis !== null) {
                $this->redis->del($keys);
            }
        } catch (\Exception $e) {
            \vc\lib\ErrorHandler::error(
                'Can\'t reset redis cache.',
                __FILE__,
                __LINE__,
                array(
                    'keys' => $keys,
                    'exception.class' => get_class($e),
                    'exception.message' => $e->getMessage(),
                    'exception.trace' => var_export($e->getTraceAsString(), true)
                )
            );
        }
    }
}
