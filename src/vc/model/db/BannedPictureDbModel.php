<?php
namespace vc\model\db;

class BannedPictureDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_banned_picture';
    const OBJECT_CLASS = '\\vc\object\\BannedPicture';

    public function getDeleteReasonsByFilehash($filehash)
    {
        $query = 'SELECT vc_profile.id, vc_profile.delete_reason
                  FROM vc_banned_picture
                  INNER JOIN vc_profile ON vc_profile.id = vc_banned_picture.profile_id
                  WHERE filehash = ?';
        $statement = $this->getDb()->queryPrepared($query, array($filehash));
        $statement->bind_result(
            $profileId,
            $deleteReason
        );
        $deleteReasons = array();
        while ($statement->fetch()) {
            $deleteReasons[$profileId] = $deleteReason;
        }
        $statement->close();
        return $deleteReasons;
    }
}
