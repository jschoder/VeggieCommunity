<?php
namespace vc\shell\cron\task\cleanup;

class LogsTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'DELETE LOW_PRIORITY FROM vc_cron_log WHERE start < DATE_SUB(NOW(), INTERVAL 60 DAY)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'DELETE LOW_PRIORITY FROM vc_group_notification ' .
                 'WHERE seen_at IS NOT NULL AND seen_at < DATE_SUB(NOW(), INTERVAL 8 HOUR)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = "DELETE LOW_PRIORITY FROM vc_news WHERE expires_at IS NOT NULL AND expires_at < NOW()";
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'DELETE LOW_PRIORITY FROM vc_update WHERE last_update < DATE_SUB(NOW(), INTERVAL 8 HOUR)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'DELETE LOW_PRIORITY FROM vc_suspicion WHERE occurrence < DATE_SUB(NOW(), INTERVAL 60 DAY)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'DELETE LOW_PRIORITY FROM vc_user_ip_log WHERE access < DATE_SUB(NOW(), INTERVAL 1 YEAR)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'DELETE LOW_PRIORITY FROM vc_websocket_server_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'DELETE LOW_PRIORITY FROM vc_websocket_user WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }
    }
}
