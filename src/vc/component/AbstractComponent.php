<?php
namespace vc\component;

abstract class AbstractComponent
{
    private $modelFactory;

    private $componentFactory;

    private $server;

    private $db = null;

    public function setDb(\vc\model\db\DbConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @return \vc\model\db\DbConnection
     */
    public function getDb()
    {
        return $this->db ;
    }

    public function setModelFactory(\vc\model\ModelFactory $modelFactory)
    {
        $this->modelFactory = $modelFactory;
    }

    public function setComponentFactory(ComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
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
    public function getDbModel($model)
    {
        return $this->modelFactory->getDbModel($model);
    }
    /**
     * @return \vc\component\AbstractComponent
     */
    public function getComponent($model)
    {
        return $this->componentFactory->getComponent($model);
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setServer($server)
    {
        $this->server = $server;
    }
}
