<?php
namespace vc\model\db;

class WebsocketMessageDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_websocket_message';
    const OBJECT_CLASS = '\\vc\object\\WebsocketMessage';

    public function triggerMods($contextType)
    {
        $query = 'SELECT id FROM vc_profile WHERE admin % 8 > 3';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($profileId);
        $profileIds = array();
        while ($statement->fetch()) {
            $profileIds[] = $profileId;
        }
        $statement->close();

        foreach ($profileIds as $profileId) {
            $this->trigger($contextType, $profileId);
        }
    }

    public function trigger($contextType, $contextId)
    {
        $query = 'INSERT LOW_PRIORITY INTO vc_websocket_message SET
                  context_type = ?, context_id = ?
                  ON DUPLICATE KEY UPDATE context_id = context_id';
        $queryParams = array(
            intval($contextType),
            intval($contextId)
        );
        $this->getDb()->executePrepared($query, $queryParams);
    }

    public function getList()
    {
        $list = array();
        $query = 'SELECT context_type, context_id FROM vc_websocket_message LIMIT 500';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($contextType, $contextId);
        while ($statement->fetch()) {
            $list[] = array($contextType, $contextId);
        }
        $statement->close();
        return $list;
    }
}
