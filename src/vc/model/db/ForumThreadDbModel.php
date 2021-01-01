<?php
namespace vc\model\db;

class ForumThreadDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_forum_thread';
    const OBJECT_CLASS = '\\vc\object\\ForumThread';

    public function edit($hashId, $userId, $newSubject, $newBody)
    {
        return $this->update(
            array(
                'hash_id' => $hashId,
                'created_by' => $userId
            ),
            array(
                'subject' => $newSubject,
                'body' => $newBody,
                'updated_at' => date('Y-m-d H:i:s')
            )
        );
    }

    public function deleteThread($hashId, $userId)
    {
        $threadObject = $this->loadObject(array('hash_id' => $hashId));
        if ($threadObject === null) {
            return false;
        } else {
            // Probably not the right place to check this?
            if ($threadObject->createdBy != $userId) {
                if ($threadObject->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
                    $groupForumModel = $this->getDbModel('GroupForum');
                    $groupId = $groupForumModel->getField('group_id', 'id', $threadObject->contextId);
                    $groupRoleModel = $this->getDbModel('GroupRole');
                    $groupRole = $groupRoleModel->getRole($groupId, $userId);
                    if (empty($groupRole)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            $query = 'UPDATE vc_forum_thread ' .
                     'SET deleted_by = ?, deleted_at = ? ' .
                     'WHERE id = ?';
            $statement = $this->getDb()->prepare($query);
            $deleteDate = date('Y-m-d H:i:s');
            $paramsBinded = $statement->bind_param('isi', $userId, $deleteDate, $threadObject->id);
            if (!$paramsBinded) {
                \vc\lib\ErrorHandler::error(
                    'Error binding parameters for thread delete',
                    __FILE__,
                    __LINE__,
                    array(
                        'query' => $query,
                        'userId' => $userId,
                        'deleteDate' => $deleteDate,
                        'hashId' => $hashId
                    )
                );
            }
            $executed = $statement->execute();
            $statement->close();
            if (!$executed) {
                \vc\lib\ErrorHandler::error(
                    'Error while deleting thread: ' . $statement->errno . ' / ' . $statement->error,
                    __FILE__,
                    __LINE__,
                    array('hashId' => $hashId,
                          'userId' => $userId)
                );
                return false;
            }
            return true;
        }
    }

    public function getNews($locale, $limit = 1)
    {
        $moderators = array();
        $query = 'SELECT id FROM vc_profile WHERE admin % 8 > 3';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $userId
        );
        while ($statement->fetch()) {
            $moderators[] = $userId;
        }
        $statement->close();

        if ($locale == 'de') {
            $forumId = 18;
        } else {
            $forumId = 272;
        }
        $news = array();
        $query = 'SELECT hash_id, subject, body
                  FROM vc_forum_thread
                  WHERE context_type = ' .\vc\config\EntityTypes::GROUP_FORUM . ' AND
                        context_id = ' . $forumId . ' AND
                        created_by IN (' . implode(',', $moderators) . ') AND
                        deleted_at IS NULL
                  ORDER BY id DESC
                  LIMIT ' . intval($limit);
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result(
            $hashId,
            $subject,
            $body
        );
        while ($statement->fetch()) {
            if (mb_strlen($body) > 100) {
                $body = mb_substr($body, 0, 97) . '...';
            }

            $news[$hashId] = array($subject, $body);
        }
        $statement->close();

        return $news;
    }

    public function updateLastUpdateTimestamp($threadId)
    {
        $query = 'UPDATE vc_forum_thread
                  LEFT JOIN (
                    SELECT thread_id, max(created_at) as last_comment_created, max(updated_at) AS last_comment_updated
                    FROM vc_forum_thread_comment WHERE deleted_at IS NULL GROUP BY thread_id
                  ) AS thread_comments ON thread_comments.thread_id = vc_forum_thread.id
                  SET
                    vc_forum_thread.content_updated_at = GREATEST(
                        vc_forum_thread.created_at,
                        IFNULL(vc_forum_thread.updated_at, \'1970-01-01 00:00:00\'),
                        IFNULL(thread_comments.last_comment_created, \'1970-01-01 00:00:00\'),
                        IFNULL(thread_comments.last_comment_updated, \'1970-01-01 00:00:00\')
                    )
                  WHERE vc_forum_thread.id = ?';
        return $this->getDb()->executePrepared($query, array($threadId));
    }

    public function getLastUpdateTimestamp($contextType, $contextId)
    {
        if ($contextType == \vc\config\EntityTypes::PROFILE) {
            $query = 'SELECT max(content_updated_at) FROM
                        (
                            SELECT content_updated_at FROM vc_forum_thread
                            WHERE context_type = ? AND created_by = ? AND deleted_at IS NULL
                        UNION
                            SELECT content_updated_at FROM vc_forum_thread
                            INNER JOIN vc_feed_thread ON
                            vc_forum_thread.id = vc_feed_thread.thread_id AND
                            vc_feed_thread.user_id = ?
                            WHERE deleted_at IS NULL
                        ) AS temp_union';
            $queryParams = array(
                intval($contextType),
                intval($contextId),
                intval($contextId)
            );
        } else {
            $query = 'SELECT max(content_updated_at)
                      FROM vc_forum_thread
                      WHERE
                        context_type = ? AND
                        context_id = ? AND
                        deleted_at IS NULL';
            $queryParams = array(
                intval($contextType),
                intval($contextId)
            );
        }
        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result(
            $contentUpdated
        );
        if ($statement->fetch()) {
            $timestamp = strtotime($contentUpdated);
        } else {
            $timestamp = 0;
        }
        $statement->close();
        return $timestamp;
    }

    public function getFriendThread($userId, $friendId)
    {
        return $this->loadObject(
            array(
                'deleted_at IS NULL',
                'context_type' => \vc\config\EntityTypes::PROFILE,
                'thread_type' => \vc\object\ForumThread::TYPE_ACTVITY_FRIEND_ADDED
            ),
            array(
                'INNER JOIN vc_friend ON
                 vc_friend.feed_thread_id = vc_forum_thread.id AND
                 (
                     (vc_friend.friend1id = ? AND vc_friend.friend2id = ?) OR
                     (vc_friend.friend1id = ? AND vc_friend.friend2id = ?)
                 )' => array(
                     $userId,
                     $friendId,
                    $friendId,
                    $userId
                )
            )
        );
    }

    public function getPictureThread($userId, $pictureId)
    {
        return $this->loadObject(
            array(
                'deleted_at IS NULL',
                'context_type' => \vc\config\EntityTypes::PROFILE,
                'context_id' => $userId,
                'thread_type' => \vc\object\ForumThread::TYPE_ACTVITY_PICTURE_ADDED
            ),
            array(
                'INNER JOIN vc_picture ON
                 vc_picture.feed_thread_id = vc_forum_thread.id AND vc_picture.id = ?' => array($pictureId)
            )
        );
    }
}
