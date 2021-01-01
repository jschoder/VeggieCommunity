<?php
namespace vc\model\db;

class CronLogDbModel extends AbstractDbModel
{
    public function add($taskName, $start, $duration, $debugInfo, $testMode)
    {
        $query = 'INSERT INTO vc_cron_log SET ' .
                 'task_name = ?, start = ?, duration = ?, debug_info = ?, test_mode = ?';
        if (strlen($debugInfo) > 65500) {
            $debugInfo = substr($debugInfo, 0, 65500);
        }
        $this->getDb()->executePrepared(
            $query,
            array(
                $taskName,
                $start,
                intval($duration),
                $debugInfo,
                $testMode ? 1 : 0
            )
        );
    }
}
