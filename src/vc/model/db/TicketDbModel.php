<?php
namespace vc\model\db;

class TicketDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_ticket';
    const OBJECT_CLASS = '\\vc\object\\Ticket';

    public function getHistory($profileId)
    {
        $query = 'SELECT vc_ticket.id, hash_id, category, subject, max(vc_ticket_message.created_at) as last_message_created_at, status
                  FROM vc_ticket
                  INNER JOIN vc_ticket_message ON vc_ticket.id = vc_ticket_message.ticket_id
                  WHERE profile_id = ? AND hash_id IS NOT NULL
                  GROUP BY vc_ticket.id
                  ORDER BY max(vc_ticket_message.created_at) DESC';
        $statement = $this->getDb()->queryPrepared($query, array(intval($profileId)));

        $statement->bind_result($id, $hashId, $category, $subject, $lastMessageCreatedAt, $status);
        $tickets = array();
        while ($statement->fetch()) {
            $tickets[] = array(
                'id' => $id,
                'hashId' => $hashId,
                'category' => $category,
                'subject' => $subject,
                'lastMessageCreatedAt' => $lastMessageCreatedAt,
                'status' => $status,
            );
        }
        $statement->close();

        return $tickets;
    }
}
