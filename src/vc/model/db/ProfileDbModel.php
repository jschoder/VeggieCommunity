<?php
namespace vc\model\db;

class ProfileDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_profile';
    const OBJECT_CLASS = '\\vc\object\\Profile';

    public function getProfileIdByUserAndPassword($user, $password, $active = array(1, 2), $addSaltOnDemand = true)
    {
        // Make sure it is only numbers
        $activeFilter = implode(',', array_map('intval', $active));
        $query = 'SELECT id, password, salt FROM vc_profile WHERE
                         id = ? AND active IN (' . $activeFilter . ')
                    UNION
                    SELECT id, password, salt FROM vc_profile WHERE
                           email = ? AND active IN (' . $activeFilter . ')
                    UNION
                    SELECT id, password, salt FROM vc_profile WHERE
                           nickname = ? AND active IN (' . $activeFilter . ')';
        $statement = $this->getDb()->queryPrepared($query, array(intval($user), $user, $user));
        if (!$statement) {
            return null;
        }

        $statement->bind_result($id, $dbPassword, $salt);
        $fetched = $statement->fetch();
        $statement->close();
        if (!$fetched) {
            return null;
        }

        // Facebook Registration
        if ($dbPassword === null && $salt === null) {
            return null;
        }

        $saltedHashedPassword = sha1($salt . $password . $salt);
        if ($saltedHashedPassword === $dbPassword) {
            return $id;
        } else {
            return null;
        }
    }

    public function setSaltedPassword($id, $password, $ip)
    {
        $salt = $this->createToken(25);
        $saltedHashedPassword = sha1($salt . $password . $salt);

        $query = 'UPDATE vc_profile SET password = ?, salt = ? WHERE id = ?';
        $statement = $this->getDb()->prepare($query);
        $id = intval($id);
        $statement->bind_param('ssi', $saltedHashedPassword, $salt, $id);
        $executed = $statement->execute();
        if (!$executed) {
            $statement->close();
            \vc\lib\ErrorHandler::error(
                'Error while salting password: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('salt' => $salt,
                      'saltedHashedPassword' => $saltedHashedPassword)
            );
            return false;
        }
        $statement->close();

        $profilePasswordLogModel = $this->getDbModel('ProfilePasswordLog');
        $profilePasswordLogModel->addLog($id, $saltedHashedPassword, $salt, $ip);

        return true;
    }

    /*
     * Returns an array of ids from the given where clause.
     */
    public function getProfileIdsByColumn($field, $limit, $exclude = array())
    {
        $query =  'SELECT id FROM vc_profile WHERE active > 0';
        if (!empty($exclude)) {
            $query .= ' AND id NOT IN (' . $this->fillQuery(count($exclude)) . ')';
        }
        $query .= ' ORDER BY ' . $field . ' DESC LIMIT 0,' . intval($limit);

        $statement = $this->getDb()->queryPrepared($query, $exclude);
        $statement->bind_result($profileId);
        $profileIds = array();
        while ($statement->fetch()) {
            $profileIds[] = $profileId;
        }
        $statement->close();
        return $profileIds;
    }

    public function getProfiles($locale, $ids, $activeOnly = true)
    {
        if (empty($ids)) {
            return array();
        }

        $ids = array_map('intval', $ids);
        $searchFields = $this->getMultiValueField('search', $ids);
        $politicalFields = $this->getMultiValueField('political', $ids);
        $query = 'SELECT
            vc_profile.id,nickname,gender,age,hide_age,birth,zodiac,email,
            age_from_friends,age_to_friends,age_from_romantic,age_to_romantic,
            homepage,favlink1,favlink2,favlink3,
            postalcode,residence,region,country,vc_country.name_' . $locale . ',vc_country.iso2,
            nutrition,nutrition_freetext,smoking,alcohol,religion,children,marital,
            bodyheight,bodytype,clothing,haircolor,eyecolor,relocate,
            word1,word2,word3,latitude,longitude,
            tabQuestionaire1Hide,tabQuestionaire2Hide,tabQuestionaire3Hide,tabQuestionaire4Hide,
            tabQuestionaire5Hide,
            first_entry,last_update,last_login,last_chat_login,reminder_date,delete_date,admin,active,
            real_marker, plus_marker
            FROM vc_profile
            LEFT JOIN vc_country ON vc_country.id = vc_profile.country
            WHERE vc_profile.id IN (' . trim(str_repeat('?, ', count($ids)), ', ') . ')';
        if ($activeOnly) {
            $query .= ' AND vc_profile.active > 0';
        }

        $statement = $this->getDb()->queryPrepared($query, $ids);
        $statement->bind_result(
            $id,
            $nickname,
            $gender,
            $age,
            $hideAge,
            $birth,
            $zodiac,
            $email,
            $ageFromFriends,
            $ageToFriends,
            $ageFromRomantic,
            $ageToRomantic,
            $homepage,
            $favlink1,
            $favlink2,
            $favlink3,
            $postalcode,
            $residence,
            $region,
            $country,
            $countryName,
            $countryIso,
            $nutrition,
            $nutritionFreetext,
            $smoking,
            $alcohol,
            $religion,
            $children,
            $marital,
            $bodyheight,
            $bodytype,
            $clothing,
            $haircolor,
            $eyecolor,
            $relocate,
            $word1,
            $word2,
            $word3,
            $latitude,
            $longitude,
            $tabQuestionaire1Hide,
            $tabQuestionaire2Hide,
            $tabQuestionaire3Hide,
            $tabQuestionaire4Hide,
            $tabQuestionaire5Hide,
            $firstEntry,
            $lastUpdate,
            $lastLogin,
            $lastChatLogin,
            $reminderDate,
            $deleteDate,
            $admin,
            $active,
            $realMarker,
            $plusMarker
        );

        $profilesUnsorted = array();
        while ($statement->fetch()) {
            $profile = new \vc\object\Profile();

            $profile->id = $id;
            $profile->nickname = $nickname;
            $profile->gender = $gender;
            $profile->age = $age;
            $profile->hideAge = (boolean) $hideAge;
            $profile->birth = $birth;
            $profile->zodiac = $zodiac;
            $profile->email = $email;

            $profile->ageFromFriends = $ageFromFriends;
            $profile->ageToFriends = $ageToFriends;
            $profile->ageFromRomantic = $ageFromRomantic;
            $profile->ageToRomantic = $ageToRomantic;

            $profile->homepage = $homepage;
            $profile->favlink1 = $favlink1;
            $profile->favlink2 = $favlink2;
            $profile->favlink3 = $favlink3;

            $profile->postalcode = $postalcode;
            $profile->city = $residence;
            $profile->region = $region;
            $profile->country = $country;
            $profile->countryname = $countryName;
            $profile->countryIso = $countryIso;

            $profile->search = array_key_exists($id, $searchFields) ? $searchFields[$id] : array();
            $profile->nutrition = $nutrition;
            $profile->nutritionFreetext = $nutritionFreetext;
            $profile->smoking = $smoking;
            $profile->alcohol = $alcohol;
            $profile->religion = $religion;
            $profile->children = $children;
            $profile->political = array_key_exists($id, $politicalFields) ? $politicalFields[$id] : array();
            $profile->marital = $marital;
            $profile->bodyheight = $bodyheight;
            $profile->bodytype = $bodytype;
            $profile->clothing = $clothing;
            $profile->haircolor = $haircolor;
            $profile->eyecolor = $eyecolor;
            $profile->relocate = $relocate;

            $profile->word1 = $word1;
            $profile->word2 = $word2;
            $profile->word3 = $word3;

            $profile->latitude = $latitude;
            $profile->longitude = $longitude;

            $profile->tabQuestionaire1Hide = $tabQuestionaire1Hide;
            $profile->tabQuestionaire2Hide = $tabQuestionaire2Hide;
            $profile->tabQuestionaire3Hide = $tabQuestionaire3Hide;
            $profile->tabQuestionaire4Hide = $tabQuestionaire4Hide;
            $profile->tabQuestionaire5Hide = $tabQuestionaire5Hide;

            $profile->firstEntry = $firstEntry;
            $profile->lastUpdate = $lastUpdate;
            $profile->lastLogin = $lastLogin;
            $profile->lastChatLogin = $lastChatLogin;
            $profile->reminderDate = $reminderDate;
            $profile->deleteDate = $deleteDate;

            $profile->admin = $admin;
            $profile->active = $active;
            $profile->realMarker = $realMarker;
            $profile->plusMarker = $plusMarker;

            $profilesUnsorted[] = $profile;
        }
        $statement->close();

        // Sorting the profiles outside the sql-statement
        $profilesSorted = array();
        foreach ($ids as $id) {
            foreach ($profilesUnsorted as $profile) {
                if ($profile->id == $id) {
                    $profilesSorted[] = $profile;
                }
            }
        }
        return $profilesSorted;
    }

    public function getSmallProfiles($locale, $ids, $orderBy = "")
    {
        if (empty($ids)) {
            return array();
        }

        $query = 'SELECT
            vc_profile.id,nickname,gender,age,hide_age,
            nutrition,nutrition_freetext,
            residence,region,country,vc_country.name_' . $locale . ',vc_country.iso2,
            latitude,longitude,last_update,active, real_marker, plus_marker
            FROM vc_profile
            LEFT JOIN vc_country ON vc_country.id = vc_profile.country
            WHERE vc_profile.id IN (' . trim(str_repeat('?, ', count($ids)), ', ') . ')';

        $statement = $this->getDb()->queryPrepared($query, $ids);
        $statement->bind_result(
            $id,
            $nickname,
            $gender,
            $age,
            $hideAge,
            $nutrition,
            $nutritionFreetext,
            $residence,
            $region,
            $country,
            $countryName,
            $countryIso,
            $latitude,
            $longitude,
            $lastUpdate,
            $active,
            $realMarker,
            $plusMarker
        );

        $profilesUnsorted = array();
        while ($statement->fetch()) {
            $profile = new \vc\object\SmallProfile();

            $profile->id = $id;
            $profile->nickname = $nickname;
            $profile->gender = $gender;
            $profile->age = $age;
            $profile->hideAge = (boolean) $hideAge;

            $profile->nutrition = $nutrition;
            $profile->nutritionFreetext = $nutritionFreetext;

            $profile->city = $residence;
            $profile->region = $region;
            $profile->country = $country;
            $profile->countryname = $countryName;
            $profile->countryIso = $countryIso;

            $profile->latitude = $latitude;
            $profile->longitude = $longitude;

            $profile->lastUpdate = $lastUpdate;
            $profile->active = $active;
            $profile->realMarker = $realMarker;
            $profile->plusMarker = $plusMarker;

            $profilesUnsorted[] = $profile;
        }
        $statement->close();

        // Sorting the profiles outside the sql-statement
        if ($orderBy == "") {
            $profilesSorted = array();
            foreach ($ids as $id) {
                foreach ($profilesUnsorted as $profile) {
                    if ($profile->id == $id) {
                        $profilesSorted[] = $profile;
                    }
                }
            }
            return $profilesSorted;
        } else {
            return $profilesUnsorted;
        }
    }

    public function getQuestionaires($profileid)
    {
        $values = array(
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array(),
            5 => array(),
            6 => array()
        );

        $query = 'SELECT topic, item, content FROM vc_questionaire WHERE profileid = ? ORDER BY topic, item';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($profileid))
        );
        $statement->bind_result($topic, $item, $content);
        while ($statement->fetch()) {
            $values[$topic][$item] = $content;
        }
        $statement->close();
        return $values;
    }

    private function getMultiValueField($field, $ids)
    {
        $values = array();
        $query = 'SELECT profile_id, field_value FROM vc_profile_field_' . $field . '
                  WHERE profile_id IN (' . trim(str_repeat('?, ', count($ids)), ', ') . ')';
        $statement = $this->getDb()->queryPrepared($query, $ids);
        $statement->bind_result($profileId, $fieldValue);
        while ($statement->fetch()) {
            if (array_key_exists($profileId, $values)) {
                $values[$profileId][] = $fieldValue;
            } else {
                $values[$profileId] = array($fieldValue);
            }
        }
        $statement->close();
        return $values;
    }

    public function setMultiValueField($field, $profileId, $values, $deleteOldValues = true)
    {
        $profileId = intval($profileId);

        if (empty($values)) {
            if ($deleteOldValues) {
                $query = 'DELETE FROM vc_profile_field_' . $field . ' WHERE
                          profile_id = ?';
                $statement = $this->getDb()->prepare($query);
                $statement->bind_param('i', $profileId);
                $executed = $statement->execute();
                if (!$executed) {
                    \vc\lib\ErrorHandler::error(
                        'Error while deleting ALL old values for multiValueField: ' .
                        $statement->errno . ' / ' . $statement->error,
                        __FILE__,
                        __LINE__,
                        array('field' => $field,
                              'profileId' => $profileId,
                              'values' => $values,
                              'deleteOldValues' => $deleteOldValues)
                    );
                }
                $statement->close();
            }
        } else {
            $values = array_map('intval', $values);

            if ($deleteOldValues) {
                $query = 'DELETE FROM vc_profile_field_' . $field . ' WHERE
                          profile_id = ? AND field_value NOT IN(' . trim(str_repeat('?, ', count($values)), ', ') . ')';
                $queryParams = array_merge(
                    array($profileId),
                    $values
                );
                $this->getDb()->executePrepared($query, $queryParams);
            }

            $query = 'INSERT INTO vc_profile_field_' . $field . ' (profile_id, field_value)
                      VALUES ';
            $queryParams = array();
            foreach ($values as $value) {
                if (empty($queryParams)) {
                    $query .= '(?,?)';
                } else {
                    $query .= ',(?,?)';
                }
                $queryParams[] = $profileId;
                $queryParams[] = intval($value);
            }
            $query .= ' ON DUPLICATE KEY UPDATE field_value = field_value';
            $this->getDb()->executePrepared($query, $queryParams);
        }
    }

    public function deleteProfile($profileId, $reason, $newStatus = -1, $deleteMessages = false)
    {
        $updated = $this->update(
            array(
                'id '=> $profileId,
                'active !=' => intval($newStatus)
            ),
            array(
                'active' => intval($newStatus),
                'delete_reason' => $reason,
                'delete_date' => date('Y-m-d H:i:s')
            )
        );
        if (!$updated) {
            \vc\lib\ErrorHandler::error(
                'Error while deactivating profile',
                __FILE__,
                __LINE__,
                array(
                    'profileId' => $profileId,
                    'reason' => $reason,
                    'newStatus' => $newStatus)
            );
            return false;
        }

        $this->getDb()->executePrepared(
            'DELETE FROM vc_favorite WHERE favoriteid = ?',
            array($profileId)
        );

        // Remove all profile feed entries from this user from all feeds
        $this->getDb()->executePrepared(
            'DELETE vc_feed_thread FROM vc_feed_thread
            INNER JOIN vc_forum_thread
            ON vc_feed_thread.thread_id = vc_forum_thread.id
            AND vc_forum_thread.context_type = ' . \vc\config\EntityTypes::PROFILE . '
            INNER JOIN vc_profile ON vc_profile.id = vc_forum_thread.created_by
            WHERE vc_profile.id = ?',
            array($profileId)
        );

        // Delete the feed entries before deleting the actual friends
        $this->getDb()->executePrepared(
            'DELETE vc_forum_thread FROM vc_forum_thread
            INNER JOIN vc_friend ON
            vc_forum_thread.id = vc_friend.feed_thread_id AND
            (vc_friend.friend1id = ? OR vc_friend.friend2id = ?)',
            array($profileId, $profileId)
        );

        $this->getDb()->executePrepared(
            'DELETE FROM vc_friend WHERE friend1id = ? OR friend2id = ?',
            array($profileId, $profileId)
        );
        $this->getDb()->executePrepared(
            'DELETE FROM vc_last_visitor WHERE profile_id = ? OR visitor_id = ?',
            array($profileId, $profileId)
        );
        $this->getDb()->executePrepared(
            'UPDATE vc_persistent_login SET active=0 WHERE active = 1 AND profile_id = ?',
            array($profileId)
        );
        $this->getDb()->executePrepared(
            'DELETE FROM vc_real_picture ' .
            'WHERE real_check_id IN (SELECT id FROM vc_real_check WHERE profile_id = ?)',
            array($profileId)
        );
        $this->getDb()->executePrepared(
            'DELETE FROM vc_real_check WHERE profile_id = ?',
            array($profileId)
        );
        $this->getDb()->executePrepared(
            'DELETE FROM vc_group_member WHERE profile_id = ?',
            array($profileId)
        );
        $this->getDb()->executePrepared(
            'DELETE FROM vc_group_role WHERE profile_id = ?',
            array($profileId)
        );

        $query = 'SELECT g.id FROM vc_group g
                  LEFT JOIN vc_group_role gr ON g.id = gr.group_id
                  WHERE g.confirmed_at IS NOT NULL AND g.deleted_at IS NULL AND gr.profile_id IS NULL';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($groupId);
        $groupIds = array();
        while ($statement->fetch()) {
            $groupIds[] = $groupId;
        }
        $statement->close();
        foreach ($groupIds as $groupId) {
            $groupMemberModel = $this->getDbModel('GroupMember');
            $groupMemberObject = new \vc\object\GroupMember();
            $groupMemberObject->groupId = $groupId;
            $groupMemberObject->profileId = 1;
            $groupMemberObject->confirmedAt = date('Y-m-d H:i:s');
            $groupMemberModel->insertObject(null, $groupMemberObject);

            $groupRoleModel = $this->getDbModel('GroupRole');
            $groupRoleObject = new \vc\object\GroupRole();
            $groupRoleObject->groupId = $groupId;
            $groupRoleObject->profileId = 1;
            $groupRoleObject->role = \vc\object\GroupRole::ROLE_ADMIN;
            $groupRoleModel->insertObject(null, $groupRoleObject);
        }

        $this->getDb()->executePrepared(
            'DELETE FROM vc_event_participant WHERE profile_id = ?',
            array($profileId)
        );
        $this->getDb()->executePrepared(
            'DELETE FROM vc_custom_design WHERE profile_id = ?',
            array($profileId)
        );

        if ($deleteMessages) {
            $this->getDb()->executePrepared(
                'UPDATE vc_message SET recipientstatus=' . \vc\object\Mail::RECIPIENT_STATUS_SPAM_CONFIRMED .
                ', recipient_delete_date=NOW() WHERE senderid = ?',
                array($profileId)
            );
            $this->getDb()->executePrepared(
                'DELETE FROM vc_pm_thread WHERE min_user_id = ? OR max_user_id = ?',
                array($profileId, $profileId)
            );
        }

        return true;
    }

    public function activateProfile($profileId)
    {
        return $this->update(
            array(
                'id' => intval($profileId),
                'active' => 0
            ),
            array(
                'active' => 1
            )
        );
    }

    public function isPasswordValid($profileId, $password)
    {
        $query = "SELECT id FROM vc_profile WHERE
                  id = ? AND
                  ((
                    salt <> '' AND
                    password=SHA1(CONCAT(salt,?,salt))
                  ) OR (
                    salt = '' AND
                    password=MD5(?)
                  )) LIMIT 1";
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($profileId), $password, $password)
        );
        $hasResult = $statement->fetch();
        $statement->close();
        return $hasResult;
    }

    public function getActiveProfileIdByEMail($email)
    {
        $query = 'SELECT id FROM vc_profile WHERE email=? AND active>0';
        $statement = $this->getDb()->queryPrepared($query, array($email));
        $statement->bind_result($profileId);
        if (!$statement->fetch()) {
            $profileId = null;
        }
        $statement->close();
        return $profileId;
    }

    public function updatePlusMarker($userId, $plusMarkerSetting)
    {
        if ($plusMarkerSetting == 0) {
            $this->update(
                array('id' => $userId),
                array('plus_marker' => 0)
            );
        } else {
            $query = 'SELECT id FROM vc_plus WHERE user_id = ? AND ? BETWEEN start_date AND end_date';
            $statement = $this->getDb()->queryPrepared(
                $query,
                array(
                    intval($userId),
                    date('Y-m-d H:i:00')
                )
            );
            $hasPlus = $statement->fetch();
            $statement->close();

            if ($hasPlus) {
                $this->update(
                    array('id' => $userId),
                    array('plus_marker' => 1)
                );
            } else {
                $this->update(
                    array('id' => $userId),
                    array('plus_marker' => 0)
                );
            }
        }
    }

    public function getActiveFieldCount($field)
    {
        if ($field == 'search' || $field == 'political') {
            $query = 'SELECT field_value, count(*) FROM vc_profile_field_' . $field . '
                INNER JOIN vc_profile ON vc_profile.id = profile_id AND vc_profile.active > 0
                GROUP BY field_value';
        } else {
            $query = 'SELECT ' . $field . ', count(*) FROM vc_profile WHERE active > 0 GROUP BY ' . $field;
        }

        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $value,
            $count
        );

        $values = array();
        while ($statement->fetch()) {
            $values[$value] = $count;
        }
        $statement->close();
        return $values;
    }

    public function getGooglableProfiles()
    {
        $query = 'SELECT p.id, p.country, p.last_update
                  FROM vc_profile p
                  LEFT JOIN vc_setting s ON p.id = s.profileid AND s.`field` = 35
                  WHERE p.active > 0 AND (s.`value` = 1 OR  s.`value` IS NULL)';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $profileId,
            $country,
            $lastUpdate
        );

        $profiles = array();
        while ($statement->fetch()) {
            $profiles[$profileId] = array(
                'country' => $country,
                'lastUpdate' => strtotime($lastUpdate)
            );
        }
        $statement->close();
        return $profiles;
    }

    public function addVisit($profileId, $ip)
    {
        $query = 'INSERT IGNORE INTO vc_profile_visit
                  SET profile_id = ?,
                      ip = ?,
                      visit = ?';
        $today = date('Y-m-d');
        $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                $ip,
                $today
            )
        );
    }

    public function getVisitCount($profileId)
    {
        $query = 'SELECT vc_profile.counter + (SELECT count(*) FROM vc_profile_visit WHERE profile_id = ?)
                  FROM vc_profile WHERE id = ?';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(
                intval($profileId),
                intval($profileId)
            )
        );
        $statement->bind_result(
            $count
        );
        $statement->fetch();
        $statement->close();
        return $count;
    }

    public function addLastVisitor($profileId, $visitorId)
    {
        $query = 'INSERT INTO vc_last_visitor SET
                    profile_id = ?,
                    visitor_id = ?,
                    last_visit = ?
                  ON DUPLICATE KEY UPDATE last_visit = ?';
        $now = date('Y-m-d H:i:s');
        $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                intval($visitorId),
                $now,
                $now
            )
        );
    }

    public function getBlockedCountByEmail($email)
    {
        $query = 'SELECT count(*) FROM vc_profile
                  WHERE (email = ? OR email = sha1(?)) AND (active = -21 OR (active >= -60 AND active <= -50))';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(
                $email,
                $email
            )
        );
        $statement->bind_result(
            $count
        );
        $statement->fetch();
        $statement->close();
        return $count;
    }

    public function addReferer($url)
    {
        $query = 'INSERT INTO vc_registration_referer SET url = ?, registration_date = ?';
        $queryParams = array(
            substr($url, 0, 500),
            date('Y-m-d H:i:s')
        );
        return $this->getDb()->executePrepared($query, $queryParams);
    }

    public function getDeleteReasons($limit)
    {
        $query = 'SELECT id, nickname, email, delete_date, delete_reason, datediff(delete_date, first_entry) FROM vc_profile ' .
                 'WHERE active = -1 AND delete_reason != \'\' ORDER BY delete_date DESC LIMIT ' . intval($limit);
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $profileId,
            $nickname,
            $email,
            $deleteDate,
            $deleteReason,
            $activeDay
        );
        $deleteReasons = array();
        while ($statement->fetch()) {
            $deleteReasons[] = array(
                'profileId' => $profileId,
                'nickname' => $nickname,
                'email' => $email,
                'deleteDate' => $deleteDate,
                'deleteReason' => $deleteReason,
                'activeDay' => $activeDay
            );
        }
        $statement->close();
        return $deleteReasons;
    }
}
