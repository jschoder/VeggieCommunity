<?php
namespace vc\model\db;

class ProfileEmailLogDbModel extends AbstractDbModel
{
    public function addLog($profileId, $email, $ip)
    {
        $query = 'INSERT INTO vc_profile_email_log SET
                  profile_id = ?,
                  email = ?,
                  ip = ?,
                  updated_at = ?';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                $profileId,
                $email,
                $ip,
                date('Y-m-d H:i:s')
            )
        );
        return $success;
    }
}