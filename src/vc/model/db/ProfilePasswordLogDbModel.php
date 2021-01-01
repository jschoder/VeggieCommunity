<?php
namespace vc\model\db;

class ProfilePasswordLogDbModel extends AbstractDbModel
{
    public function addLog($profileId, $password, $salt, $ip)
    {
        $query = 'INSERT INTO vc_profile_password_log SET
                  profile_id = ?,
                  `password` = ?,
                  salt = ?,
                  ip = ?,
                  updated_at = ?';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                $profileId,
                $password,
                $salt,
                $ip,
                date('Y-m-d H:i:s')
            )
        );
        return $success;
    }
}