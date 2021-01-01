<?php
namespace vc\model\db;

class UpdateDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_update';
    const OBJECT_CLASS = '\\vc\object\\Update';

    public function add($entityId, $entityType, $action, $contextType, $contextId)
    {
        $query = 'INSERT INTO vc_update SET '
            . 'entity_id = ?, entity_type = ?, action = ?, context_type = ?, context_id = ?, last_update = ? '
            . 'ON DUPLICATE KEY UPDATE last_update = ?';
        $statement = $this->getDb()->prepare($query);
        $now = date('Y-m-d H:i:s');
        $statement->bind_param(
            'iiiiiss',
            $entityId,
            $entityType,
            $action,
            $contextType,
            $contextId,
            $now,
            $now
        );
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while adding update: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('entityId' => $entityId,
                      'entityType' => $entityType,
                      'action' => $action,
                      'contextType' => $contextType,
                      'contextId' => $contextId)
            );
        }
        $statement->close();

        $websocketMessageModel = $this->getDbModel('WebsocketMessage');
        $websocketMessageModel->trigger($contextType, $contextId);
    }
}
