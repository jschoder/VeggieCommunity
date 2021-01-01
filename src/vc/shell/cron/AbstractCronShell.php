<?php
namespace vc\shell\cron;

abstract class AbstractCronShell extends \vc\shell\AbstractShell
{
    abstract protected function getLockName();

    abstract protected function getTasks();

    public function run()
    {
        $lock = new \vc\shell\Lock(TMP_DIR . '/' . $this->getLockName() . '.lock');
        if ($lock->create()) {
            $tasks = $this->getTasks();
            foreach ($tasks as $task) {
                $start = microtime(true);
                echo 'Running ' . get_class($task) . ' ';
                $task->setCronShell($this);
                echo '.';
                $task->setTestMode($this->isTestMode());
                echo '.';
                $task->execute();
                echo '.';
                $task->log();
                $end = microtime(true);
                echo " done (" . round($end - $start, 3) . ") \n";
            }
        } else {
            echo 'Cron still running. Won\'t attempt starting another one. ';
        }
    }
}
