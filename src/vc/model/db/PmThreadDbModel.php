<?php
namespace vc\model\db;

class PmThreadDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_pm_thread';
    const OBJECT_CLASS = '\\vc\object\\PmThread';

    public function updateThreadByMessageIds($messageIds, $isNew = null)
    {
        $ids = array();
        $query = 'SELECT DISTINCT senderid, recipientid FROM vc_message ' .
                 'WHERE id IN (' . trim(str_repeat('?, ', count($messageIds)), ', ') . ')';
        $statement = $this->getDb()->queryPrepared($query, $messageIds);
        $statement->bind_result(
            $senderid,
            $recipientid
        );
        while ($statement->fetch()) {
                $ids[] = array($senderid, $recipientid);
        }
        $statement->close();

        foreach ($ids as $id) {
            $this->updateThread($id[0], $id[1]);
        }
    }

    public function updateThread($senderId, $recipientId, $senderNew = null, $recipientNew = null)
    {
        if ($senderId < $recipientId) {
            $minUserId = intval($senderId);
            $maxUserId = intval($recipientId);
            $senderNewField = 'min_user_is_new';
            $recipientNewField = 'max_user_is_new';
        } else {
            $minUserId = intval($recipientId);
            $maxUserId = intval($senderId);
            $senderNewField = 'max_user_is_new';
            $recipientNewField = 'min_user_is_new';
        }

        // At first the subqueries select the last visible message for sender and recipient
        // (Results might differ if the message is held back or if either one marked the last message as deleted)
        // The results are joined by a UNION which results in two rows and reduced to one row with the max-value
        // which will return a null for two nulls.
        // The results are then inserted into the correct table or updated with the "On Duplicate" at the end.

        // The conditions in the query are necessary to allow the same query once with updating "is_new" and one
        // for not doing so.
        $query = 'INSERT INTO vc_pm_thread
                  (min_user_id, max_user_id,
                   ' . ($senderNew === null ? '': $senderNewField . ',') . '
                   ' . ($recipientNew === null ? '': $recipientNewField . ',') . '
                   min_user_last_pm_id, min_user_last_update,
                   max_user_last_pm_id, max_user_last_update,
                   created_at)
                      SELECT
                      ? AS user_1_id, ? AS user_2_id,
                      ' . ($senderNew === null ? '': '? AS ' . $senderNewField . ',') . '
                      ' . ($recipientNew === null ? '': '? AS ' . $recipientNewField . ',') . '
                      MAX(user_1_pm_id), MAX(user_1_update),
                      MAX(user_2_pm_id), MAX(user_2_update),
                      ? AS created_at
                      FROM
                      (
                          SELECT
                          max(id) AS user_1_pm_id, max(created) AS user_1_update,
                          null AS user_2_pm_id, null AS user_2_update
                          FROM vc_message
                          WHERE
                          (senderid = ? AND recipientid = ? AND senderstatus > 0) OR
                          (senderid = ? AND recipientid = ? AND recipientstatus > 0)

                          UNION

                          SELECT
                          null AS user_1_pm_id, null AS user_1_update,
                          max(id) AS user_2_pm_id, max(created) AS user_2_update
                          FROM vc_message
                          WHERE
                          (senderid = ? AND recipientid = ? AND senderstatus > 0) OR
                          (senderid = ? AND recipientid = ? AND recipientstatus > 0)
                    ) as t
                  ON DUPLICATE KEY UPDATE
                     ' . ($senderNew === null ? '': $senderNewField. ' = VALUES(' . $senderNewField . '),') . '
                     ' . ($recipientNew === null ? '': $recipientNewField .' = VALUES(' . $recipientNewField . '),') . '
                     min_user_last_pm_id = VALUES(min_user_last_pm_id),
                     min_user_last_update = VALUES(min_user_last_update),
                     max_user_last_pm_id = VALUES(max_user_last_pm_id),
                     max_user_last_update = VALUES(max_user_last_update)';

        $queryParams = array();
        $queryParams[] = $minUserId;
        $queryParams[] = $maxUserId;
        if ($senderNew !== null) {
            $queryParams[] = $senderNew ? 1 : 0;
        }
        if ($recipientNew !== null) {
            $queryParams[] = $recipientNew ? 1 : 0;
        }
        $queryParams[] = date('Y-m-d H:i:s');
        $queryParams[] = $minUserId;
        $queryParams[] = $maxUserId;
        $queryParams[] = $maxUserId;
        $queryParams[] = $minUserId;
        $queryParams[] = $maxUserId;
        $queryParams[] = $minUserId;
        $queryParams[] = $minUserId;
        $queryParams[] = $maxUserId;

        $executed = $this->getDb()->executePrepared($query, $queryParams);
        return $executed;
    }

    public function getThreads($profileId, $limit, $before = null, $after = null, $nameFilter = null, $unreadOnly = false)
    {
        $basicQueryParams = array(
            intval($profileId),
            intval($profileId),
            intval($profileId),
            intval($profileId)
        );
        $minUserWhereQuery = 'min_user_id = ?';
        $minUserQueryParams = array(intval($profileId));
        $maxUserWhereQuery = 'max_user_id = ?';
        $maxUserQueryParams = array(intval($profileId));
        if (!empty($before)) {
            $minUserWhereQuery .= ' AND min_user_last_update < ?';
            $maxUserWhereQuery .= ' AND max_user_last_update < ?';
            $date = date('Y-m-d H:i:s', intval($before));
            $minUserQueryParams[] = $date;
            $maxUserQueryParams[] = $date;
        }
        if (!empty($after)) {
            $minUserWhereQuery .= ' AND min_user_last_update > ?';
            $maxUserWhereQuery .= ' AND max_user_last_update > ?';
            $date = date('Y-m-d H:i:s', intval($after));
            $minUserQueryParams[] = $date;
            $maxUserQueryParams[] = $date;
        }

        $whereQuery = array();
        $whereQueryParams = array();
        if (!empty($nameFilter)) {
            $whereQuery[] = 'vc_profile.nickname LIKE ?';
            $whereQueryParams[] = '%' . $nameFilter . '%';
        }
        if ($unreadOnly) {
            $whereQuery[] = 'pmt.is_new = 1';
        }

        $query = 'SELECT
                    pmt.contact,
                    vc_profile.nickname, vc_profile.gender, vc_profile.active,
                    vc_profile.real_marker, vc_profile.plus_marker,
                    vc_picture.filename, vc_message.body, vc_message.created, pmt.is_new
                  FROM
                    (SELECT
                        IF(min_user_id = ?, max_user_id, min_user_id) AS contact,
                        IF(min_user_id = ?, min_user_last_pm_id, max_user_last_pm_id) AS last_pm_id,
                        IF(min_user_id = ?, min_user_last_update, max_user_last_update) AS last_update,
                        IF(min_user_id = ?, min_user_is_new, max_user_is_new) AS is_new
                    FROM vc_pm_thread
                    WHERE (' . $minUserWhereQuery . ')  OR (' . $maxUserWhereQuery . ')) as pmt
                    INNER JOIN vc_profile ON vc_profile.id = pmt.contact
                    LEFT JOIN vc_picture ON vc_picture.profileid = pmt.contact AND vc_picture.defaultpic = 1
                    INNER JOIN vc_message ON vc_message.id = pmt.last_pm_id ' .
                    (empty($whereQuery) ? '' : 'WHERE ' . implode(' AND ', $whereQuery)) .
                    ' ORDER BY pmt.last_update DESC';

        $queryParams = array_merge($basicQueryParams, $minUserQueryParams, $maxUserQueryParams, $whereQueryParams);
        if (!empty($limit)) {
            $query .= ' LIMIT ?';
            $queryParams[] = intval($limit);
        }

        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $contactId,
            $contactNickname,
            $contactGender,
            $contactIsActive,
            $contactIsReal,
            $contactIsPlus,
            $contactPicture,
            $body,
            $created,
            $isNew
        );
        $threads = array();
        while ($statement->fetch()) {
            $lastMessage = str_replace(array("\r", "\n"), array('', ' '), $body);
            if (mb_strlen($lastMessage) > 46) {
                $lastMessage = mb_substr($lastMessage, 0, 46);
            }
            $threads[] = array(
                'contact' => array(
                    'id' => $contactId,
                    'nickname' => $contactNickname,
                    'gender' => $contactGender,
                    'isActive' => ($contactIsActive > 0),
                    'isReal' => ($contactIsReal > 0),
                    'isPlus' => ($contactIsPlus > 0),
                    'picture' => $contactPicture),
                'lastMessage' => $lastMessage,
                'created' => strtotime($created),
                'isNew' => $isNew);
            $contacts[] = $contactId;
        }
        $statement->close();

        $pmDbModel = $this->getDbModel('Pm');
        foreach($threads as &$thread) {
            $thread['unfilter'] = $pmDbModel->getUnfilter($profileId, $thread['contact']['id']);
        }

        return $threads;
    }


    public function getUnreadThreads($userId)
    {
        $query = 'SELECT count(*) FROM vc_pm_thread ' .
                 'WHERE (min_user_id = ? AND min_user_is_new = 1) OR (max_user_id = ? AND max_user_is_new = 1)';
        $statement = $this->getDb()->queryPrepared($query, array(intval($userId), intval($userId)));
        $statement->bind_result($count);
        $statement->fetch();
        return $count;
    }

    public function getContactUserBlock($blocked, $currentUser, $contact)
    {
        if (in_array($contact->id, $blocked)) {
            // Can't send a message to user if you are blocked. Even if you had contact before
            return gettext('pm.blocked');
        }

        // Almost always allow to continue an existing communication
        $pmThread = $this->getCount(
            array(
                'min_user_id' => min($currentUser->id, $contact->id),
                'max_user_id' => max($currentUser->id, $contact->id),
                '(min_user_last_pm_id IS NOT NULL OR max_user_last_pm_id IS NOT NULL)'
            )
        );
        if ($pmThread === 1) {
            return null;
        }

        // New users (and a list of existing users) has a block on how many new contacts they are allowed to create.
        if ($this->isMessageLimitReached($currentUser, $contact)) {
            return str_replace('%LIMIT%', \vc\config\Globals::MESSAGE_SPAM_LIMIT, gettext('compose.messagelimit.reached'));
        }

        // Check if the user has limited message to users from the same age range.
        $cacheModel = $this->getModel('Cache');
        $contactSettings = $cacheModel->getSettings($contact->id);
        if (intval($contactSettings->getValue(\vc\object\Settings::AGE_RANGE_FILTER))) {
            // Looking for nothing. Filter doesn't make sense
            if (!empty($contact->search)) {
                $searchingFriends = false;
                $searchingRomantic = false;
                foreach ($contact->search as $search) {
                    if ($search === 1 || $search === 5 || $search === 10) {
                        $searchingFriends = true;
                    } else {
                        $searchingRomantic = true;
                    }
                }

                if ($searchingRomantic &&
                    (
                        $currentUser->age < $contact->ageFromRomantic ||
                        $currentUser->age > $contact->ageToRomantic
                    ) &&
                    (
                        !$searchingFriends ||
                        $currentUser->age < $contact->ageFromFriends ||
                        $currentUser->age > $contact->ageToFriends
                    )) {
                    return gettext('pm.thread.blocked.romantic');
                } else if (
                    $searchingFriends &&
                    (
                        $currentUser->age < $contact->ageFromFriends ||
                        $currentUser->age > $contact->ageToFriends
                    ) && (
                        !$searchingRomantic ||
                        $currentUser->age < $contact->ageFromRomantic ||
                        $currentUser->age > $contact->ageToRomantic
                    )) {
                    return gettext('pm.thread.blocked.friends');
                }
            }
        }

        return null;
    }

    private function isMessageLimitReached($profile, $recipient)
    {
        if (!$this->doesUserHaveLimit($profile)) {
            return false;
        }

        // New contacts created by the same user in the last 24 hours
        $statement = $this->getDb()->queryPrepared(
            'SELECT count(*) FROM vc_pm_thread ' .
            'WHERE (min_user_id = ? or max_user_id = ?) and created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)',
            array(
                $profile->id,
                $profile->id
            )
        );
        $amount = 0;
        $statement->bind_result(
            $amount
        );
        $statement->fetch();
        $statement->close();

        return $amount > \vc\config\Globals::MESSAGE_SPAM_LIMIT;
    }

    private function doesUserHaveLimit($profile)
    {
        if (empty($profile)) {
            return false;
        // Users permanentely having limits
        } elseif (in_array($profile->id, array(22250, 25298, 20712, 25145))) {
            return true;
        } else {
            // 2 month (61 days) since registration
            $timeSinceRegistration = time() - strtotime($profile->firstEntry);
            return ($timeSinceRegistration < 5356800);// 86400 * 62
        }
    }

    public function getThreadReceiveSent($userId, $contactId)
    {
        $hasSentMessage = false;
        $hasReceivedMessage = false;

        $statement = $this->getDb()->queryPrepared(
            'SELECT id FROM vc_message WHERE senderid = ? AND recipientid = ? LIMIT 1',
            array($userId, $contactId)
        );
        if ($statement->fetch()) {
            $hasSentMessage = true;
        }
        $statement->close();

        $statement = $this->getDb()->queryPrepared(
            'SELECT id FROM vc_message WHERE senderid = ? AND recipientid = ? LIMIT 1',
            array($contactId, $userId)
        );
        if ($statement->fetch()) {
            $hasReceivedMessage = true;
        }
        $statement->close();

        if ($hasSentMessage && $hasReceivedMessage) {
            return \vc\object\PmThread::STATUS_BIDIRECTIONAL;
        } else if ($hasSentMessage) {
            return \vc\object\PmThread::STATUS_SENT;
        } else if ($hasReceivedMessage) {
            return \vc\object\PmThread::STATUS_RECEIVED;
        } else {
            return null;
        }
    }
}
