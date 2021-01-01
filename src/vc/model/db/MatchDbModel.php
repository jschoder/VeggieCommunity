<?php
namespace vc\model\db;

class MatchDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_match';

    public function getPercentage($userId1, $userId2)
    {
        if ($userId1 == $userId2) {
            return null;
        }

        $query = 'SELECT percentage FROM vc_match WHERE min_user_id = ? AND max_user_id = ?';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(
               min(intval($userId1), intval($userId2)),
               max(intval($userId1), intval($userId2))
            )
        );
        $statement->store_result();

        if ($statement->num_rows == 0) {
            $statement->close();
            return null;
        } else {
            $statement->bind_result($percentage);
            $statement->fetch();
            $statement->close();
            return $percentage;
        }
    }

    public function getPercentages($currentUserId, $userIdList)
    {
        if (empty($userIdList) || $userIdList == array($currentUserId)) {
            return array();
        }

        $minUserIds = array();
        $maxUserIds = array();
        foreach ($userIdList as $userId) {
            if ($userId < $currentUserId) {
                $minUserIds[] = $userId;
            } elseif ($userId > $currentUserId) {
                $maxUserIds[] = $userId;
            }
            // Same userId will be ignored
        }

        $query = 'SELECT min_user_id, max_user_id, percentage FROM vc_match WHERE ';
        $queryParams = array();
        if (!empty($minUserIds)) {
            $queryParams = array_merge($queryParams, (array) $currentUserId, $minUserIds);
            $query .= '(max_user_id = ? AND '
                    . ' min_user_id IN (' . trim(str_repeat('?, ', count($minUserIds)), ', ') . '))';
            if (!empty($maxUserIds)) {
                $query .= ' OR ';
            }
        }
        if (!empty($maxUserIds)) {
            $query .= '(min_user_id = ? AND '
                    . ' max_user_id IN (' . trim(str_repeat('?, ', count($maxUserIds)), ', ') . '))';
            $queryParams = array_merge($queryParams, (array) $currentUserId, $maxUserIds);
        }

        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result($minUserId, $maxUserId, $percentage);
        $percentages = array();
        while ($statement->fetch()) {
            if ($minUserId == $currentUserId) {
                $percentages[$maxUserId] = $percentage;
            } else {
                $percentages[$minUserId] = $percentage;
            }
        }
        $statement->close();
        return $percentages;
    }

    public function getSuggestedMembers($profileId, $blocked, $limit)
    {
        $query = 'SELECT
                  IF(min_user_id = ?, max_user_id, min_user_id) AS match_user_id,
                  (
                      IF(vc_match.percentage > 80, vc_match.percentage - 80, 80 - vc_match.percentage) +
                      POW(
                          acos(
                              (max_user.sin_latitude * min_user.sin_latitude) +
                              (max_user.cos_latitude * min_user.cos_latitude *
                               cos(max_user.longitude_radius - min_user.longitude_radius))
                          ) * 90,
                          2
                      ) +
                      (
                          SQRT(
                              IF(
                                  min_user_id = ?,
                                  (NOW() - max_user.last_update) / 604800,
                                  (NOW() - min_user.last_update) / 604800
                              ) +
                              IF(
                                  min_user_id = ?,
                                  (NOW() - max_user.last_login) / 86400,
                                  (NOW() - min_user.last_login) / 86400
                              )
                          ) / 4
                      )
                  ) as calculation
                  FROM vc_match
                  INNER JOIN vc_profile min_user ON min_user.id = min_user_id
                  LEFT JOIN vc_picture min_pictures
                            ON min_pictures.profileid = min_user_id AND min_pictures.defaultpic = 1
                  INNER JOIN vc_profile max_user
                             ON max_user.id = max_user_id
                  LEFT JOIN vc_picture max_pictures
                            ON max_pictures.profileid = max_user_id AND max_pictures.defaultpic = 1
                  WHERE
                      (min_user_id = ? OR max_user_id = ?) AND
                      (min_user.latitude!=0 OR min_user.longitude!=0) AND
                      (max_user.latitude!=0 OR max_user.longitude!=0) AND
                      ((min_user_id = ? AND max_pictures.id IS NOT NULL) OR
                       (max_user_id = ? AND min_pictures.id IS NOT NULL)) ' .
                    (
                        empty($blocked)
                            ? ''
                            : ' AND min_user_id NOT IN (' . implode(',', $blocked) . ') '.
                              ' AND max_user_id NOT IN (' . implode(',', $blocked) . ') '
                    ) .
                  'ORDER BY calculation ASC
                  LIMIT ?';

        $statement = $this->getDb()->queryPrepared(
            $query,
            array($profileId, $profileId, $profileId, $profileId, $profileId, $profileId, $profileId, $limit)
        );
        $statement->bind_result($profileId, $calculation);
        $matches = array();
        while ($statement->fetch()) {
            $matches[] = $profileId;
        }
        $statement->close();

        return $matches;
    }
}
