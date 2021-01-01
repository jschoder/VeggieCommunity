<?php
namespace vc\model\db;

class ChangePwTokenDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_change_pw_token';

    public function addUniqueToken($profileId, $createdAt, $ip)
    {
        do {
            $token = $this->createToken(8);
            $query = 'SELECT count(*) FROM vc_change_pw_token ' .
                     'WHERE profile_id = ' . intval($profileId) . ' AND ' .
                     'token = "' . $this->prepareSQL($token) . '"';
            $result = $this->getDb()->select($query);
            $row = $result->fetch_row();
            if ($row[0] > 0) {
                $token = null;
            }
        } while ($token === null);

        $query = 'INSERT INTO vc_change_pw_token SET
                    profile_id = ?,
                    token = ?,
                    created_at = ?,
                    ip = ?';
        $inserted = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                $token,
                date('Y-m-d H:i:s', intval($createdAt)),
                $ip
            )
        );

        if (!$inserted) {
            \vc\lib\ErrorHandler::error(
                'Unable to store password token',
                __FILE__,
                __LINE__,
                array(
                    'profileId' => $profileId,
                    'token' => $token,
                    'createdAt' => $createdAt,
                    'ip' => $ip
                )
            );
        }

        return $token;
    }

    public function isTokenValid($profileId, $token)
    {
        $query = 'SELECT count(*) FROM vc_change_pw_token ' .
                 'WHERE profile_id = ' . intval($profileId) . ' AND ' .
                 'token = "' . $this->prepareSQL($token) . '" AND ' .
                 'used_at IS NULL AND ' .
                 'created_at > DATE_SUB(NOW(), INTERVAL ' . \vc\config\Globals::TOKEN_EXPIRE_HOURS . ' HOUR)';
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        if ($row[0] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function setTokenUsed($profileId, $token, $usedAt)
    {
        return $this->update(
            array(
                'profile_id' => intval($profileId),
                'token' => $token,
                'used_at IS NULL'
            ),
            array(
                'used_at' => date('Y-m-d h:i:s', intval($usedAt))
            )
        );
    }
}
