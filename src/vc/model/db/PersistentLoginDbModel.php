<?php
namespace vc\model\db;

class PersistentLoginDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_persistent_login';

    public function createPersistentLogin($profileId, $userAgent, $expiresAt)
    {
        do {
            $token = $this->createToken(25);
            $query = 'SELECT count(*) FROM vc_persistent_login ' .
                     'WHERE token = "' . $this->prepareSQL($token) . '"';
            $result = $this->getDb()->select($query);
            $row = $result->fetch_row();
            if ($row[0] > 0) {
                $token = null;
            }
        } while ($token === null);



        $query = 'INSERT INTO vc_persistent_login SET
                    profile_id = ?,
                    token = ?,
                    user_agent = ?,
                    expires_at = ?,
                    active = 1';
        $inserted = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                $token,
                $userAgent,
                date('Y-m-d h:i:s', intval($expiresAt))
            )
        );

        if (!$inserted) {
            \vc\lib\ErrorHandler::error(
                'Unable to store persistent login',
                __FILE__,
                __LINE__,
                array(
                    'profileId' => $profileId,
                    'userAgent' => $userAgent,
                    'expiresAt' => $expiresAt
                )
            );
        }

        return $token;
    }

    public function getPersistentLogin($profileId, $token)
    {
        $query = 'SELECT user_agent, expires_at, active FROM vc_persistent_login WHERE ' .
                 'profile_id = ' . intval($profileId) . ' AND ' .
                 'token = "' . $this->getDb()->prepareSQL($token) . '"';
        $result = $this->getDb()->select($query);
        if ($result->num_rows == 0) {
            return null;
        } else {
            $row = $result->fetch_row();
            return array('user_agent' => $row[0],
                         'expires_at' => $row[1],
                         'active' => $row[2]);
        }
    }

    public function setInactive($profileId, $token = null)
    {
        $queryParams = array(
            'profile_id' => intval($profileId)
        );
        if (!empty($token)) {
            $queryParams['token'] = $token;
        }
        return $this->update(
            $queryParams,
            array(
                'active' => 0
            )
        );
    }

    public function setExpiresAt($profileId, $token, $expiresAt)
    {
        return $this->update(
            array(
                'profile_id' => intval($profileId),
                'token' => $token
            ),
            array(
                'expires_at' => date('Y-m-d h:i:s', intval($expiresAt))
            )
        );
    }
}
