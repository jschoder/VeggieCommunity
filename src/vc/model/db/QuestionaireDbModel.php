<?php
namespace vc\model\db;

class QuestionaireDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_questionaire';

    public function getProfileTopics($profileId)
    {
        $query = 'SELECT DISTINCT topic FROM vc_questionaire WHERE profileid = ?';
        $statement = $this->getDb()->queryPrepared($query, array(intval($profileId)));
        $statement->bind_result(
            $topic
        );
        $topics = array();
        while ($statement->fetch()) {
            $topics[] = $topic;
        }
        $statement->close();
        return $topics;
    }

    public function insertUpdate($profileId, $topic, $item, $content)
    {
        if (empty($content)) {
            return $this->delete(
                array(
                    'profileid' => intval($profileId),
                    'topic' => intval($topic),
                    'item' => intval($item),
                )
            );
        } else {
            $query = 'INSERT INTO vc_questionaire SET
                      profileid = ?,
                      topic = ?,
                      item = ?,
                      content = ?
                      ON DUPLICATE KEY UPDATE
                      content = ?';
            return $this->getDb()->executePrepared(
                $query,
                array(
                    intval($profileId),
                    intval($topic),
                    intval($item),
                    $content,
                    $content
                )
            );
        }
    }
}
