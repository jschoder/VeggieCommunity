<?php
namespace vc\model\db;

class BannedEmailDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_banned_email';

    public function add($email)
    {
        $query = 'INSERT INTO vc_banned_email SET
                  email = ?,
                  added_at = ?,
                  last_occurrence = ?,
                  count = 1
                  ON DUPLICATE KEY UPDATE
                  last_occurrence = ?,
                  count = count + 1';
        $now = date('Y-m-d H:i:s');
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                $email,
                $now,
                $now,
                $now
            )
        );
        return $success;
    }
}
