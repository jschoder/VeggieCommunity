<?php
namespace vc\model;

abstract class AbstractModel
{
    private $modelFactory;

    public function setModelFactory(\vc\model\ModelFactory $modelFactory)
    {
        $this->modelFactory = $modelFactory;
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
}
