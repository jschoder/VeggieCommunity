<?php
namespace vc\model\db;

class BlockedDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_blocked';

    public function addBlock($userId, $blockedId)
    {
        $query = 'INSERT INTO vc_blocked SET profile_id = ?, blocked_id = ?, created_at = ?';
        $blocked = $this->getDb()->executePrepared(
            $query,
            array(
                intval($userId),
                intval($blockedId),
                date('Y-m-d H:i:s')
            )
        );

        if ($blocked) {
            $this->getDb()->executePrepared(
                'DELETE FROM vc_friend WHERE
                (friend1id = ? AND friend2id = ?) OR
                (friend2id = ? AND friend1id = ?)',
                array(
                    intval($userId),
                    intval($blockedId),
                    intval($userId),
                    intval($blockedId)
                )
            );

            $this->getDb()->executePrepared(
                'DELETE FROM vc_favorite WHERE
                (profileid = ? AND favoriteid = ?) OR
                (favoriteid = ? AND profileid = ?)',
                array(
                    intval($userId),
                    intval($blockedId),
                    intval($userId),
                    intval($blockedId)
                )
            );

            $this->getDb()->executePrepared(
                'DELETE FROM vc_last_visitor WHERE
                (profile_id = ? AND visitor_id = ?) OR
                (visitor_id = ? AND profile_id = ?)',
                array(
                    intval($userId),
                    intval($blockedId),
                    intval($userId),
                    intval($blockedId)
                )
            );
        }
        return $blocked;
    }

    public function getBlocked($userId)
    {
        $query = 'SELECT profile_id, blocked_id FROM vc_blocked
                  WHERE (profile_id = ? OR blocked_id = ?) AND deleted_at IS NULL';
        $statement = $this->getDb()->queryPrepared($query, array($userId, $userId));
        $statement->bind_result($profileId, $blockedId);
        $blocked = array();
        while ($statement->fetch()) {
            if ($profileId == $userId) {
                $blocked[] = $blockedId;
            } else {
                $blocked[] = $profileId;
            }
        }
        $statement->close();
        return $blocked;
    }
}
