<?php
namespace vc\model\db;

class MatchingDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_matching';
    const OBJECT_CLASS = '\\vc\object\\Matching';

    // Leaving the 0 row/column for performance reasons
    private $matchingMatrix = array(
        array(0,   0,   0,   0,   0,   0),
        array(0,   4,   3,   1, 0.1,   0),
        array(0,   3,   4,   3,   1, 0.1),
        array(0,   1,   3,   4,   3,   1),
        array(0, 0.1,   1,   3,   4,   3),
        array(0,   0, 0.1,   1,   3,   4),
    );

    private $reverseMatchingMatrix = array(
        array(0,   0,   0,   0,   0,   0),
        array(0,   0, 0.1,   1,   3,   4),
        array(0, 0.1,   1,   3,   4,   3),
        array(0,   1,   3,   4,   3,   1),
        array(0,   3,   4,   3,   1, 0.1),
        array(0,   4,   3,   1, 0.1,   0),
    );

    public function match($matchingObject1, $matchingObject2)
    {

        $rawPercentage = (
                ($this->matchingMatrix[$matchingObject1->poly][$matchingObject2->poly] * 0.862) +
                ($this->matchingMatrix[$matchingObject1->weird][$matchingObject2->weird] * 0.768) +
                ($this->matchingMatrix[$matchingObject1->stayhome][$matchingObject2->stayhome] * 0.768) +
                ($this->matchingMatrix[$matchingObject1->myLooks][$matchingObject2->theirLooks] * 0.762) +
                ($this->matchingMatrix[$matchingObject1->theirLooks][$matchingObject2->myLooks] * 0.762) +
                ($this->matchingMatrix[$matchingObject1->fitness][$matchingObject2->fitness] * 0.738) +
                ($this->matchingMatrix[$matchingObject1->couch][$matchingObject2->couch] * 0.725) +
                ($this->matchingMatrix[$matchingObject1->driven][$matchingObject2->driven] * 0.723) +
                ($this->matchingMatrix[$matchingObject1->individuality][$matchingObject2->individuality] * 0.706) +
                ($this->matchingMatrix[$matchingObject1->money][$matchingObject2->money] * 0.701) +
                ($this->matchingMatrix[$matchingObject1->adventure][$matchingObject2->adventure] * 0.698) +
                ($this->matchingMatrix[$matchingObject1->mood][$matchingObject2->mood] * 0.684) +
                ($this->matchingMatrix[$matchingObject1->optimistic][$matchingObject2->optimistic] * 0.63) +
                ($this->matchingMatrix[$matchingObject1->extroverted][$matchingObject2->extroverted] * 0.611) +
                ($this->matchingMatrix[$matchingObject1->conflict][$matchingObject2->conflict] * 0.597) +

                ($this->reverseMatchingMatrix[$matchingObject1->messy][$matchingObject2->messy] * 0.597) +
                ($this->reverseMatchingMatrix[$matchingObject1->logic][$matchingObject2->logic] * 0.649) +
                ($this->reverseMatchingMatrix[$matchingObject1->proactive][$matchingObject2->proactive] * 0.667) +
                ($this->reverseMatchingMatrix[$matchingObject1->calm][$matchingObject2->calm] * 0.667) +
                ($this->reverseMatchingMatrix[$matchingObject1->otherDs][$matchingObject2->otherDs] * 0.746) +
                ($this->reverseMatchingMatrix[$matchingObject1->bedDs][$matchingObject2->bedDs] * 0.835)
            ) / 59.584;

        // Alternatives to: 0.05, 0.6, 0.125
        // 0.062, 0.667, 0.124
        // 0.04, 0.5, 0.141
        if ($rawPercentage < 0.5) {
            $percentage = 0.5 - pow((0.5 - $rawPercentage) / 0.05, 0.6) * 0.125;
        } elseif ($rawPercentage > 0.5) {
            $percentage = 0.5 + pow(($rawPercentage - 0.5) / 0.05, 0.6) * 0.125;
        } else {
            $percentage = 0.5;
        }
        return $percentage;
    }

    public function recalculate($userId)
    {
        $myMatchingObject = $this->loadObject(array('user_id' => $userId));
        if (empty($myMatchingObject)) {
            $query = 'DELETE FROM vc_match WHERE min_user_id = ? OR max_user_id = ?';
            $executed = $this->getDb()->executePrepared($query, array($userId, $userId));
            if (!$executed) {
                \vc\lib\ErrorHandler::error(
                    'Error while calculating clearing matches.',
                    __FILE__,
                    __LINE__,
                    array('userId' => $userId)
                );
            }

            return;
        }

        $matchUserIds = array();
        // Only add matching to romantic ones
        $query = 'SELECT DISTINCT p2.id
                FROM vc_profile p1
                INNER JOIN vc_profile_field_search fs1 ON p1.id = ?
                           AND p1.id = fs1.profile_id AND fs1.field_value NOT IN (1, 5, 10)
                INNER JOIN vc_search_match m ON m.user_1_gender = p1.gender AND m.user_1_search = fs1.field_value
                INNER JOIN vc_profile_field_search fs2 ON m.user_2_search = fs2.field_value
                INNER JOIN vc_profile p2 ON
                p2.id = fs2.profile_id AND
                p2.active > 0 AND
                p1.id != p2.id AND
                m.user_2_gender = p2.gender AND
                p1.age BETWEEN p2.age_from_romantic AND p2.age_to_romantic AND
                p2.age BETWEEN p1.age_from_romantic AND p1.age_to_romantic';
        $statement = $this->getDb()->queryPrepared($query, array(intval($userId)));
        $statement->bind_result($matchUserId);
        while ($statement->fetch()) {
            // Skip yourself
            if ($matchUserId != $userId) {
                $matchUserIds[] = $matchUserId;
            }
        }
        $statement->close();

        if (empty($matchUserIds)) {
            $query = 'DELETE FROM vc_match WHERE min_user_id = ? OR max_user_id = ?';
            $executed = $this->getDb()->executePrepared($query, array($userId, $userId));
            if (!$executed) {
                \vc\lib\ErrorHandler::error(
                    'Error while calculating clearing matches.',
                    __FILE__,
                    __LINE__,
                    array('userId' => $userId)
                );
            }
        } else {
            $matchingObjects = $this->loadObjects(array('user_id' => $matchUserIds));

            $matchingUserIds = array();
            foreach ($matchingObjects as $matchingObject) {
                $matchingUserIds[] = $matchingObject->userId;

                $percentage = round($this->match($myMatchingObject, $matchingObject) * 100);
                $query = 'INSERT INTO vc_match SET ' .
                         'min_user_id = ?, max_user_id = ?, percentage = ? ' .
                         'ON DUPLICATE KEY UPDATE percentage = ?';
                $executed = $this->getDb()->executePrepared(
                    $query,
                    array(
                        min($myMatchingObject->userId, $matchingObject->userId),
                        max($myMatchingObject->userId, $matchingObject->userId),
                        intval($percentage),
                        intval($percentage)
                    )
                );

                if (!$executed) {
                    \vc\lib\ErrorHandler::error(
                        'Error while calculating match',
                        __FILE__,
                        __LINE__,
                        array('myMatchingObject.userId' => $myMatchingObject->userId,
                              'matchingObject.userId' => $matchingObject->userId,
                              'percentage' => $percentage)
                    );
                }
            }

            if (empty($matchingUserIds)) {
                $query = 'DELETE FROM vc_match WHERE min_user_id = ? OR max_user_id = ?';
                $executed = $this->getDb()->executePrepared(
                    $query,
                    array($userId, $userId)
                );
            } else {
                $query = 'DELETE FROM vc_match WHERE
                          (min_user_id = ? AND max_user_id NOT IN (' .
                            trim(str_repeat('?, ', count($matchingUserIds)), ', ') . ')) OR
                          (max_user_id = ? AND min_user_id NOT IN (' .
                            trim(str_repeat('?, ', count($matchingUserIds)), ', ') . '))';
                $executed = $this->getDb()->executePrepared(
                    $query,
                    array_merge(
                        array($userId),
                        $matchingUserIds,
                        array($userId),
                        $matchingUserIds
                    )
                );
            }
            if (!$executed) {
                \vc\lib\ErrorHandler::error(
                    'Error while calculating clearing matches: ' . $statement->errno . ' / ' . $statement->error,
                    __FILE__,
                    __LINE__,
                    array('userId' => $userId)
                );
            }
        }
    }
}
