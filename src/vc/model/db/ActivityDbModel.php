<?php
namespace vc\model\db;

class ActivityDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_activity';

    public function addReport($profileId, $activityType, $message = null, $relatedProfileId = null)
    {
        $query = sprintf(
            'SELECT count(*) FROM vc_activity WHERE ' .
            'profileid=%d AND activity_type=%d AND message%s AND related_profileid%s AND ' .
            'created > DATE_SUB(NOW(), INTERVAL %s)',
            $profileId,
            $activityType,
            empty($message) ? ' IS NULL' : ' = \'' . $this->getDb()->prepareSQL($message) . '\'',
            $relatedProfileId == null ? ' IS NULL' : ' = ' . intval($relatedProfileId),
            \vc\object\ActivityReport::HISTORY_PREVENTION
        );
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $result->free();
        if ($row[0] > 0) {
            return true;
        } else {
            $success = $this->getDb()->executePrepared(
                'INSERT INTO vc_activity SET
                    profileid = ?,
                    activity_type = ?,
                    message = ?,
                    related_profileid = ?,
                    created = ?',
                array(
                    $profileId,
                    $activityType,
                    empty($message) ? null : $message,
                    $relatedProfileId === null ? null : intval($relatedProfileId),
                    date('Y-m-d H:i:s')
                )
            );
            return $success;
        }
    }

    public function getReports($profileId, $confirmedFriendIds)
    {
        $reports = array();
        $confirmedFriendIds[] = $profileId;
        $friendList = implode(',', $confirmedFriendIds);

        $whereStatements = array();
        // Add to activities if friend has custom message, updated his/her profile,
        // added a picture or added a friend
        $whereStatements[] = sprintf(
            'activity_type IN (%s) AND (profileid IN (%s) OR related_profileid IN (%s))',
            implode(
                ',',
                array(\vc\object\ActivityReport::TYPE_CUSTOM_MESSAGE,
                      \vc\object\ActivityReport::TYPE_PROFILE_UPDATED,
                      \vc\object\ActivityReport::TYPE_PICTURE_ADDED,
                      \vc\object\ActivityReport::TYPE_FRIEND_ADDED)
            ),
            $friendList,
            $friendList
        );

        $query = 'SELECT id, profileid, activity_type, created, message, related_profileid ' .
                 'FROM vc_activity WHERE (' . implode(') OR (', $whereStatements) . ') ' .
                 'ORDER BY id DESC LIMIT 0, 30';
        $result = $this->getDb()->select($query);
        while ($row = $result->fetch_row()) {
            $reports[] = new \vc\object\ActivityReport(
                intval($row[0]),
                intval($row[1]),
                intval($row[2]),
                strtotime($row[3]),
                $row[4],
                $row[5]
            );
        }
        $result->free();
        return $reports;
    }
}
