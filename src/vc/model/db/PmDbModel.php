<?php
namespace vc\model\db;

class PmDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_message';
    const OBJECT_CLASS = '\\vc\object\\Mail';

    public function create($recipientId, $recipientStatus, $profileID, $body, $sourcemailid)
    {
        $body = $this->stripIconChars($body);

        $query = 'INSERT INTO vc_message SET ' .
                'recipientid = ?, ' .
                'recipientstatus = ?, ' .
                'senderid = ?, ' .
                'senderstatus = ' . \vc\object\Mail::SENDER_STATUS_SENT . ', ' .
                'body = ?, ' .
                'body_hash = sha1(?), ' .
                'created=now()';
        $statement = $this->getDb()->prepare($query);
        $paramsBinded = $statement->bind_param(
            'iiiss',
            $recipientId,
            $recipientStatus,
            $profileID,
            $body,
            $body
        );
        $executed = $statement->execute();
        $statement->close();

        if ($executed) {
            if (!empty($sourcemailid) && $sourcemailid != '0') {
                \vc\lib\Assert::assertLong('sourcemailid', $sourcemailid, 1, 99999999, false);
                $query = 'UPDATE vc_message SET recipientreplied=1 WHERE id=?';
                $statement = $this->getDb()->prepare($query);
                $sourcemailid = intval($sourcemailid);
                $statement->bind_param('i', $sourcemailid);
                $executed = $statement->execute();
                $statement->close();
            }

            $pmThreadModel = $this->getDbModel('PmThread');
            if ($recipientStatus > 0) {
                $pmThreadModel->updateThread($profileID, $recipientId, false, true);
            } else {
                $pmThreadModel->updateThread($profileID, $recipientId, false, null);

                // Inform moderator about unsent message
                $websocketMessageModel = $this->getDbModel('WebsocketMessage');
                $websocketMessageModel->triggerMods(\vc\config\EntityTypes::STATUS);
            }

            $websocketMessageModel = $this->getDbModel('WebsocketMessage');
            $websocketMessageModel->trigger(\vc\config\EntityTypes::PM, $recipientId);
            $websocketMessageModel->trigger(\vc\config\EntityTypes::STATUS, $recipientId);

            return true;
        } else {
            return false;
        }
    }

    public function setMessageSent($id)
    {
        return $this->update(
            array(
                'id' => intval($id)
            ),
            array(
                'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_NEW
            )
        );
    }

    // Old Mailing system
    public function getAllUnsentMessages()
    {
        $query = 'SELECT
            m.id, m.subject, m.body, m.hide_email, m.created,
            s.id, s.nickname, s.email, s.first_entry,
            r.id, r.nickname, r.email,
            l.value, n.value, tc.ip, gc.iso2
            FROM vc_message m
            INNER JOIN vc_profile s on s.id = m.senderid
            INNER JOIN vc_profile r on r.id = m.recipientid
            LEFT JOIN (SELECT DISTINCT profile_id, ip FROM vc_termsofuse_confirm) AS tc
               ON m.senderid = tc.profile_id
            LEFT JOIN geoip_country gc ON INET_ATON(tc.ip) BETWEEN gc.ip_from AND gc.ip_to
            LEFT JOIN vc_setting l ON l.field = 31 AND l.profileid = r.id
            LEFT JOIN vc_setting n ON n.field = 12 AND n.profileid = r.id
            WHERE m.recipientstatus = ' . \vc\object\Mail::RECIPIENT_STATUS_UNSENT;
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $messageId,
            $subject,
            $body,
            $hideEmail,
            $created,
            $senderId,
            $senderNickname,
            $senderEmail,
            $senderSignup,
            $recipientId,
            $recipientNickname,
            $recipientEmail,
            $language,
            $notification,
            $senderIp,
            $senderIpIso
        );
        $messages = array();
        while ($statement->fetch()) {
            $messages[] = array(
                'id' => $messageId,
                'subject' => $subject,
                'body' => $body,
                'hide_email' => $hideEmail,
                'created' => $created,
                'sender' => array(
                    'id' => $senderId,
                    'nickname' => $senderNickname,
                    'email' => $senderEmail,
                    'signup' => $senderSignup,
                    'ip' => $senderIp,
                    'ip_iso' => $senderIpIso),
                'recipient' => array(
                    'id' => $recipientId,
                    'nickname' => $recipientNickname,
                    'email' => $recipientEmail),
                'language' => $language,
                'notification' => $notification,
            );
        }
        $statement->close();
        return $messages;
    }

    // New Mailing system

    public function markAllMessagesRead($profileId, $contactId)
    {
        return $this->update(
            array(
                'senderid' => intval($contactId),
                'recipientid' => intval($profileId),
                'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_NEW
            ),
            array(
                'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_READ
            ),
            false
        );
    }

    public function getMessages(
        $profileId,
        $contactId,
        $preFlagMessages,
        $limit,
        $before = null,
        $after = null,
        $textFilter = array(),
        $fromFilter = null,
        $toFilter = null
    ) {
        $query = 'SELECT vc_message.id, vc_message.senderid, vc_message.recipientid,
                         vc_message.subject, vc_message.body, vc_message.created, vc_message.recipientstatus
                  FROM vc_message
                  WHERE ((vc_message.senderid = ? AND vc_message.recipientid = ? AND vc_message.recipientstatus > 0)
                        OR
                        ( vc_message.senderid = ? AND vc_message.recipientid = ? AND vc_message.senderstatus > 0))';
        $queryParams = array();
        $queryParams[] = intval($contactId);
        $queryParams[] = intval($profileId);
        $queryParams[] = intval($profileId);
        $queryParams[] = intval($contactId);
        if (!empty($before)) {
            $query .= ' AND vc_message.id < ?';
            $queryParams[] = intval($before);
        }
        if (!empty($after)) {
            $query .= ' AND vc_message.id > ?';
            $queryParams[] = intval($after);
        }
        if (!empty($textFilter)) {
            foreach ($textFilter as $filterWord) {
                $filterWord = trim($filterWord);
                if (!empty($filterWord)) {
                    $query .=  ' AND (vc_message.subject LIKE ? OR vc_message.body LIKE ?)';
                    $queryParams[] = '%' . $filterWord. '%';
                    $queryParams[] = '%' . $filterWord. '%';
                }
            }
        }
        if (!empty($fromFilter)) {
            $query .= ' AND vc_message.created >= ?';
            $queryParams[] = date('Y-m-d H:i:s', intval($fromFilter));
        }
        if (!empty($toFilter)) {
            $query .= ' AND vc_message.created <= ?';
            $queryParams[] = date('Y-m-d H:i:s', intval($toFilter));
        }
        $query .= ' ORDER BY vc_message.id DESC';
        if (!empty($limit)) {
            $query .= ' LIMIT ?';
            $queryParams[] = intval($limit);
        }

        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result($id, $senderId, $recipientId, $subject, $body, $created, $recipientStatus);

        $messages = array();
        while ($statement->fetch()) {
            if ($profileId == $senderId) {
                $isNew = 0;
            } elseif ($recipientStatus == \vc\object\Mail::RECIPIENT_STATUS_NEW) {
                $isNew = 1;
            } else {
                $isNew = 0;
            }

            $messages[] = array(
                'id' => $id,
                'senderid' => $senderId,
                'recipientid' => $recipientId,
                'subject' => $subject,
                'body' => $body,
                'created' => strtotime($created),
                'isNew' => $isNew,
                'preFlag' => null,
                'identical' => intval(0));
        }
        $statement->close();

        if ($preFlagMessages) {
            foreach ($messages as &$message) {
                if ($message['senderid'] === $contactId) {
                    foreach (\vc\config\Globals::$filterKeywords as $type => $words) {
                        foreach ($words as $word) {
                            if (preg_match('/(^|[^\w])' . $word . '($|[^\w])/i', $message['body'])) {
                                $unfilter = $this->getUnfilter($profileId, $contactId);
                                if (!in_array($type, $unfilter)) {
                                    $message['preFlag'] = $type;
                                }
                            }
                        }
                    }
                }
            }
        }

        return array_reverse($messages);
    }

    public function getUnfilter($profileId, $contactId)
    {
        $unfilters = array();

        // User is in the Sexanfragen-Group
        $cacheModel = $this->getModel('Cache');
        $contactRelations = $cacheModel->getProfileRelations($contactId);
        $contactGroups = $contactRelations[\vc\model\CacheModel::RELATIONS_GROUPS];
        if (array_key_exists('ft2euk0', $contactGroups)) {
            $unfilters[] = 'sx';
        } else {
            // User has received at least 20 messages from the other person
            // (Only makes sense to test if the recipient isn't in the sex message group)
            $receivedMessageCount = $this->getCount(array(
                'senderid' => $contactId,
                'recipientid' => $profileId
            ));
            if ($receivedMessageCount > 19) {
                $unfilters[] = 'sx';
            }
        }
        return $unfilters;
    }

    public function isUserWhitelisted($currentUser, $recipientId)
    {
        // User has received a message DOESN'T guarantee
        // since people have sent messages to spamprofiles before
        // User has received a message from the sender
        $query = 'SELECT id FROM vc_message WHERE recipientid = ? AND senderid = ? LIMIT 1';
        $queryParams = array(
            intval($currentUser->id),
            intval($recipientId)
        );

        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $hasAMessage = $statement->fetch();
        $statement->close();

        return $hasAMessage;
    }

    public function isUserHardBlacklisted($ip, $senderId, $body, $recipientId)
    {
        return $this->isTextBlacklisted($body) ||
               $this->isSuspicionBlock($ip, $senderId) ||
               $this->hasBannedCountryLogin($senderId);
    }

    public function isSuspicionBlock($ip, $senderId)
    {
        $suspicionModel = $this->getDbModel('Suspicion');
        $suspicionCount = $suspicionModel->getCount(
            array(
                '(profile_id = ? OR ip = ?)' => array($senderId, $ip),
                'type' => array(
                    \vc\model\db\SuspicionDbModel::TYPE_SPAM_BLOCKED_PIC,
                    \vc\model\db\SuspicionDbModel::TYPE_SPAM_ILLEGAL_COUNTRY_LOGIN,
                    \vc\model\db\SuspicionDbModel::TYPE_SPAM_BLOCKED_USER_LOGIN_ATTEMPT
                )
            )
        );
        return $suspicionCount > 0;
    }

    public function isTextBlacklisted($body)
    {
        $strippedBody = str_replace(array("\r", "\n"), '', $body);
        if (stripos($strippedBody, 'Hello Dear New Friend') !== false ||
            stripos($strippedBody, 'i have something very IMPORTANT to tell you') !== false ||
            stripos($strippedBody, 'i can give you my picture') !== false ||
            stripos($strippedBody, 'my pictures to you') !== false ||
            stripos($strippedBody, 'my picture for you') !== false ||
            stripos($strippedBody, 'Sincere emotions are very difficult to express') !== false ||
            stripos($strippedBody, 'interest me in having communication') !== false ||
            stripos($strippedBody, 'i am interested to have good relationship with you') !== false ||
            stripos($strippedBody, 'at(veggiecommunity.org)') !== false ||
            stripos($strippedBody, 'solo66') !== false ||
            stripos($strippedBody, 'at(https://www.veggiecommunity.org)') !== false) {
            return true;
        }

        return false;
    }

    public function hasBannedCountryLogin($senderId)
    {
        if (in_array($senderId, \vc\config\Globals::$blockedCountryUserWhitelist)) {
            return false;
        }

        $query = 'SELECT count(*)
                  FROM (
                      SELECT DISTINCT profile_id, INET_ATON(ip) as ip FROM vc_user_ip_log
                  ) as tmp
                  INNER JOIN geoip_country
                    ON tmp.ip >= geoip_country.ip_from AND
                       tmp.ip <= geoip_country.ip_to AND
                       iso2 IN (\'' . implode('\',\'', \vc\config\Globals::$blockedCountries) . '\')
                  INNER JOIN vc_profile ON vc_profile.id = tmp.profile_id
                  WHERE tmp.profile_id = ?';
        $statement = $this->getDb()->queryPrepared($query, array($senderId));
        $statement->bind_result(
            $count
        );
        $statement->close();
        if ($count > 0) {
            return true;
        }

        return false;
    }

    public function getSpamMessages()
    {
        $query = 'SELECT
	          vc_profile.id, vc_profile.nickname, vc_profile.email, vc_profile.first_entry,
                  vc_profile.last_update, vc_profile.last_login, vc_message.recipientid
                  FROM vc_profile
                  INNER JOIN vc_message ON vc_profile.id=vc_message.senderid
                  WHERE vc_message.recipientstatus=' . \vc\object\Mail::RECIPIENT_STATUS_SPAM_SUSPECT . ' AND
                        vc_profile.active>0
                  ORDER BY id DESC';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $profileId,
            $profileNickname,
            $profileEmail,
            $profileFirstEntry,
            $profileLastUpdate,
            $profileLastLogin,
            $recipientId
        );
        $spamMessages = array();
        while ($statement->fetch()) {
            $spamMessages[] = array(
                'profileId' => $profileId,
                'profileNickname' => $profileNickname,
                'profileEmail' => $profileEmail,
                'profileFirstEntry' => $profileFirstEntry,
                'profileLastUpdate' => $profileLastUpdate,
                'profileLastLogin' => $profileLastLogin,
                'recipientId' => $recipientId

            );
        }
        $statement->close();

        return $spamMessages;
    }

    public function getAllPms($senderId, $recipientId = null, $limit = 5000)
    {
        $query = 'SELECT id, senderid, recipientid, subject, body, recipientstatus, created ' .
                 'FROM vc_message WHERE ';
        $queryParams = array();
        if ($recipientId === null) {
            $query .= 'senderid = ?';
            $queryParams[] = intval($senderId);
        } else {
            $query .= '(senderid = ? AND recipientid = ?) OR (senderid = ? AND recipientid = ?)';
            $queryParams[] = intval($senderId);
            $queryParams[] = intval($recipientId);
            $queryParams[] = intval($recipientId);
            $queryParams[] = intval($senderId);
        }
        $query .= ' ORDER BY id DESC LIMIT ' . intval($limit);
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $id,
            $senderId,
            $recipientId,
            $subject,
            $body,
            $recipientstatus,
            $created
        );
        $messages = array();
        while ($statement->fetch()) {
            $messages[] = array(
                'id' => $id,
                'senderId' => $senderId,
                'recipientId' => $recipientId,
                'subject' => $subject,
                'body' => $body,
                'recipientStatus' => $recipientstatus,
                'created' => $created
            );
        }
        $statement->close();

        return array_reverse($messages);
    }

    public function getSpamCount()
    {
        $query = 'SELECT count(*) FROM vc_message
                  INNER JOIN vc_profile ON vc_message.senderid = vc_profile.id AND vc_profile.active > 0
                  WHERE recipientstatus = ' . \vc\object\Mail::RECIPIENT_STATUS_SPAM_SUSPECT;
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($count);
        $statement->fetch();
        $statement->close();
        return $count;
    }

    public function containsSpamBlacklisting($text)
    {
        if (strpos($text, '/dot/c/o/m') !== false ||
            strpos($text, '-dot-c-o-m') !== false) {
            return true;
        }
        return false;
    }

    public function mayContainSpamPotential($text)
    {
        if (strpos($text, '@') !== false ||
            strpos($text, '(at)') !== false ||
            strpos($text, 'http') !== false ||
            strpos($text, 'www') !== false ||
            strpos($text, ' c o m') !== false) {
            return true;
        }
        return false;
    }

    public function getRecipientStatusCount($profileId)
    {
        $query = 'SELECT recipientstatus, count(id) FROM vc_message WHERE senderid = ? GROUP BY recipientstatus';
        $statement = $this->getDb()->queryPrepared($query, array(intval($profileId)));
        $statement->bind_result(
            $recipientStatus,
            $messageCount
        );
        $statusCounts = array();
        while ($statement->fetch()) {
            $statusCounts[$recipientStatus] = $messageCount;
        }
        $statement->close();
        return $statusCounts;
    }
}
