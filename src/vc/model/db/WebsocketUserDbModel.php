<?php
namespace vc\model\db;

class WebsocketUserDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_websocket_user';
    const OBJECT_CLASS = '\\vc\object\\WebsocketUser';

    public function add($userId)
    {
        $key = $this->getUniqueToken(
            $this::DB_TABLE,
            'websocket_key',
            64,
            false
        );

        $query = 'INSERT INTO vc_websocket_user SET
                  user_id = ?, websocket_key = ?, created_at = NOW()';
        $queryParams = array(
            intval($userId),
            $key
        );
        $this->getDb()->executePrepared($query, $queryParams);

        return $key;
    }
}
