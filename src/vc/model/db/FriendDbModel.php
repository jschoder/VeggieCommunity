<?php
namespace vc\model\db;

class FriendDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_friend';
    const OBJECT_CLASS = '\\vc\object\\Friend';

    public function getFriends($profileid, $filter, $order = false)
    {
        $fields = array();
        $fields[] = 'friend1id';
        $fields[] = 'friend2id';

        if ($filter === \vc\object\Friend::FILTER_CONFIRMED) {
            $where = sprintf(
                '(friend1id=%d OR friend2id=%d) AND friend2_accepted=1',
                intval($profileid),
                intval($profileid)
            );
        } elseif ($filter === \vc\object\Friend::FILTER_TO_CONFIRM) {
            $where = sprintf(
                'friend2id=%d AND friend2_accepted=0',
                intval($profileid)
            );
        } else { // if ($filter === \vc\object\Friend::FILTER_ALL)
            $where = sprintf(
                'friend1id=%d OR friend2id=%d',
                intval($profileid),
                intval($profileid)
            );
        }
        $query = 'SELECT ' . implode(',', $fields) . ' FROM vc_friend WHERE ' . $where;
        $result = $this->getDb()->select($query);
        $friends = array();
        $friendIds = array();
        while ($row = $result->fetch_row()) {
            $friend = new \vc\object\Friend();
            if ($row[0] == $profileid) {
                $friend->friendid = $row[1];
                $friendIds[] = $row[1];
            } elseif ($row[1] == $profileid) {
                $friend->friendid = $row[0];
                $friendIds[] = $row[0];
            }
            $friends[$friend->friendid] = $friend;
        }
        $result->free();

        if ($order && count($friendIds) > 0) {
            $sortedFriends = array();
            $query = 'SELECT id FROM vc_profile ' .
                     'WHERE id IN (' . implode(',', $friendIds) . ') ' .
                     'ORDER BY last_update DESC';
            $result = $this->getDb()->select($query);
            while ($row = $result->fetch_row()) {
                // Durch das erneute Setzen der Variablen wird der Index nicht verändert
                // aber die Reihenfolge in der, die Profile ins array hinzugefügt werden.
                $sortedFriends[$row[0]] = $friends[$row[0]];
            }
            $friends = $sortedFriends;
            $result->free();
        }
        return $friends;
    }

    public function getFriendIds($profileId, $filter)
    {
        if (empty($profileId)) {
            return array();
        }

        $friends = array();
        if ($filter === \vc\object\Friend::FILTER_CONFIRMED) {
            $where = '(friend1id = ? OR friend2id = ?) AND friend2_accepted=1';
            $queryParams = array(
                intval($profileId),
                intval($profileId)
            );
        } elseif ($filter === \vc\object\Friend::FILTER_TO_CONFIRM) {
            $where = 'friend2id = ? AND friend2_accepted = 0';
            $queryParams = array(
                intval($profileId)
            );
        } elseif ($filter === \vc\object\Friend::FILTER_WAIT_FOR_CONFIRM) {
            $where = 'friend1id = ? AND friend2_accepted = 0';
            $queryParams = array(
                intval($profileId)
            );
        } else { // if ($filter === \vc\object\Friend::FILTER_ALL)
            $where = 'friend1id = ? OR friend2id = ?';
            $queryParams = array(
                intval($profileId),
                intval($profileId)
            );
        }
        $query = 'SELECT friend1id, friend2id FROM vc_friend WHERE ' . $where;
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $friend1id,
            $friend2id
        );
        while ($statement->fetch()) {
            if ($friend1id == $profileId) {
                $friends[] = $friend2id;
            } else {
                $friends[] = $friend1id;
            }
        }
        $statement->close();

        return $friends;
    }

    public function getFullFriendList($profileId)
    {
        $query = 'SELECT friend1id, friend2id, friend2_accepted FROM vc_friend WHERE ' .
                 'friend1id = ? OR friend2id = ?';
        $statement = $this->getDb()->queryPrepared($query, array($profileId, $profileId));
        $statement->bind_result(
            $friend1id,
            $friend2id,
            $accepted
        );
        $friends = array();
        while ($statement->fetch()) {
            $friends[] = array($friend1id, $friend2id, $accepted);
        }
        $statement->close();

        return $friends;
    }

    public function addFriend($profileId, $friendId)
    {
        $query = 'INSERT INTO vc_friend
                  SET friend1id = ?, friend2id = ?, friend2_accepted = 0';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                intval($friendId)
            )
        );
        if ($success) {
            $websocketMessageModel = $this->getDbModel('WebsocketMessage');
            $websocketMessageModel->trigger(\vc\config\EntityTypes::STATUS, $friendId);
        }
        return $success;
    }

    public function acceptRequest($profileId, $friendId)
    {
        $query = 'UPDATE vc_friend
                  SET friend2_accepted = 1
                  WHERE friend2id = ? AND friend1id = ?';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                intval($friendId)
            )
        );
        if ($success) {
            $websocketMessageModel = $this->getDbModel('WebsocketMessage');
            $websocketMessageModel->trigger(\vc\config\EntityTypes::STATUS, $profileId);
        }
        return $success;
    }

    public function denyRequest($profileId, $friendId)
    {
        $query = 'DELETE FROM vc_friend
                  WHERE (friend1id = ? AND friend2id = ?) OR
                        (friend2id = ? AND friend1id = ?)';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                intval($friendId),
                intval($profileId),
                intval($friendId)
            )
        );
        if ($success) {
            $websocketMessageModel = $this->getDbModel('WebsocketMessage');
            $websocketMessageModel->trigger(\vc\config\EntityTypes::STATUS, $profileId);
        }
        return $success;
    }

    public function deleteFriend($profileId, $friendId)
    {
        $query = 'DELETE FROM vc_friend
                  WHERE (friend1id = ? AND friend2id = ?) OR
                        (friend2id = ? AND friend1id = ?)';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                intval($friendId),
                intval($profileId),
                intval($friendId)
            )
        );
        return $success;
    }

    public function getFriendsToNotify($profileId)
    {
        $query = 'SELECT profileid FROM vc_setting WHERE
                  field=' . \vc\object\Settings::FRIEND_CHANGED_NOTIFICATION . ' AND value=1 AND
                  (
                      profileid IN (SELECT friend2id FROM vc_friend WHERE friend1id = ? AND friend2_accepted=1) OR
                      profileid IN (SELECT friend1id FROM vc_friend WHERE friend2id = ? AND friend2_accepted=1)
                  )';
        $statement = $this->getDb()->queryPrepared($query, array($profileId, $profileId));
        $statement->bind_result(
            $friendId
        );
        $friendIds = array();
        while ($statement->fetch()) {
            $friendIds[] = $friendId;
        }
        $statement->close();

        return $friendIds;
    }
}
