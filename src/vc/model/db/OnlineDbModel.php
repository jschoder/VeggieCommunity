<?php
namespace vc\model\db;

class OnlineDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_online';

    public function insertOnline($profileId)
    {
        $now = date('Y-m-d H:i:s');
        $query = 'INSERT INTO vc_online SET profile_id = ?, updated_at = ? ON DUPLICATE KEY UPDATE updated_at = ?';
        $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                $now,
                $now
            )
        );
    }
}
