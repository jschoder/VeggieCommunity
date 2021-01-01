<?php
namespace vc\shell\cron\task\workers\hourly;

class PlusTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'UPDATE LOW_PRIORITY vc_profile
                  SET plus_marker = 0
                  WHERE id NOT IN (SELECT user_id FROM vc_plus WHERE NOW() BETWEEN start_date AND end_date)';
        $this->getDb()->execute($query);
    }
}
