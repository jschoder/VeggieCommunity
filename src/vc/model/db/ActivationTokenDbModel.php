<?php
namespace vc\model\db;

class ActivationTokenDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_activation_token';
    const OBJECT_CLASS = '\\vc\object\\ActivationToken';

    public function addUniqueToken($profileId, $createdAt)
    {
        do {
            $token = $this->createToken(8);
            $query = 'SELECT count(*) FROM vc_activation_token ' .
                     'WHERE profile_id = ' . intval($profileId) . ' AND ' .
                     'token = "' . $this->prepareSQL($token) . '"';
            $result = $this->getDb()->select($query);
            $row = $result->fetch_row();
            if ($row[0] > 0) {
                $token = null;
            }
        } while ($token === null);

        $query = 'INSERT INTO vc_activation_token SET
                    profile_id = ?,
                    token = ?,
                    created_at = ?';
        $inserted = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                $token,
                date('Y-m-d H:i:s', intval($createdAt))
            )
        );

        if (!$inserted) {
            \vc\lib\ErrorHandler::error(
                'Unable to store activation token',
                __FILE__,
                __LINE__,
                array(
                    'profileId' => $profileId,
                    'token' => $token,
                    'createdAt' => $createdAt
                )
            );
        }

        return $token;
    }

    public function isTokenValid($profileId, $token)
    {
        $query = 'SELECT count(*) FROM vc_activation_token ' .
                 'WHERE profile_id = ' . intval($profileId) . ' AND ' .
                 'token = "' . $this->prepareSQL($token) . '" AND ' .
                 'used_at IS NULL';
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
