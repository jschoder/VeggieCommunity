<?php
namespace vc\model\db;

class WatchlistDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_watchlist';

    public function add($profileId, $undesirable, $currentUser)
    {
        $now = date('Y-m-d H:i:s');
        return $this->getDb()->executePrepared(
            'INSERT INTO vc_watchlist SET profile_id = ?, undesirable = ?, created_by = ?, created_at = ? ' .
            'ON DUPLICATE KEY UPDATE undesirable = ?, created_by = ?, created_at = ?',
            array(
                $profileId,
                $undesirable,
                $currentUser,
                $now,
                $undesirable,
                $currentUser,
                $now
            )
        );
    }

    public function getList()
    {
        $query = 'SELECT profile_id, undesirable, created_at FROM vc_watchlist
                  INNER JOIN vc_profile ON vc_profile.id = profile_id AND vc_profile.active > 0
                  ORDER BY created_at DESC';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($profileId, $undesirable, $createdAt);
        $list = array();
        while ($statement->fetch()) {
            $list[$profileId] = array($undesirable, $createdAt);
        }
        $statement->close();

        return $list;
    }
}
