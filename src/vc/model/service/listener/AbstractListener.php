<?php
namespace vc\model\service\listener;

abstract class AbstractListener
{
    private $eventService;

    private $modelFactory;

    public function __construct($eventService, $modelFactory)
    {
        $this->eventService = $eventService;
        $this->modelFactory = $modelFactory;
    }

    abstract public function trigger($entityId, $createdBy);

    /**
     * @return \vc\model\service\EventService
     */
    public function getEventService()
    {
        return $this->eventService;
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
}
