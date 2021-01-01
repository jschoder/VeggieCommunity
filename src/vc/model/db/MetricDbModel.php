<?php
namespace vc\model\db;

class MetricDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_metric';
    const OBJECT_CLASS = '\\vc\object\\Metric';

    public function getMetricData()
    {
        $metricData = array();

        // Specific queries for user signups
        $query = 'SELECT CONCAT(YEAR(first_entry), \'-\', WEEK(first_entry)) as week, count(DISTINCT id)
                  FROM vc_profile
                  INNER JOIN vc_termsofuse_confirm ON vc_termsofuse_confirm.profile_id = vc_profile.id
                  WHERE first_entry >= DATE_SUB(NOW(), INTERVAL ' . \vc\object\Metric::PAST_WEEKS . ' WEEK)
                  GROUP BY week';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($week, $sum);
        $metricData[\vc\object\Metric::TYPE_PROFILE_CREATION] = array();
        while ($statement->fetch()) {
            $metricData[\vc\object\Metric::TYPE_PROFILE_CREATION][$week] = $sum;
        }
        $statement->close();

        // Specific queries for manual user deletions
        $query = 'SELECT CONCAT(YEAR(delete_date), \'-\', WEEK(delete_date)) as week, count(id)
                  FROM vc_profile
                  WHERE delete_date >= DATE_SUB(NOW(), INTERVAL ' . \vc\object\Metric::PAST_WEEKS . ' WEEK) AND
                        active = -1
                  GROUP BY week';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($week, $sum);
        $metricData[\vc\object\Metric::TYPE_PROFILE_MANUAL_DELETION] = array();
        while ($statement->fetch()) {
            $metricData[\vc\object\Metric::TYPE_PROFILE_MANUAL_DELETION][$week] = $sum;
        }
        $statement->close();

        $query = 'SELECT CEIL(DATEDIFF(NOW(), last_login)/7) as weeks, count(*) FROM vc_profile
                  WHERE active > 0 AND last_login >= DATE_SUB(NOW(), INTERVAL 364 DAY) GROUP BY weeks';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($week, $sum);
        $metricData[\vc\object\Metric::TYPE_LOGINS_DAYS_SINCE] = array();
        while ($statement->fetch()) {
            $metricData[\vc\object\Metric::TYPE_LOGINS_DAYS_SINCE][$week] = $sum;
        }
        $statement->close();

        $query = 'SELECT type, CONCAT(YEAR(day), \'-\', WEEK(day)) as week, SUM(value) FROM vc_metric
                  WHERE day >= DATE_SUB(NOW(), INTERVAL ' . \vc\object\Metric::PAST_WEEKS . ' WEEK)
                  GROUP BY type, week
                  ORDER BY `week` ASC';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($type, $week, $sum);
        while ($statement->fetch()) {
            if (!array_key_exists($type, $metricData)) {
                $metricData[$type] = array();
            }

            // Some charts should show averages rather than total values
            if ($type === \vc\object\Metric::TYPE_EVENTS_UPCOMING ||
                $type === \vc\object\Metric::TYPE_ACTIVE_PLUS) {
                $metricData[$type][$week] = round($sum / 7);
            } else {
                $metricData[$type][$week] = $sum;
            }
        }
        $statement->close();




        return $metricData;
    }
}
