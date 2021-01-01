<?php
namespace vc\model;

class ModelFactory
{
    private $db;

    private $server;

    private $models;

    private $dbModels;


    public function __construct($db, $server)
    {
        $this->db = $db;
        $this->server = $server;
        $this->models = new \stdClass();
        $this->dbModels = new \stdClass();
    }

    /**
     * @return \vc\model\db\AbstractModel
     */
    public function getModel($modelName)
    {
        if (!isset($this->models->$modelName)) {
            $modelClassName = '\\vc\\model\\' . $modelName . 'Model';
            if (!class_exists($modelClassName)) {
                throw new \vc\exception\FatalSystemException('Model ' . $modelName. ' can\'t be created.');
            }
            $model = new $modelClassName();
            $model->setModelFactory($this);
            $this->models->$modelName = $model;
            return $model;
        }
        return $this->models->$modelName;
    }

    /**
     * @return \vc\model\db\AbstractDbModel
     */
    public function getDbModel($modelName)
    {
        if (!isset($this->dbModels->$modelName)) {
            $modelClassName = '\\vc\\model\\db\\' . $modelName . 'DbModel';
            if (!class_exists($modelClassName)) {
                throw new \vc\exception\FatalSystemException('DB Model ' . $modelName. ' can\'t be created.');
            }
            $model = new $modelClassName();
            $model->setModelFactory($this);
            $model->setDb($this->db);
            $this->dbModels->$modelName = $model;
            return $model;
        }
        return $this->dbModels->$modelName;
    }
}
