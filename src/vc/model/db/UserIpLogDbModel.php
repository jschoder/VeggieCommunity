<?php
namespace vc\model\db;

class UserIpLogDbModel extends AbstractDbModel
{
    public function getDuplicates($repetitionLevel = 8)
    {
        $duplicates = array();

        $query = 'SELECT DISTINCT
                  ip2.day as day,
                  LEAST(ip1.profile_id, ip2.profile_id) as u1,
                  GREATEST(ip1.profile_id, ip2.profile_id) as u2
                  FROM vc_user_ip_log ip1
                  INNER JOIN (SELECT DISTINCT DATE(access) as day, profile_id, ip FROM vc_user_ip_log) AS ip2
                  ON DATE(ip1.access) = ip2.`day` AND ip1.ip = ip2.ip AND ip1.profile_id != ip2.profile_id ';

        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($day, $profileId1, $profileId2);
        while ($statement->fetch()) {
            if (!array_key_exists($profileId1, $duplicates)) {
                $duplicates[$profileId1] = array();
            }
            if (!array_key_exists($profileId2, $duplicates[$profileId1])) {
                $duplicates[$profileId1][$profileId2] = array();
            }
            $duplicates[$profileId1][$profileId2][] = $day;
        }
        $statement->close();

        $filteredDuplicates = array();
        foreach ($duplicates as $profileId1 => $profileIds) {
            foreach ($profileIds as $profileId2 => $days) {
                if (count($days) >= $repetitionLevel) {
                    if (!array_key_exists($profileId1, $duplicates)) {
                        $filteredDuplicates[$profileId1] = array();
                    }
                    $filteredDuplicates[$profileId1][$profileId2] = $days;
                }
            }
        }
        return $filteredDuplicates;
    }

    public function getRecentLogins($profileId)
    {
        $query = 'SELECT ip, max(access)
                  FROM vc_user_ip_log
                  WHERE profile_id = ?
                  GROUP BY ip
                  ORDER BY max(access) DESC
                  LIMIT 25';

        $statement = $this->getDb()->queryPrepared($query, array($profileId));
        $statement->bind_result(
            $ip,
            $access
        );
        $recentLogins = array();
        while ($statement->fetch()) {
            $recentLogins[$access] = $ip;
        }
        $statement->close();

        return $recentLogins;
    }
}
