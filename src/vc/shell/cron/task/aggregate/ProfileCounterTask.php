<?php
namespace vc\shell\cron\task\aggregate;

class ProfileCounterTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'SELECT profile_id, count(*) FROM vc_profile_visit WHERE CURDATE() != visit GROUP BY profile_id';
        $result = $this->getDb()->select($query);
        $this->setDebugInfo('affectedProfiles', $result->num_rows);

        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $profileid = $row[0];
            $counter = $row[1];

            if (!$this->isTestMode()) {
                $this->getDb()->executePrepared(
                    'UPDATE vc_profile SET counter = counter + ? WHERE id = ?',
                    array(
                        intval($counter),
                        $profileid
                    )
                );
            }
        }

        $query2 = 'DELETE LOW_PRIORITY FROM vc_profile_visit WHERE CURDATE() != visit';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query2);
        }
    }
}
