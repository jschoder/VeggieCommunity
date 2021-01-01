<?php
namespace vc\model\db;

class HelpNotificationDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_help_notification';

    public function add($profileId, $ticketId)
    {
        $query = 'INSERT INTO vc_help_notification SET
                  profile_id = ?, ticket_id = ?
                  ON DUPLICATE KEY UPDATE ticket_id = ticket_id';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('ii', $profileId, $ticketId);
        $executed = $statement->execute();
        $statement->close();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while adding help notification: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('profileId' => $profileId,
                      'ticketId' => $ticketId)
            );
            return false;
        }

        $websocketMessageModel = $this->getDbModel('WebsocketMessage');
        $websocketMessageModel->trigger(\vc\config\EntityTypes::STATUS, $profileId);

        return true;
    }
}
