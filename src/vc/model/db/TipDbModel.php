<?php
namespace vc\model\db;

class TipDbModel extends AbstractDbModel
{
    public function getTips($locale)
    {
        $query = 'SELECT body FROM vc_tip WHERE locale = ? ORDER BY number ASC';
        $statement = $this->getDb()->queryPrepared($query, array($locale));

        $statement->bind_result($body);
        $tips = array();
        while ($statement->fetch()) {
            $tips[] = $body;
        }
        $statement->close();

        return $tips;
    }
}
