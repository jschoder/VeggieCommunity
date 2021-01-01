<?php
namespace vc\controller\web;

abstract class AbstractWebController extends \vc\controller\AbstractController
{
    const NOTIFICATION_INFO = 'notifyInfo';
    const NOTIFICATION_SUCCESS = 'notifySuccess';
    const NOTIFICATION_WARNING = 'notifyWarn';
    const NOTIFICATION_ERROR = 'notifyError';

    protected $design;

    /**
     * @var \vc\design\AbstractDesignConfig
     */
    protected $designConfig;

    protected $imagesPath;

    private $usersOnline = null;

    private $isFullPage = true;

    private $view = null;

    private $friendsConfirmed = null;

    private $friendsToConfirm = null;

    private $friendsWaitForConfirm = null;

    private $favorites = null;

    private $blocked = null;

    public function setup($db, $modelFactory, $componentFactory, $server, $path, $site, $siteParams, $locale)
    {
        parent::setup($db, $modelFactory, $componentFactory, $server, $path, $site, $siteParams, $locale);

        if (array_key_exists('HTTP_REFERER', $_SERVER) && !empty($_SERVER['HTTP_REFERER'])) {
            // Save referering site to improve marketing for registered user
            if (!strpos($_SERVER['HTTP_REFERER'], 'veggiecommunity.org')) {
                $_SESSION['REGISTRATION_REFERER'] = $_SERVER['HTTP_REFERER'];
            }
        }

        $this->design = $this->getDesign();
        $designConfigClass = '\vc\design\\' . $this->design . '\DesignConfig';
        $this->designConfig = new $designConfigClass;

        $this->imagesPath = '/img/' . $this->design . '/';

        if ($this->getSession()->hasActiveSession()) {
            $cacheModel = $this->getModel('Cache');
            $relations = $cacheModel->getProfileRelations($this->getSession()->getUserId());

            $this->friendsConfirmed = $relations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED];
            $this->friendsToConfirm = $relations[\vc\model\CacheModel::RELATIONS_FRIENDS_TO_CONFIRM];
            $this->friendsWaitForConfirm = $relations[\vc\model\CacheModel::RELATIONS_FRIENDS_WAIT_FOR_CONFIRM];
            $this->favorites = $relations[\vc\model\CacheModel::RELATIONS_FAVORITES];
            $this->blocked = $relations[\vc\model\CacheModel::RELATIONS_BLOCKED];
            $this->ownGroups = $relations[\vc\model\CacheModel::RELATIONS_GROUPS];
            $this->ownEvents = $relations[\vc\model\CacheModel::RELATIONS_EVENTS];
        } else {
            $this->friendsConfirmed = array();
            $this->friendsToConfirm = array();
            $this->friendsWaitForConfirm = array();
            $this->favorites = array();
            $this->blocked = array();
            $this->ownGroups = array();
            $this->ownEvents = array();
        }
    }

    private function getDesign()
    {
        $design = null;
        if (array_key_exists('DESIGN', $_GET)) {
            // Manually overwrite design to open via testcases
            $design = $_GET['DESIGN'];
        } elseif ($this->getSession()->hasActiveSession()) {
            $design = $this->getSession()->getSetting(\vc\object\Settings::DESIGN);
            if (!empty($design)) {
                setcookie('DESIGN', $design, time() + 31536000, '/');
            }
        } elseif (array_key_exists('DESIGN', $_COOKIE)) {
            $design = $_COOKIE['DESIGN'];
        }

        // Falling back old or invalid designs
        if (empty($design) ||
           !in_array($design, array('lemongras', 'matcha'))) {
            $design = 'matcha';
        }
        return $design;
    }

    public function dispatch()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Second method will do the actual login
                if (!$this->getSession()->hasActiveSession() && $this->getSession()->hasCookieLogin()) {
                    if ($this->site != 'css' && $this->site != 'js') {
                        $this->getSession()->performLogin($this->locale, $this->getIp());
                    }
                }

                // Caching get-pages for one hour if parameter is set to true
                if ($this->cacheGet() && !$this->getSession()->hasActiveSession() && $this->getServer() == 'live') {
                    $cacheModel = $this->getModel('Cache');
                    $pageContent = $cacheModel->getPageCache($_SERVER['REQUEST_URI'], $this->getDesign());
                    if ($pageContent === null) {
                        ob_start();
                        $this->preGetHook();
                        $this->handleGet(new \vc\controller\Request($_GET));
                        $pageContent = ob_get_contents();
                        ob_end_clean();
                        $cacheModel->setPageCache($_SERVER['REQUEST_URI'], $this->getDesign(), $pageContent);
                    }
                    echo $pageContent;
                } else {
                    $this->preGetHook();
                    $this->handleGet(new \vc\controller\Request($_GET));
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePost(new \vc\controller\Request($_POST));
            } else {
                header('HTTP/1.0 405 Method Not Allowed');
                die();
            }
        } catch (\vc\exception\RedirectException $exception) {
            // Handled further down the road
            throw $exception;
        } catch (\vc\exception\LoginRequiredException $exception) {
            $controller = new LoginController();
            $controller->setup(
                $this->db,
                $this->modelFactory,
                $this->componentFactory,
                $this->server,
                $this->path,
                'login',
                array(),
                $this->locale
            );
            $controller->getView()->setHeader(
                'canonical',
                'https://www.veggiecommunity.org/' . $this->locale . '/login/'
            );
            $controller->getView()->set('loginTargetUrl', $_SERVER['REQUEST_URI']);
            $controller->dispatch();
        } catch (\vc\exception\NotFoundException $exception) {
            $controller = new NotFoundController();
            $controller->setup(
                $this->db,
                $this->modelFactory,
                $this->componentFactory,
                $this->server,
                $this->path,
                $this->site,
                $this->siteParams,
                $this->locale
            );
            $controller->dispatch();
        } catch (\vc\exception\DBConnectionFailedException $exception) {
            header('HTTP/1.1 500 Internal Server Error');
            require(TEMPLATE_DIR . '/dbfailed.tpl.php');
        } catch (\vc\exception\AssertionException $exception) {
            $stacktrace = '';
            foreach ($exception->getTrace() as $line) {
                $stacktrace .= $line['file']. ' : ' . $line['line'] . "\n";
            }

            if ($this->getServer() == 'local') {
                \vc\lib\ErrorHandler::error(
                    'Failed Exception: ' . $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    array(
                        'trace' => trim($stacktrace)
                    )
                );
            } else {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_FAILED_ASSERTION,
                    array(
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => trim($stacktrace)
                    )
                );
            }

            $controller = new InternalServerErrorController();
            $controller->setup(
                $this->db,
                $this->modelFactory,
                $this->componentFactory,
                $this->server,
                $this->path,
                $this->site,
                $this->siteParams,
                $this->locale
            );
            $controller->dispatch();
        } catch (\Exception $exception) {
            \vc\lib\ErrorHandler::error(
                get_class($exception) . ' :: ' . $exception->getMessage(),
                __FILE__,
                __LINE__,
                array('stacktrace' => var_export($exception->getTraceAsString(), true))
            );
            $controller = new InternalServerErrorController();
            $controller->setup(
                $this->db,
                $this->modelFactory,
                $this->componentFactory,
                $this->server,
                $this->path,
                $this->site,
                $this->siteParams,
                $this->locale
            );
            $controller->dispatch();
        }

        if ($this->logPageView()) {
            $this->addPageViewLog($this->getSession()->getUserId());
        }
    }

    protected function preGetHook()
    {
        $this->saveFullDebug(\vc\object\FullLog::GET, $_GET);
        $this->getSession()->updateOnlinestatus();
    }

    public function handleGet(\vc\controller\Request $request)
    {
        // Use as a flag to reduce spam
        if (!empty($_POST)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GET_REQUEST,
                array(
                    'GET' => $_GET,
                    'POST' => $_POST
                )
            );
        }
        header('HTTP/1.0 405 Method Not Allowed');
    }

    protected function prePostHook()
    {
        $this->saveFullDebug(\vc\object\FullLog::POST, $_POST);
        $this->getSession()->updateOnlinestatus();
    }

    public function handlePost(\vc\controller\Request $request)
    {
        // Use as a flag to reduce spam
        if (!empty($_GET)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'GET' => $_GET,
                    'POST' => $_POST
                )
            );
        }
        header('HTTP/1.0 405 Method Not Allowed');
    }

    private function saveFullDebug($method, $data)
    {
        if (!empty($_COOKIE['debug']) && $_COOKIE['debug'] === 'true') {
            // Don't log certain urls

            if (!($this instanceof CssController) &&
                !($this instanceof JsController) &&
                !($this instanceof PictureController) &&
                !($this instanceof account\StatusController)
            ) {
                if (strrpos($_SERVER['REQUEST_URI'], '?') === false) {
                    $requestUriWithoutParams = $_SERVER['REQUEST_URI'];
                } else {
                    $requestUriWithoutParams = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
                }

                $fullLog = new \vc\object\FullLog();
                $fullLog->profileId = $this->getSession()->getUserId();
                $fullLog->ip = $this->getIp();
                $fullLog->url = $requestUriWithoutParams;
                $fullLog->parameters = var_export($data, true);
                $fullLog->method = $method;

                $fullLogModel = $this->getDbModel('FullLog');
                $fullLogModel->insertObject(null, $fullLog);
            }
        }
    }

    protected function cacheGet()
    {
        return false;
    }

    protected function setFullPage($isFullPage)
    {
        $this->isFullPage = $isFullPage;
    }

    /**
     * @return \vc\view\html\View
     */
    protected function getView()
    {
        if ($this->view === null) {
            $this->view = new \vc\view\html\View($this->design, $this->designConfig);
            $this->view->set('path', $this->path);
            $this->view->set('site', $this->site);
            $this->view->set('siteParams', $this->siteParams);
            $this->view->set('locale', $this->locale);
            $this->view->set('imagesPath', $this->imagesPath);
            $this->view->set('version', \vc\config\Globals::VERSION);

            $noLocalePath = '';
            if ($this->site !== 'start') {
                $noLocalePath .= $this->site . '/';
            }
            if (!empty($this->siteParams)) {
                $noLocalePath .= implode('/', $this->siteParams) . '/';
            }
            if (!empty($_SERVER['QUERY_STRING'])) {
                $noLocalePath .= '?' . str_replace('&', '&amp;', $_SERVER['QUERY_STRING']);
            }
            $this->view->set('noLocalePath', $noLocalePath);

            if (!empty($_GET['notification'])) {
                $this->view->set('notification', $this->getNotification($_GET['notification']));
            } elseif ($this->getSession()->hasActiveSession() &&
                      $this->getSession()->getSetting(\vc\object\Settings::USER_LANGUAGE) !== $this->locale) {
                $locale = $this->getSession()->getSetting(\vc\object\Settings::USER_LANGUAGE);
                $this->view->set(
                    'notification',
                    array(
                        'type' => self::NOTIFICATION_INFO,
                        'message' => str_replace(
                            '%URL%',
                            '/' . $locale . '/' . substr($_SERVER['REQUEST_URI'], strlen($this->path)),
                            gettext('switchLocale.' . $locale)
                        )
                    )
                );
            } else {
                $this->view->set('notification', false);
            }

            $currentUser = $this->getSession()->getProfile();
            $this->view->set('currentUser', $currentUser);
            $this->view->set('plusLevel', $this->getSession()->getPlusLevel());
            $this->view->set('isAdmin', $this->getSession()->isAdmin());
            $this->view->set('sessionSettings', $this->getSession()->getSettings());
            $this->view->set('websocketKey', $this->getSession()->getWebsocketKey());
            $this->view->set('usersOnline', $this->getUsersOnline());

            if (isset($_SERVER['HTTP_REFERER'])) {
                $this->view->set('referer', $_SERVER['HTTP_REFERER']);
            }

            if ($this->favorites === null) {
                $this->view->set('ownFavorites', array());
            } else {
                $this->view->set('ownFavorites', $this->favorites);
            }
            if ($this->friendsConfirmed === null) {
                $this->view->set('ownFriendsConfirmed', array());
            } else {
                $this->view->set('ownFriendsConfirmed', $this->friendsConfirmed);
            }
            if ($this->friendsToConfirm === null) {
                $this->view->set('ownFriendsToConfirm', array());
            } else {
                $this->view->set('ownFriendsToConfirm', $this->friendsToConfirm);
            }
            if ($this->friendsWaitForConfirm === null) {
                $this->view->set('ownFriendsWaitForConfirm', array());
            } else {
                $this->view->set('ownFriendsWaitForConfirm', $this->friendsWaitForConfirm);
            }
            if ($this->blocked === null) {
                $this->view->set('blocked', array());
            } else {
                $this->view->set('blocked', $this->blocked);
            }
            if ($this->ownGroups === null) {
                $this->view->set('ownGroups', array());
            } else {
                $this->view->set('ownGroups', $this->ownGroups);
            }
            if ($this->ownEvents === null) {
                $this->view->set('ownEvents', array());
            } else {
                $this->view->set('ownEvents', $this->ownEvents);
            }

            if ($this->isFullPage) {
                if ($currentUser !== null) {
                    $pictureModel = $this->getDbModel('Picture');
                    $ownPictureArray = $pictureModel->readPictures(
                        $currentUser->id,
                        array($currentUser)
                    );
                    $this->getView()->set('ownPicture', $ownPictureArray[$currentUser->id]);

                    $pollModel = $this->getDbModel('Poll');
                    $polls = $pollModel->loadPolls($this->locale, $currentUser);
                    $this->view->set('polls', $polls);
                } else {
                    $this->view->set('polls', array());
                    $this->getView()->set('ownPicture', null);
                }

                $this->view->set('loginTargetUrl', '/');

                $this->setTitle(null);

                $this->view->setHeader('description', gettext('header.meta.description'));
                $this->view->setHeader('keywords', gettext('header.meta.keywords'));
                $this->view->setHeader('robots', 'index, follow');
                $this->view->setHeader('image', 'https://www.veggiecommunity.org/img/logo-200x200.png');
                $this->view->setHeader('ogType', 'website');

                if ($this->getSession()->hasActiveSession()) {
//                    $pmThreadModel = $this->getDbModel('PmThread');
//                    $this->view->set(
//                        'newMessages',
//                        $pmThreadModel->getUnreadThreads($this->getSession()->getUserId())
//                    );

// :TODO: JOE - use or kill
                    $pmModel = $this->getDbModel('Pm');
                    $this->view->set(
                        'newMessages',
                        $pmModel->getCount(
                            array(
                                'recipientid' => $this->getSession()->getUserId(),
                                'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_NEW
                            )
                        )
                    );
                    $this->view->set(
                        'openFriendRequests',
                        count($this->friendsToConfirm)
                    );

                    $groupNotificationModel = $this->getDbModel('GroupNotification');
                    $this->view->set(
                        'groupNotifications',
                        $groupNotificationModel->getNotificationsCount($this->getSession()->getUserId())
                    );

                    if ($this->design === 'lemongras') {
                        $this->view->set('ticketNotifications', 0);
                    } else {
                        $helpNotificationModel = $this->getDbModel('HelpNotification');
                        $this->view->set(
                            'ticketNotifications',
                            $helpNotificationModel->getCount(
                                array(
                                    'profile_id' => $this->getSession()->getUserId()
                                )
                            )
                        );
                    }

                    if ($this->getSession()->isAdmin()) {
                        $ticketModel = $this->getDbModel('Ticket');
                        $this->view->set(
                            'modOpenTickets',
                            $ticketModel->getCount(array('status' => \vc\object\Ticket::STATUS_OPEN))
                        );
#
                        $pmModel = $this->getDbModel('Pm');
                        $this->view->set(
                            'modSpam',
                            $pmModel->getSpamCount()
                        );

                        $flagModel = $this->getDbModel('Flag');
                        $this->view->set(
                            'modFlag',
                            $flagModel->getCount(
                                array(
                                    'aggregate_type !=' => \vc\config\EntityTypes::GROUP_FORUM,
                                    'processed_at IS NULL'
                                )
                            )
                        );

                        $realCheckModel = $this->getDbModel('RealCheck');
                        $this->view->set(
                            'modSubmittedReals',
                            $realCheckModel->getCount(
                                array(
                                    'status' => array(
                                        \vc\object\RealCheck::STATUS_SUBMITTED,
                                        \vc\object\RealCheck::STATUS_REOPENED
                                    )
                                )
                            )
                        );

                        $groupModel = $this->getDbModel('Group');
                        $this->view->set(
                            'modUnconfirmedGroups',
                            $groupModel->getCount(
                                array(
                                    'confirmed_at IS NULL',
                                    'deleted_at IS NULL'
                                )
                            )
                        );

                        $pmModel = $this->getDbModel('Pm');
                        $this->view->set(
                            'modUnsentMessages',
                            $pmModel->getCount(array('recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_UNSENT))
                        );

                        $pictureChecklistModel = $this->getDbModel('PictureChecklist');
                        $this->view->set(
                            'modPicsUnchecked',
                            $pictureChecklistModel->getCount(array())
                        );

                        $pictureWarningModel = $this->getDbModel('PictureWarning');
                        $this->view->set(
                            'modPicsPrewarned',
                            $pictureWarningModel->getCount(
                                array(
                                    'own_pic_confirmed_at IS NULL',
                                    'deleted_at IS NULL'
                                ),
                                array(
                                    'INNER JOIN vc_picture ON vc_picture.id = vc_picture_warning.picture_id',
                                    'INNER JOIN vc_profile ON vc_profile.id = vc_picture_warning.profile_id
                                                AND vc_profile.active > 0'
                                )
                            )
                        );

                        $toldafriendModel = $this->getDbModel('Toldafriend');
                        $this->view->set(
                            'modToldafriend',
                            $toldafriendModel->getCount(array('is_sent' => 0))
                        );
                    }
                } else {
                    $this->view->set('newMessages', 0);
                    $this->view->set('openFriendRequests', 0);
                    $this->view->set('groupNotifications', 0);
                    $this->view->set('ticketNotifications', 0);
                }

                if ($this->designConfig->hasBlocks()) {
                    $blockModuleService = new \vc\model\service\BlockModuleService(
                        $this->modelFactory,
                        $this->componentFactory
                    );
                    $this->getView()->set(
                        'blocks',
                        $blockModuleService->getBlocks(
                            $this,
                            $this->view,
                            $this->site
                        )
                    );
                }

                // Reading the timezoneOffset from the cookie (if JavaScript is active)
                if (array_key_exists('TZoff', $_COOKIE)) {
                    $cookieOffset = $_COOKIE['TZoff'];
                    $timezone  = new \DateTimeZone('Europe/Berlin');
                    $offset = $timezone->getOffset(date_create());
                    $timeOffset = $cookieOffset + ($offset / 60);
                    $this->getView()->setTimeOffset($timeOffset);
                }
                if ($this->design !== 'lemongras') {
                    $this->view->set('greeting', $this->getGreeting());
                }
            }
        }
        return $this->view;
    }

    private function getGreeting()
    {
        $greetings = array();
        $day = date('m-d');
        $hour = intval(date('H'));
        if ($this->locale == 'de') {
            if ($day === '05-04') {
                $greetings[] = 'May the fourth be with you!';
            } elseif ($day === '05-25') {
                $greetings[] = 'Don\'t panic! Und schnappe dir ein Handtuch.';
            } else {
                $greetings[] = 'Ich wünsche dir einen tollen Tag!';
                $greetings[] = 'Servus und grüß Gott!';
                $greetings[] = 'Wie gehts, wie stehts?';
                $greetings[] = 'Wohin des Wegs?';
                $greetings[] = 'Gott zum Gruße!';
                $greetings[] = 'Habe die Ehre!';
                $greetings[] = 'Sei mir gegrüßt!';
                $greetings[] = 'Griaß Di!';
                $greetings[] = 'Moinmoin!';
                $greetings[] = 'Grüezi miteinand\'';
                $greetings[] = 'Aloha!';

                // Multiple values to make it more likely to display
                if ($hour === 11 || $hour === 12) {
                    $greetings[] = 'Mahlzeit!';
                    $greetings[] = 'Mahlzeit!';
                    $greetings[] = 'Mahlzeit!';
                    $greetings[] = 'Mahlzeit!';
                } elseif ($hour === 20 || $hour === 21 || $hour === 22) {
                    $greetings[] = 'Guten Abend!';
                    $greetings[] = 'Guten Abend!';
                    $greetings[] = 'Guten Abend!';
                    $greetings[] = '\'n Abend!';
                    $greetings[] = '\'n Abend!';
                    $greetings[] = '\'n Abend!';
                } elseif ($hour === 23 || $hour === 0 || $hour === 1 || $hour == 2 || $hour == 3) {
                    $greetings[] = 'Carpe noctem!';
                    $greetings[] = 'Carpe noctem!';
                    $greetings[] = 'Carpe noctem!';
                    $greetings[] = 'Carpe noctem!';
                }
            }

        } else {
            // Default to english
            if ($day === '05-04') {
                $greetings[] = 'May the fourth be with you!';
            } elseif ($day === '05-25') {
                $greetings[] = 'Don\'t panic! Just grab a towel.';
            } elseif ($day === '09-19') {
                $greetings[] = 'Happy talk like a pirate day!';
            } else {
                $greetings[] = 'Have an awesome day!';
                $greetings[] = 'How are you?';
                $greetings[] = 'What’s up?';
                $greetings[] = 'Good to see you!';
                $greetings[] = 'Nice to see you!';
                $greetings[] = 'How\'s it going?';
                $greetings[] = 'G\'day mate!';
                $greetings[] = 'Whazzup?';
                $greetings[] = 'Howdy!';
                $greetings[] = 'Aloha!';

                if ($hour === 20 || $hour === 21 || $hour === 22) {
                    $greetings[] = 'Good evening!';
                    $greetings[] = 'Good evening!';
                    $greetings[] = 'Good evening!';
                } elseif ($hour === 23 || $hour === 0 || $hour === 1 || $hour == 2 || $hour == 3) {
                    $greetings[] = 'Carpe noctem!';
                    $greetings[] = 'Carpe noctem!';
                    $greetings[] = 'Carpe noctem!';
                    $greetings[] = 'Carpe noctem!';
                }
            }
        }

        $count = count($greetings);
        if ($greetings === 1) {
            return $greetings[0];
        } else {
            return $greetings[rand(0, $count -1)];
        }
    }

    protected function getOwnFavorites()
    {
        return $this->favorites;
    }

    protected function getOwnFriendsConfirmed()
    {
        return $this->friendsConfirmed;
    }

    protected function getOwnFriendsToConfirm()
    {
        return $this->friendsToConfirm;
    }

    protected function getOwnFriendsWaitForConfirm()
    {
        return $this->friendsWaitForConfirm;
    }

    public function getBlocked()
    {
        return $this->blocked;
    }

    public function getUsersOnline()
    {
        if ($this->usersOnline === null) {
            $onlineModel = $this->getDbModel('Online');
            $this->usersOnline = $onlineModel->getFieldList(
                'profile_id',
                array(
                    'updated_at >' => date('Y-m-d H:i:00', time() - 600)
                )
            );
        }
        return $this->usersOnline;
    }

    protected function setTitle($title)
    {
        if (!empty($title)) {
            $fullTitle = $title . ' | ' . gettext('header.title');
        } else {
            $fullTitle = gettext('header.title') . ' | ' . gettext('header.motto');
        }
        $this->getView()->set('title', $fullTitle);
    }

    public function setNotification($type, $message)
    {
        $notification = array('type' => $type, 'message' => $message);

        // :TODO: move to memcache
        $key = dechex(microtime(true)) . '.' . dechex(rand(0, 65535));
        $_SESSION['NOTIFICATION_' . $key] = json_encode($notification);
        return $key;
    }

    public function getNotification($key)
    {
        if (array_key_exists('NOTIFICATION_' . $key, $_SESSION)) {
            return json_decode($_SESSION['NOTIFICATION_' . $key], true);
        } else {
            return false;
        }
    }

    protected function setForm($form)
    {
        $form->updateHoneypot();

        $cacheModel = $this->getModel('Cache');
        $cacheModel->setForm(
            $form,
            $this->getSession()->getUserId(),
            $this->getIp()
        );
    }

    /**
     * @param string $formId
     * @return \vc\form\Form
     */
    protected function getForm($formId)
    {
        $cacheModel = $this->getModel('Cache');
        return $cacheModel->getForm(
            $formId,
            $this->getSession()->getUserId(),
            $this->getIp()
        );
    }

    public function getImagesPath()
    {
        return $this->imagesPath;
    }

    protected function isSuspicionBlocked()
    {
        $suspicionModel = $this->getDbModel('Suspicion');
        $suspicionLevel = $suspicionModel->getSuspicionLevel(
            $this->getSession()->getUserId(),
            $this->getIp(),
            time() - \vc\config\Globals::SUSPICION_PAST
        );
        if ($suspicionLevel >= \vc\config\Globals::SUSPICION_BLOCK_LEVEL) {
            $systemMessageModel = $this->getDbModel('SystemMessage');
            $systemMessageModel->informModerators(
                'Access blocked because of high suspicion level',
                "User is trying to access a secured page with a high suspicion level: " . $suspicionLevel . " \n" .
                "User: https://www.veggiecommunity.org/de/user/view/" . $this->getSession()->getUserId() . "/mod/\n" .
                "SERVER: " . var_export($_SERVER, true) . "\n" .
                "POST: " . var_export($_POST, true) . "\n" .
                "GET: " . var_export($_GET, true)
            );
            return true;
        } else {
            return false;
        }
    }

    protected function getFacebook($accessToken)
    {
        try {
            return new \Facebook\Facebook(
                array(
                    'app_id' => \vc\config\Globals::$apps[$this->getServer()]['facebook']['appid'],
                    'app_secret' => \vc\config\Globals::$apps[$this->getServer()]['facebook']['secret'],
                    'default_access_token' => $accessToken
                )
            );
        } catch (Exception $exception) {
            \vc\lib\ErrorHandler::error(
                'Can\'t created Facebook :: ' . get_class($exception) . ': ' . $exception->getMessage(),
                __FILE__,
                __LINE__,
                array('stacktrace' => var_export($exception->getTraceAsString(), true))
            );

            return null;
        }
    }

    protected function getFacebookMe($accessToken, $params = array())
    {
        return $this->queryFacebook(
            $accessToken,
            '/me',
            array(
                'fields' => $params
            )
        );
    }

    protected function queryFacebook($accessToken, $path, $params)
    {
        try {
            $facebook = $this->getFacebook($accessToken);
            if ($facebook === null) {
                return false;
            }

            $request = $facebook->request(
                'GET',
                $path,
                $params
            );
            $response = $facebook->getClient()->sendRequest($request);
            return $response->getGraphNode();
        } catch (Exception $exception) {
            \vc\lib\ErrorHandler::error(
                'Can\'t query Facebook :: ' . get_class($exception) . ': ' . $exception->getMessage(),
                __FILE__,
                __LINE__,
                array('stacktrace' => var_export($exception->getTraceAsString(), true))
            );

            return false;
        }
    }

    protected function handleHoneypot($honeypot, $formValues)
    {
        /*
         * BETTER HONEYPOT
         */
        $modMessageModel = $this->getDbModel('ModMessage');
        $modMessageModel->addMessage(
            $this->getSession()->getUserId(),
            $this->getIp(),
            "BOT IN THE HONEYPOT!!!\”" .
            "Honeypot: " . $honeypot . "\n" .
            "UserAgent: " . (empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT']) . "\n" .
            "FormValues: " . var_export($formValues, true)
        );
    }

    protected function autoLogout()
    {
        $profileModel = $this->getDbModel('Profile');
        $currentUserStatus = intval($profileModel->getField('active', 'id', $this->getSession()->getUserId()));
        if ($currentUserStatus < 1) {
            $this->getSession()->killSession();
            return true;
        } else {
            return false;
        }
    }
}
