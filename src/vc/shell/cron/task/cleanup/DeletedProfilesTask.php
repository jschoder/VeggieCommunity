<?php
namespace vc\shell\cron\task\cleanup;

class DeletedProfilesTask extends \vc\shell\cron\task\AbstractCronTask
{
    // :TODO: JOE - restructure class

    public function execute()
    {
        $this->removeDeprecated();
        $this->clearDataOfDeleted();
        $this->clearOldProfileContent();
    }

    //--------------------------------------------------------------------

    private function removeDeprecated()
    {
        $deprecated = array();

        $queryPostfix = "WHERE active=2 AND "
        . "last_login < DATE_SUB(NOW(), INTERVAL 2 MONTH) AND "
        . "reminder_date < DATE_SUB(NOW(), INTERVAL 1 MONTH) "
        . "ORDER BY reminder_date, last_login asc";
        $query = "SELECT id, nickname, email, reminder_date, last_update, last_login FROM vc_profile " . $queryPostfix;
        $result = $this->getDb()->select($query);

        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();

            $deprecated[] = array('id' => $row[0],
                                  'nickname' => $row[1],
                                  'email' => $row[2],
                                  'reminder_date' => $row[3],
                                  'last_update' => $row[4],
                                  'last_login' => $row[5]);
        }
        $this->setDebugInfo('deprecated', $deprecated);

