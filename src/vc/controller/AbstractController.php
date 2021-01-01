<?php
namespace vc\controller;

abstract class AbstractController
{
    // :TODO: migrator2 - get rid of this reference
    protected $db = null;

    protected $modelFactory = null;

    protected $componentFactory = null;

    protected $server = null;

    protected $path = null;

    protected $site = null;

    protected $siteParams = null;

    protected $locale = null;

    private $eventService = null;

    /**
     * @var \vc\model\SessionModel
     */
    private $sessionModel;

    public function setup($db, $modelFactory, $componentFactory, $server, $path, $site, $siteParams, $locale)
    {
        $this->db = $db;
        $this->modelFactory = $modelFactory;
        $this->componentFactory = $componentFactory;
        $this->server = $server;
        $this->path = $path;
        $this->site = $site;
        $this->siteParams = $siteParams;
        $this->locale = $locale;

        $this->getComponent('I14n')->loadLocale($locale);

        $this->sessionModel = $this->getModel('Session');
    }

    protected function logPageView()
    {
        return true;
    }

    abstract public function dispatch();

    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return \vc\model\db\DbConnection
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return \vc\component\AbstractComponent
     */
    public function getComponent($componentName)
    {
        return $this->componentFactory->getComponent($componentName);
    }

    /**
     * @return \vc\model\db\AbstractModel
     */
    public function getModel($modelName)
    {
        return $this->modelFactory->getModel($modelName);
    }

    /**
     * @return \vc\model\db\AbstractDbModel
     */
    public function getDbModel($modelName)
    {
        return $this->modelFactory->getDbModel($modelName);
    }

    /**
     * @return \vc\model\service\EventService
     */
    public function getEventService()
    {
        if ($this->eventService === null) {
            $this->eventService = new \vc\model\service\EventService($this->modelFactory);
        }
        return $this->eventService;
    }


    public function getPath()
    {
        return $this->path;
    }

    public function getServer()
    {
        return $this->server;
    }

    protected function getServerRoot()
    {
        if ($_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        return $protocol . '://' . $_SERVER['SERVER_NAME'] . $this->path;
    }

    protected function getIp()
    {
        return \vc\helper\RequestHelper::getIp();
    }

    /**
     * @return \vc\model\SessionModel
     */
    public function getSession()
    {
        return $this->sessionModel;
    }

    protected function addSuspicion($type, $debugData = null)
    {
        if (empty($debugData)) {
            $debugData = array();
        }
        $debugData['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        $suspicionModel = $this->getDbModel('Suspicion');
        $suspicionModel->addSuspicion($this->getModel('Session')->getUserId(), $type, $this->getIp(), $debugData);
    }

    protected function addPageViewLog($profileId)
    {
        /*
        global $scriptStart;

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $requestMethod = \vc\model\db\PageViewLogDbModel::REQUEST_METHOD_GET;
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestMethod = \vc\model\db\PageViewLogDbModel::REQUEST_METHOD_POST;
        } else {
            $requestMethod = 3;
        }
        $scriptTime = round((microtime(true) - $scriptStart) * 1000);

        $pageViewLogModel = $this->getDbModel('PageViewLog');
        $pageViewLogModel->log(
            $requestMethod,
            $this->site,
            $this->siteParams,
            session_id(),
            $profileId,
            $scriptTime
        );
         */
    }
}
