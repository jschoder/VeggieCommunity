<?php
namespace vc\shell\cron\task;

abstract class AbstractCronTask
{
    private $cronShell;

    private $start = null;

    private $debugInfo = false;

    private $testMode = false;

    public function __construct()
    {
        $this->start = time();
    }

    abstract public function execute();

    protected function setDebugInfo($key, $value)
    {
        $this->debugInfo[$key] = $value;
    }

    public function setCronShell(\vc\shell\cron\AbstractCronShell $cronShell)
    {
        $this->cronShell = $cronShell;
    }

    public function isTestMode()
    {
        return $this->testMode;
    }

    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
    }

    public function log()
    {
        $end = time();
        $duration = $end - $this->start;

        $taskName = substr(get_class($this), 19);
        $debugInfo = '';
        if (!empty($this->debugInfo)) {
            $debugInfo = json_encode($this->debugInfo);
        }

        $cronLogModel = $this->getDbModel('CronLog');
        $cronLogModel->add(
            $taskName,
            date('Y-m-d h:i:s', $this->start),
            $duration,
            $debugInfo,
            $this->testMode
        );
    }

    /**
     * @return \vc\model\db\DbConnection
     */
    public function getDb()
    {
        return $this->cronShell->getDb();
    }

    /**
     * @return \vc\component\AbstractComponent
     */
    public function getComponent($componentName)
    {
        return $this->cronShell->getComponent($componentName);
    }

    /**
     * @return \vc\model\db\AbstractModel
     */
    public function getModel($model)
    {
        return $this->cronShell->getModel($model);
    }

    /**
     * @return \vc\model\db\AbstractDbModel
     */
    public function getDbModel($modelName)
    {
        return $this->cronShell->getDbModel($modelName);
    }
}
