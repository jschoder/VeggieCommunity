<?php
namespace vc\shell\cron\task\cleanup;

class TimedOutProfilesTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $queryPostfix = "WHERE active=0 AND first_entry < DATE_SUB(NOW(), INTERVAL 7 DAY) ";

        $query = "SELECT id, nickname, email, first_entry FROM vc_profile " .
            $queryPostfix . " ORDER BY first_entry asc";
        $result = $this->getDb()->select($query);

        $timedOutProfiles = array();
        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $timedOutProfiles[] = array('id' => $row[0],
                                        'nickname' => $row[1],
                                        'email' => $row[2],
                                        'first_entry' => $row[3]);
        }
        $this->setDebugInfo('timedOutProfiles', $timedOutProfiles);

        $query2 = "UPDATE LOW_PRIORITY vc_profile SET active=-100, delete_date=now() " . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->update($query2);
            $this->setDebugInfo('affectedRows', $affectedRows);
        }
    }
}
