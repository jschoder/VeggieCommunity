<?php
namespace vc\model\service;

abstract class AbstractModuleService
{
    private $modelFactory;

    private $componentFactory;

    public function __construct($modelFactory, $componentFactory)
    {
        $this->modelFactory = $modelFactory;
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

}