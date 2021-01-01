<?php
namespace vc\shell\cron;

class ContinualCronShell extends AbstractCronShell
{
    protected function getLockName()
    {
        return 'cron.continual';
    }

    protected function getTasks()
    {
        $tasks = array();
        $tasks[] = new task\workers\continual\AutoSendMailsTask();
        $tasks[] = new task\workers\continual\SystemMailTask();
        return $tasks;
    }
}
