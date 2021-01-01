<?php
namespace vc\model\db;

class NewsDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_news';

    public function add($userId, $textDe, $textEn)
    {
        $query = 'INSERT INTO vc_news SET
                  current = ?,
                  profileid = ?,
                  content_de = ?,
                  content_en = ?';
        $this->getDb()->executePrepared(
            $query,
            array(
                date('Y-m-d H:i:s'),
                $userId,
                $textDe,
                $textEn
            )
        );
    }


    public function getNews($profileId, $locale)
    {
        $values = array();
        $query = 'SELECT id, content_' . $locale . ' FROM vc_news ' .
                 'WHERE profileid = ? AND content_' . $locale . ' != "" ' .
                 'AND (expires_at IS NULL OR expires_at > NOW())' .
                 'ORDER BY id DESC';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($profileId))
        );
        $statement->bind_result($id, $content);
        while ($statement->fetch()) {
            $news = new \vc\object\News();
            $news->id = $id;
            $news->content = $content;
            $values[] = $news;
        }
        $statement->close();
        return $values;
    }

    public function deleteNews($id, $profileid)
    {
        return $this->delete(
            array(
                'id' => intval($id),
                'profileid' => intval($profileid)
            )
        );
    }
}
