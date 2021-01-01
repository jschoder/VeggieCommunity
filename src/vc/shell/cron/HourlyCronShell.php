<?php
namespace vc\shell\cron;

class HourlyCronShell extends AbstractCronShell
{
    protected function getLockName()
    {
        return 'cron.hourly';
    }

    protected function getTasks()
    {
        $tasks = array();
        $tasks[] = new task\workers\hourly\AddReconfirmRealCheckTask();
        $tasks[] = new task\workers\hourly\PlusTask();
        $tasks[] = new task\workers\continual\FilesystemTask();
        $tasks[] = new task\aggregate\GroupActivityTask();
        return $tasks;
    }
}
