<?php
namespace vc\model\db;

class PictureWarningDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_picture_warning';
    const OBJECT_CLASS = '\\vc\object\\PictureWarning';

    public function getStates($profileId)
    {
        $query = 'SELECT
                  IF (deleted_at IS NOT NULL,
                      \'deleted\',
                      IF (own_pic_confirmed_at IS NOT NULL,
                          \'confirmed\',
                          \'open\'
                      )
                  ) as status,
                  count(*)
                  FROM vc_picture_warning
                  WHERE profile_id = ?
                  GROUP BY status';
        $statement = $this->getDb()->queryPrepared($query, array($profileId));
        $statement->bind_result(
            $state,
            $count
        );
        $states = array();
        while ($statement->fetch()) {
            $states[$state] = $count;
        }
        $statement->close();

        return $states;
    }
}
