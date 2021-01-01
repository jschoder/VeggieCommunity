<?php
namespace vc\model\db;

class FavoriteDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_favorite';

    public function getFavoriteIds($profileId)
    {
        if (empty($profileId)) {
            return array();
        }
        return $this->getFieldList(
            'favoriteid',
            array('profileid' => intval($profileId))
        );
    }

    public function addFavorite($profileId, $favoriteId)
    {
        return $this->getDb()->executePrepared(
            'INSERT INTO vc_favorite SET profileid = ?, favoriteid = ?',
            array(
                intval($profileId),
                intval($favoriteId)
            )
        );
    }

    public function deleteFavorite($profileId, $favoriteId)
    {
        return $this->delete(
            array(
                'profileid' => intval($profileId),
                'favoriteid' => intval($favoriteId)
            )
        );
    }
}
