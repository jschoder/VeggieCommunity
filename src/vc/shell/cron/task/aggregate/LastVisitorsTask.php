<?php
namespace vc\shell\cron\task\aggregate;

class LastVisitorsTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $subselect = 'SELECT id FROM vc_profile WHERE active<0';

        $queryPostfix = ' FROM vc_last_visitor WHERE profile_id IN (' . $subselect . ') ' .
                                                  'OR visitor_id IN (' . $subselect . ')';
        $query = 'SELECT count(*) ' . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('DeletedLastVisitors', $row[0]);

        $query2 = 'DELETE LOW_PRIORITY ' . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query2);
        }

        $reducedLastVisitors = 0;
        $query = 'SELECT id FROM vc_profile WHERE active > 0';
        $result = $this->getDb()->select($query);
        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $lastVisitors = array();
            $query2 = 'SELECT visitor_id FROM vc_last_visitor WHERE profile_id=' . $row[0] .
                      ' ORDER BY last_visit DESC LIMIT 0,100';
            $result2 = $this->getDb()->select($query2);
            for ($i2=0; $i2 < $result2->num_rows; $i2++) {
                $row2 = $result2->fetch_row();
                $lastVisitors[] = $row2[0];
            }

            if (count($lastVisitors) == 100) {
                $queryPostfix =  'WHERE profile_id=' . $row[0] .
                                 ' AND visitor_id NOT IN (' . implode(',', $lastVisitors) . ')';
                if ($this->isTestMode()) {
                    $query = 'SELECT count(*) FROM vc_last_visitor ' . $queryPostfix;
                    $result = $this->getDb()->select($query);
                    $row = $result->fetch_row();
                    $reducedLastVisitors += $row[0];
                } else {
                    $query3 = 'DELETE LOW_PRIORITY FROM vc_last_visitor ' . $queryPostfix;
                    // :TODO: JOE - deprecated
                    $reducedLastVisitors += $this->getDb()->delete($query3);
                }
            }
        }
        $this->setDebugInfo('reducedLastVisitors', $reducedLastVisitors);
    }
}
