<?php
namespace vc\shell;

abstract class AbstractShell
{
    private $params = array();

    private $server = null;

    private $db = null;

    private $modelFactory = null;

    private $componentFactory = null;

    private $testMode = false;


    public function setup()
    {
        $testParam = $this->getParam('test');
        if ($testParam == 'true' ||
           $testParam == '1') {
            $this->setTestMode(true);
        }

        $this->server = $this->getParam('server', 'local');
        $this->db = new \vc\model\db\DbConnection($this->server);
        $this->modelFactory = new \vc\model\ModelFactory($this->db, $this->server);
        $this->componentFactory = new \vc\component\ComponentFactory($this->db, $this->server, $this->modelFactory);
    }

    abstract public function run();

    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
    }

    public function isTestMode()
    {
        return $this->testMode;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParam($key, $default = null)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        } else {
            return $default;
        }
    }

    public function getServer()
    {
        return $this->server;
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
    public function getModel($model)
    {
        return $this->modelFactory->getModel($model);
    }

    /**
     * @return \vc\model\db\AbstractDbModel
     */
    public function getDbModel($modelName)
    {
        return $this->modelFactory->getDbModel($modelName);
    }
}
