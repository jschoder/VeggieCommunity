<?php
namespace vc\model\db;

class FeedThreadDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_feed_thread';

    public function addThread($threadId, $friendId)
    {
        $query = 'INSERT INTO vc_feed_thread (user_id, thread_id)
                  SELECT
                    IF(vc_friend.friend1id = ?, vc_friend.friend2id, vc_friend.friend1id) AS friend_id,
                    vc_forum_thread.id
                  FROM vc_forum_thread
                  INNER JOIN vc_friend
                    ON (vc_friend.friend1id = ? OR vc_friend.friend2id = ?) AND vc_friend.friend2_accepted = 1
                  WHERE vc_forum_thread.context_type = 1 AND vc_forum_thread.id = ?
                  ON DUPLICATE KEY UPDATE thread_id = thread_id';
        return $this->getDb()->executePrepared(
            $query,
            array(
                $friendId,
                $friendId,
                $friendId,
                $threadId
            )
        );
    }

    public function addAllThreads($sourceUserId, $targetFeedUserId)
    {
        $query = 'INSERT INTO vc_feed_thread (user_id, thread_id)
                  SELECT ? AS friend_id, vc_forum_thread.id
                  FROM vc_forum_thread
                  WHERE vc_forum_thread.context_type = 1 AND vc_forum_thread.context_id = ?
                  ON DUPLICATE KEY UPDATE thread_id = thread_id';
        return $this->getDb()->executePrepared($query, array(intval($targetFeedUserId), intval($sourceUserId)));
    }

    public function removeAllThreads($sourceUserId, $targetFeedUserId = null)
    {
        $query = 'DELETE vc_feed_thread FROM vc_feed_thread
                  INNER JOIN vc_forum_thread
                  ON vc_feed_thread.thread_id = vc_forum_thread.id
                  AND vc_forum_thread.context_type = 1
                  AND vc_forum_thread.context_id = ?';
        $queryParams = array($sourceUserId);
        if ($targetFeedUserId !== null) {
            $query .= ' WHERE vc_feed_thread.user_id = ?';
            $queryParams[] = intval($targetFeedUserId);
        }
        return $this->getDb()->executePrepared($query, $queryParams);
    }

    public function getFeedThreadCount($userId)
    {
        $query = 'SELECT count(*) FROM
                  (
                      SELECT id FROM vc_forum_thread
                      INNER JOIN vc_feed_thread ON vc_forum_thread.id = vc_feed_thread.thread_id AND vc_feed_thread.user_id = ?
                      WHERE deleted_at IS NULL
                  UNION
                      SELECT id FROM vc_forum_thread
                      WHERE context_type = ' . \vc\config\EntityTypes::PROFILE . ' AND created_by = ? AND deleted_at IS NULL
                  ) AS temp';
        $statement = $this->getDb()->queryPrepared($query, array($userId, $userId));
        $statement->bind_result(
            $threadCount
        );
        $statement->fetch();
        $statement->close();
        return $threadCount;
    }
}
