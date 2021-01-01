<?php
namespace vc\shell\cron\task\cleanup;

class TokenTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'DELETE LOW_PRIORITY FROM vc_activation_token WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'DELETE LOW_PRIORITY FROM vc_change_pw_token WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }
    }
}
