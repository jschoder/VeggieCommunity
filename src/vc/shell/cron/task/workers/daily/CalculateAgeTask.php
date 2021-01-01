<?php
namespace vc\shell\cron\task\workers\daily;

class CalculateAgeTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        if (!$this->isTestMode()) {
            $this->getDb()->executePrepared(
                'UPDATE LOW_PRIORITY vc_profile ' .
                'SET age=(YEAR(CURDATE())-YEAR(birth))-(RIGHT(CURDATE(),5)<RIGHT(birth,5)) WHERE active > 0'
            );
        }
    }
}
