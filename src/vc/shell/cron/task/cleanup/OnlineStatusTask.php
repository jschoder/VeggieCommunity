<?php
namespace vc\shell\cron\task\cleanup;

class OnlineStatusTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $queryPostfix = 'WHERE updated_at < NOW() - INTERVAL 30 MINUTE';

        $query = 'SELECT count(*) FROM vc_online ' . $queryPostfix;
            // :TODO: JOE - deprecated
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();

        $this->setDebugInfo('userCount', $row[0]);

        $query = 'DELETE LOW_PRIORITY FROM vc_online ' . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->delete($query);
            $this->setDebugInfo('affectedRows', $affectedRows);
        }
    }
}