        $query2 = "UPDATE LOW_PRIORITY vc_profile SET active=-23, delete_date=now() " . $queryPostfix;
        if (!$this->isTestMode()) {
            $profileModel = $this->getDbModel('Profile');
            foreach ($deprecated as $deleteUser) {
                $profileModel->deleteProfile(
                    intval($deleteUser['id']),
                    '',
                    -23
                );
            }
        }
    }

    //--------------------------------------------------------------------

    private function clearDataOfDeleted()
    {
        // Remove deleted friends
        $queryPostfix = "FROM vc_friend WHERE"
            . "    friend1id IN (SELECT id FROM vc_profile WHERE active<0)"
            . " OR friend2id IN (SELECT id FROM vc_profile WHERE active<0)";

        $query = "SELECT count(*) " . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('DeletedData.Friends', $row[0]);

        $query2 = "DELETE LOW_PRIORITY " . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->delete($query2);
            $this->setDebugInfo('DeletedData.Friends.AffectedRows', $affectedRows);
        }

        // Remove deleted favorites
        $queryPostfix = "FROM vc_favorite WHERE"
            . "    profileid IN (SELECT id FROM vc_profile WHERE active<0)"
            . " OR favoriteid IN (SELECT id FROM vc_profile WHERE active<0)";

        $query = "SELECT count(*) " . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('DeletedData.Favorites', $row[0]);

        $query2 = "DELETE LOW_PRIORITY " . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->delete($query2);
            $this->setDebugInfo('DeletedData.Favorites.AffectedRows', $affectedRows);
        }

        // clear activities from deleted profiles
        $queryPostfix = "FROM vc_activity WHERE profileid IN (SELECT id FROM vc_profile WHERE active<0)";

        $query = "SELECT count(*) " . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('DeletedData.Activity.DeletedProfiles', $row[0]);

        $query2 = "DELETE LOW_PRIORITY " . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->delete($query2);
            $this->setDebugInfo('DeletedData.Activity.DeletedProfiles.AffectedRows', $affectedRows);
        }

        // clear old activities (older than half a year)
        $queryPostfix = "FROM vc_activity WHERE created < DATE_SUB(NOW(),INTERVAL 12 month)";

        $query = "SELECT count(*) " . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('DeletedData.Activity.Old', $row[0]);

        $query2 = "DELETE LOW_PRIORITY " . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->delete($query2);
            $this->setDebugInfo('DeletedData.Activity.Old.AffectedRows', $affectedRows);
        }

        // clear last visitors
        $queryPostfix = "FROM vc_last_visitor WHERE visitor_id IN (SELECT id FROM vc_profile WHERE active<0)";

        $query = "SELECT count(*) " . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('DeletedData.LastVisitor', $row[0]);

        $query2 = "DELETE LOW_PRIORITY " . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->delete($query2);
            $this->setDebugInfo('DeletedData.LastVisitor.AffectedRows', $affectedRows);
        }

        // clearFriendRequests
        $queryPostfix = "FROM vc_friend WHERE friend2_accepted=0 AND created < DATE_SUB(NOW(), INTERVAL 2 MONTH)";

        $query = "SELECT count(*) " . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('DeletedData.FriendRequests', $row[0]);

        $query2 = "DELETE LOW_PRIORITY " . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $affectedRows = $this->getDb()->delete($query2);
            $this->setDebugInfo('DeletedData.FriendRequests.AffectedRows', $affectedRows);
        }
    }

    private function clearOldProfileContent()
    {
        $ids = array();
        $query = 'SELECT id FROM vc_profile ' .
             'WHERE ((delete_date < DATE_SUB(NOW(), INTERVAL 14 DAY) ' .
             'AND active < 0) OR active = -101) AND nutrition > 0 ' .
             'ORDER BY id';
        $result = $this->getDb()->select($query);
        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $ids[] = $row[0];
        }
        $this->setDebugInfo('ClearedContent.Ids', $ids);

        if (count($ids) > 0) {
            $query2 = "UPDATE LOW_PRIORITY vc_profile SET " .
                        "email=sha1(email)," .
                        "gender=0,"  .
                        "birth='0000-00-00',  "  .
                        "age=0,  "  .
                        "age_from_friends=0,  "  .
                        "age_to_friends=0,  "  .
                        "age_from_romantic=0,  "  .
                        "age_to_romantic=0,  "  .
                        "zodiac=0, "  .
                        "password=null, "  .
                        "salt='', "  .
                        "postalcode=null, "  .
                        "residence=null, "  .
                        "country=0, "  .
                        "region=null, "  .
                        "nutrition=0, "  .
                        "smoking=0, "  .
                        "alcohol=0, "  .
                        "religion=0, "  .
                        "children=0, "  .
                        "marital=0, "  .
                        "bodyheight=0, "  .
                        "bodytype=0, "  .
                        "clothing=0, "  .
                        "haircolor=0, "  .
                        "eyecolor=0, "  .
                        "relocate=0, "  .
                        "word1=null, "  .
                        "word2=null, "  .
                        "word3=null, "  .
                        "tabQuestionaire1Hide=0, "  .
                        "tabQuestionaire2Hide=0, "  .
                        "tabQuestionaire3Hide=0, "  .
                        "tabQuestionaire4Hide=0, "  .
                        "tabQuestionaire5Hide=0, "  .
                        "questionairelength=0, "  .
                        "last_update=null, "  .
                        "last_login=null, "  .
                        "reminder_date=null, "  .
                        "counter=0, "  .
                        "latitude=0, "  .
                        "longitude=0, "  .
                        "sin_latitude=0, "  .
                        "cos_latitude=0, "  .
                        "longitude_radius=0, "  .
                        "homepage=null, "  .
                        "favlink1=null, "  .
                        "favlink2=null, "  .
                        "favlink3=null, "  .
                        "admin=null "  .
                        "WHERE id IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->update($query2);
                $this->setDebugInfo('ClearedContent.Profiles', $affectedRows);
            }

            $query3 = "DELETE LOW_PRIORITY FROM vc_questionaire WHERE profileid IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                $affectedRows = $this->getDb()->delete($query3);
                $this->setDebugInfo('ClearedContent.Questionaire', $affectedRows);
            }

            $query4 = "DELETE LOW_PRIORITY FROM vc_setting WHERE profileid IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query4);
                $this->setDebugInfo('ClearedContent.Settings', $affectedRows);
            }

            $query5 = "DELETE LOW_PRIORITY FROM vc_profile_hobby WHERE profileid IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query5);
                $this->setDebugInfo('ClearedContent.ProfileHobbies', $affectedRows);
            }

            $query6 = "DELETE LOW_PRIORITY FROM vc_blocked WHERE profile_id IN (" . implode(',', $ids) . ") " .
                                                  "OR blocked_id IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query6);
                $this->setDebugInfo('ClearedContent.Blocked', $affectedRows);
            }

            $query7 = "DELETE LOW_PRIORITY FROM vc_news WHERE profileid IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query7);
                $this->setDebugInfo('ClearedContent.News', $affectedRows);
            }

            $query8 = "DELETE LOW_PRIORITY FROM vc_profile_visit WHERE profile_id IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query8);
                $this->setDebugInfo('ClearedContent.Counter', $affectedRows);
            }

            $query9 = "DELETE LOW_PRIORITY FROM vc_last_visitor WHERE profile_id IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query9);
                $this->setDebugInfo('ClearedContent.', $affectedRows);
            }

            $query10 = "DELETE LOW_PRIORITY FROM vc_picture WHERE profileid IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query10);
                $this->setDebugInfo('ClearedContent.Pictures', $affectedRows);
            }

            $query11 = "DELETE LOW_PRIORITY FROM vc_picture_warning WHERE profile_id IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query11);
                $this->setDebugInfo('ClearedContent.PictureWarnings', $affectedRows);
            }

            $query12 = "DELETE LOW_PRIORITY FROM vc_search WHERE profileid IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query12);
                $this->setDebugInfo('ClearedContent.Searches', $affectedRows);
            }

            $query13 = "DELETE LOW_PRIORITY FROM vc_searchstring_index WHERE profileid IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query13);
                $this->setDebugInfo('ClearedContent.SearchstringIndexes', $affectedRows);
            }

            $query14 = "DELETE LOW_PRIORITY FROM vc_match WHERE min_user_id IN (" . implode(',', $ids) .
                       ") OR max_user_id IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query14);
                $this->setDebugInfo('ClearedContent.Match', $affectedRows);
            }

            $query15 = "DELETE LOW_PRIORITY FROM vc_matching WHERE user_id IN (" . implode(',', $ids) . ")";
            if (!$this->isTestMode()) {
                // :TODO: JOE - deprecated
                $affectedRows = $this->getDb()->delete($query15);
                $this->setDebugInfo('ClearedContent.Matching', $affectedRows);
            }
        }
    }
}
