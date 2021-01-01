<?php
namespace vc\model\db;

class BlockedLoginDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_blocked_login';
    const OBJECT_CLASS = '\\vc\object\\BlockedLogin';

    public function isBlocked($userId)
    {
        $query = 'SELECT blocked_till FROM vc_blocked_login 
                  WHERE user_id = ? AND blocked_till > ?  ORDER BY blocked_till DESC LIMIT 1';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($userId), date('Y-m-d H:i:s'))
        );

        $blockedTill = true;
        $statement->bind_result($blockedTill);
        // Might return true/false.
        // I don't really care since if it can't find a row it will stay on the default null value.
        $statement->fetch();
        $statement->close();
        return $blockedTill;
    }
}
