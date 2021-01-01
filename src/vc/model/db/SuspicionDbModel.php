<?php
namespace vc\model\db;

class SuspicionDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_suspicion';

    const TYPE_FAILED_ASSERTION = 1;
    const TYPE_INVALID_LOGIN = 2;
//    const TYPE_INVALID_COOKIE_LOGIN = 3; Deprecated after replacing old cookie login
    const TYPE_MESSAGE_TO_DELETED_USER = 4;
    const TYPE_MESSAGE_TO_BLOCKED_USER = 36;
    const TYPE_INVALID_CHANGE_PW_URL = 5;
    const TYPE_INVALID_UNLOCK_URL = 6;
    const TYPE_ACCESS_DELETED_PROFILE = 7;
    const TYPE_REMIND_PASSWORD = 8;
    const TYPE_HTML_IN_SUPPORT_REQUEST = 9;

    const TYPE_COOKIE_LOGIN_NOT_FOUND = 10;
    const TYPE_COOKIE_LOGIN_EXPIRED_INACTIVE = 11;
    const TYPE_INVALID_GET_REQUEST = 12;
    const TYPE_INVALID_POST_REQUEST = 13;
    
    const TYPE_FRIEND_REQUEST = 39;

    const TYPE_INVALID_GROUP = 14;
    const TYPE_INVALID_FORUM = 15;
    const TYPE_INVALID_FORUM_THREAD = 16;
    const TYPE_INVALID_FORUM_COMMENT = 27;
    const TYPE_ACCESS_GROUP_AS_NONMEMBER = 17;
    const TYPE_ACCESS_GROUP_AS_NONADMIN = 18;

    const TYPE_INVALID_EVENT = 23;
    const TYPE_ACCESS_INVISIBLE_EVENT = 24;

    const TYPE_INVALID_CONTEXT = 21;
    const TYPE_ACCESS_CONTEXT_AS_NONMEMBER = 22;

    const TYPE_INVALID_FLAG = 19;

    const TYPE_MISSING_PICTURE = 20;
    const TYPE_TOO_MANY_PICTURES = 28;

    const TYPE_INVALID_WEBSOCKET_REQUEST = 25;

    const TYPE_EDIT_MATCHING_24H = 26;

    const TYPE_ACCESS_PLUS_ONLY_FEATURES = 29;

    const TYPE_PATH_MANIPULATION = 30;
    const TYPE_INVALID_POST_URL = 31;
    const TYPE_DOUBLE_POINT_URL = 32;
    const TYPE_INVALID_ROUTING = 33;
    const TYPE_ATTEMPT_EDITING_CONTENT_OF_OTHERS = 38;

    // Spam markers
    // Setting the suspicion rate at the minimal possible value
    // since they are only used to identiy probable spam
    const TYPE_SPAM_BLOCKED_PIC = 34;
    const TYPE_SPAM_ILLEGAL_COUNTRY_LOGIN = 35;
    const TYPE_SPAM_BLOCKED_USER_LOGIN_ATTEMPT = 37;
    const TYPE_BLOCKED_EMAIL = 40;
    const TYPE_TEMP_EMAIL = 41;

    public $suspicionWeights = array(
        self::TYPE_FAILED_ASSERTION => 10,
        self::TYPE_INVALID_LOGIN => 5,
        self::TYPE_MESSAGE_TO_DELETED_USER => 5,
        self::TYPE_MESSAGE_TO_BLOCKED_USER => 5,
        self::TYPE_INVALID_CHANGE_PW_URL => 10,
        self::TYPE_INVALID_UNLOCK_URL => 10,
        self::TYPE_ACCESS_DELETED_PROFILE => 1,
        self::TYPE_REMIND_PASSWORD => 2,
        self::TYPE_HTML_IN_SUPPORT_REQUEST => 10,
        self::TYPE_COOKIE_LOGIN_NOT_FOUND => 20,
        self::TYPE_COOKIE_LOGIN_EXPIRED_INACTIVE => 1,
        self::TYPE_INVALID_GET_REQUEST => 10,
        self::TYPE_INVALID_POST_REQUEST => 10,
        self::TYPE_FRIEND_REQUEST => 2,
        self::TYPE_INVALID_GROUP => 10,
        self::TYPE_INVALID_FORUM => 10,
        self::TYPE_INVALID_FORUM_THREAD => 10,
        self::TYPE_INVALID_FORUM_COMMENT => 10,
        self::TYPE_ACCESS_GROUP_AS_NONMEMBER => 20,
        self::TYPE_ACCESS_GROUP_AS_NONADMIN => 20,
        self::TYPE_INVALID_EVENT => 10,
        self::TYPE_ACCESS_INVISIBLE_EVENT => 5,
        self::TYPE_INVALID_CONTEXT => 10,
        self::TYPE_ACCESS_CONTEXT_AS_NONMEMBER => 20,
        self::TYPE_INVALID_FLAG => 20,
        self::TYPE_MISSING_PICTURE => 20,
        self::TYPE_TOO_MANY_PICTURES => 10,
        self::TYPE_INVALID_WEBSOCKET_REQUEST => 20,
        self::TYPE_EDIT_MATCHING_24H => 10,
        self::TYPE_ACCESS_PLUS_ONLY_FEATURES => 20,
        self::TYPE_PATH_MANIPULATION => 10,
        self::TYPE_INVALID_POST_URL => 50,
        self::TYPE_DOUBLE_POINT_URL => 50,
        self::TYPE_INVALID_ROUTING => 5,
        self::TYPE_ATTEMPT_EDITING_CONTENT_OF_OTHERS => 50,
        self::TYPE_SPAM_BLOCKED_PIC => 1,
        self::TYPE_SPAM_ILLEGAL_COUNTRY_LOGIN => 1,
        self::TYPE_SPAM_BLOCKED_USER_LOGIN_ATTEMPT => 1,
        self::TYPE_BLOCKED_EMAIL => 1,
        self::TYPE_TEMP_EMAIL => 1
    );

    public function addSuspicion($userId, $type, $ip, $debugData = null)
    {
        $this->storeSuspicion(
            time(),
            $userId,
            $ip,
            $type,
            $this->suspicionWeights[$type],
            $debugData
        );
        $suspicionLevel = $this->getSuspicionLevel(
            $userId,
            $ip,
            time() - \vc\config\Globals::SUSPICION_PAST
        );

        if ($suspicionLevel >= \vc\config\Globals::SUSPICION_MOD_WARN_LEVEL) {
//            $suspicionConstants = \vc\helper\ConstantHelper::getConstants(__CLASS__);
//            $mailBody = 'User with high suspicion level: ' . "\n" .
//                        'ProfileId: ' . $userId . "\n" .
//                        'Ip: ' . $ip  . "\n\n";
//
//            $suspicionCount = $this->getAllSuspicionCount(
//                $userId,
//                $ip,
//                time() - \vc\config\Globals::SUSPICION_PAST
//            );
//            foreach ($suspicionCount as $type => $count) {
//                $mailBody .= $suspicionConstants[$type] . ': ' . $count . "\n";
//            }
//            $mailBody .= "\n";
//
//            $suspicions = $this->getAllCurrentSuspicions(
//                $userId,
//                $ip,
//                time() - \vc\config\Globals::SUSPICION_PAST
//            );
//            foreach ($suspicions as &$suspicion) {
//                $suspicion['type'] = $suspicionConstants[$suspicion['type']] . ' (' . $suspicion['type'] . ')';
//            }
//            $mailBody .= 'Previous Suspicions: ' . "\n" . var_export($suspicions, true);
//
//            $systemMessageModel = $this->getDbModel('SystemMessage');
//            $systemMessageModel->informModerators(
//                'User-Suspicion [' . $suspicionLevel . ']',
//                $mailBody
//            );
        }
        return $suspicionLevel;
    }

    private function storeSuspicion($occurence, $profileId, $ip, $type, $weight, $debugData)
    {
        $encodedDebugData = '';
        if ($debugData !== null) {
            $encodedDebugData = json_encode($debugData);
        }
        $query = 'INSERT INTO vc_suspicion SET
                  occurrence = ?,
                  profile_id = ?,
                  ip = ?,
                  type = ?,
                  weight = ?,
                  debug_data = ?';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                date('Y-m-d H:i:s', intval($occurence)),
                intval($profileId),
                $ip,
                intval($type),
                intval($weight),
                $encodedDebugData
            )
        );
        return $success;
    }

    public function getSuspicionLevel($profileId, $ip, $since)
    {
        $query = 'SELECT sum(weight) as level FROM vc_suspicion WHERE occurrence > ?';
        $queryParams = array();
        $queryParams[] = date('Y-m-d h:i:s', intval($since));
        if ($profileId == 0) {
            $query .= ' AND ip = ?';
            $queryParams[] = $ip;
        } else {
            $query .= ' AND (ip = ? OR profile_id = ?)';
            $queryParams[] = $ip;
            $queryParams[] = intval($profileId);
        }
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result($weight);
        $statement->fetch();
        $statement->close();
        return $weight;
    }

    public function getAllSuspicionCount($profileId, $ip, $since)
    {
        $query = 'SELECT type, count(*) FROM vc_suspicion WHERE occurrence > ?';
        $queryParams = array();
        $queryParams[] = date('Y-m-d h:i:s', intval($since));
        if ($profileId == 0) {
            $query .= ' AND ip = ?';
            $queryParams[] = $ip;
        } else {
            $query .= ' AND (ip = ? OR profile_id = ?)';
            $queryParams[] = $ip;
            $queryParams[] = intval($profileId);
        }
        $query .= ' GROUP BY type';
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $type,
            $count
        );
        $suspicionCount = array();
        while ($statement->fetch()) {
            $suspicionCount[$type] = $count;
        }
        $statement->close();
        return $suspicionCount;
    }

    public function getAllCurrentSuspicions($profileId, $ip, $since)
    {
        $query = 'SELECT occurrence, profile_id, ip, type, weight, debug_data FROM vc_suspicion WHERE occurrence > ?';
        $queryParams = array();
        $queryParams[] = date('Y-m-d h:i:s', intval($since));
        if ($profileId == 0) {
            $query .= ' AND ip = ?';
            $queryParams[] = $ip;
        } else {
            $query .= ' AND (ip = ? OR profile_id = ?)';
            $queryParams[] = $ip;
            $queryParams[] = intval($profileId);
        }
        $query .= ' LIMIT 1000';
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $occurrence,
            $profileId,
            $ip,
            $type,
            $weight,
            $debugData
        );
        $suspicions = array();
        while ($statement->fetch()) {
            $suspicions[] = array(
                'occurrence' => $occurrence,
                'profile_id' => $profileId,
                'ip' => $ip,
                'type' => $type,
                'weight' => $weight,
                'debug_data' => json_decode($debugData)
            );
        }
        $statement->close();
        return $suspicions;
    }

    public function getAggregatedHoursSuspicions()
    {
        $query = 'SELECT type, HOUR(occurrence), count(*)
                  FROM vc_suspicion
                  WHERE occurrence > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                  GROUP BY type, YEAR(occurrence), MONTH(occurrence), DAY(occurrence), HOUR(occurrence)
                  ORDER BY YEAR(occurrence), MONTH(occurrence), DAY(occurrence), HOUR(occurrence)';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $type,
            $hour,
            $count
        );
        $suspicions = array();
        while ($statement->fetch()) {
            if (empty($suspicions[$hour])) {
                $suspicions[$hour] = array();
            }
            $suspicions[$hour][$type] = $count;
        }
        $statement->close();
        return $suspicions;
    }

    public function getAggregatedDaysSuspicions()
    {
        $query = 'SELECT type, YEAR(occurrence), MONTH(occurrence), DAY(occurrence), count(*)
                  FROM vc_suspicion
                  WHERE occurrence > DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY type, YEAR(occurrence), MONTH(occurrence), DAY(occurrence)
                  ORDER BY YEAR(occurrence), MONTH(occurrence), DAY(occurrence)';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $type,
            $year,
            $month,
            $day,
            $count
        );
        $suspicions = array();
        while ($statement->fetch()) {
            $date = $year . '-' . ($month < 10 ? '0' : '') . $month . '-' . ($day < 10 ? '0' : '') .$day;
            if (empty($suspicions[$date])) {
                $suspicions[$date] = array();
            }
            $suspicions[$date][$type] = $count;
        }
        $statement->close();
        return $suspicions;
    }

    public function getSuspicions($type, $user, $ip, $createdSince, $limit)
    {
        $queryJoins = '';
        $queryWhere = array();
        $queryParams = array();

        if (!empty($type)) {
            $queryWhere[] = 'vc_suspicion.type = ?';
            $queryParams[] = $type;
        }
        if (!empty($user)) {
            $queryJoins .= ' LEFT JOIN vc_user_ip_log ON vc_user_ip_log.ip = vc_suspicion.ip';
            $queryWhere[] = '(vc_suspicion.profile_id = ? OR vc_user_ip_log.profile_id = ?)';
            $queryParams[] = $user;
            $queryParams[] = $user;
        }
        if (!empty($ip)) {
            $queryWhere[] = 'vc_suspicion.ip = ?';
            $queryParams[] = $ip;
        }
        if (!empty($createdSince)) {
            $queryWhere[] = 'vc_suspicion.occurrence >= ?';
            $queryParams[] = date('Y-m-d H:i:s', $createdSince);
        }

        $query = 'SELECT DISTINCT occurrence, vc_suspicion.profile_id, vc_suspicion.ip, type, debug_data FROM vc_suspicion' .
                 $queryJoins . ' ' .
                 (empty($queryWhere) ? '' : ' WHERE ' . implode(' AND ', $queryWhere)) .
                 ' ORDER BY occurrence DESC';
        if ($limit !== null) {
            $query .= ' LIMIT ' . intval($limit);
        }

        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $occurrence,
            $profileId,
            $ip,
            $type,
            $debugData
        );
        $suspicions = array();
        while ($statement->fetch()) {
            $suspicions[] = array(
                'occurrence' => $occurrence,
                'profileId' => $profileId,
                'ip' => $ip,
                'type' => $type,
                'debugData' => $debugData
            );
        }
        $statement->close();
        return $suspicions;
    }
}
