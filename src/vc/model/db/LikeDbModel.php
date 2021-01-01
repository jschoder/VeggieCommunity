<?php
namespace vc\model\db;

class LikeDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_like';
    const OBJECT_CLASS = '\\vc\object\\Like';

    public function set($entityType, $entityId, $profileId, $upDown)
    {
        $query = 'INSERT INTO vc_like SET ' .
                 'entity_type = ?, entity_id = ?, profile_id = ?, up_down = ?, created_at = NOW() ' .
                 'ON DUPLICATE KEY UPDATE up_down = ?, created_at = NOW()';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('iiiii', $entityType, $entityId, $profileId, $upDown, $upDown);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while setting like: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('entityType' => $entityType,
                      'entityId' => $entityId,
                      'profileId' => $profileId,
                      'upDown' => $upDown)
            );
            return false;
        }

        return true;
    }

    public function get($entityType, $entityId)
    {
        $query = 'SELECT up_down, count(*) FROM vc_like ' .
                 'WHERE entity_type = ? AND entity_id = ? ' .
                 'GROUP BY up_down';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($entityType), intval($entityId))
        );

        $return = array('likes' => 0, 'dislikes' => 0);
        $statement->bind_result(
            $upDown,
            $count
        );
        while ($statement->fetch()) {
            if ($upDown == -1) {
                $return['dislikes'] = $count;
            } else {
                $return['likes'] = $count;
            }
        }
        $statement->close();

        return $return;
    }

    public function getForumLikes($forumThreadIds, $forumThreadCommentIds)
    {
        $return = array(
            \vc\config\EntityTypes::FORUM_THREAD => array(),
            \vc\config\EntityTypes::FORUM_COMMENT => array(),
        );

        if (empty($forumThreadIds) && empty($forumThreadCommentIds)) {
            return $return;
        }

        // Prefill empty results
        foreach ($forumThreadIds as $entityId) {
            $return[\vc\config\EntityTypes::FORUM_THREAD][$entityId] = array('likes' => 0, 'dislikes' => 0);
        }
        foreach ($forumThreadCommentIds as $entityId) {
            $return[\vc\config\EntityTypes::FORUM_COMMENT][$entityId] = array('likes' => 0, 'dislikes' => 0);
        }

        // Fill actual data from database
        $where = array();
        $whereParams = array();
        if (!empty($forumThreadIds)) {
            $where[] = 'entity_type = ' . \vc\config\EntityTypes::FORUM_THREAD . ' AND ' .
                       'entity_id IN (' . $this->fillQuery(count($forumThreadIds)) . ')';
            $whereParams = $forumThreadIds;
        }
        if (!empty($forumThreadCommentIds)) {
            $where[] = 'entity_type = ' . \vc\config\EntityTypes::FORUM_COMMENT . ' AND ' .
                       'entity_id IN (' . $this->fillQuery(count($forumThreadCommentIds)) . ')';
            $whereParams = array_merge($whereParams, $forumThreadCommentIds);
        }

        $query = 'SELECT entity_type, entity_id, up_down, count(*) FROM vc_like ' .
                 'WHERE (' . implode(') OR (', $where) . ') ' .
                 'GROUP BY entity_type, entity_id, up_down';
        $statement = $this->getDb()->queryPrepared($query, $whereParams);
        $statement->bind_result(
            $entityType,
            $entityId,
            $upDown,
            $count
        );
        while ($statement->fetch()) {
            if ($upDown == -1) {
                $return[$entityType][$entityId]['dislikes'] = $count;
            } else {
                $return[$entityType][$entityId]['likes'] = $count;
            }
        }
        $statement->close();

        return $return;
    }

    public function getProfiles($entityType, $entityHashId, $upDown)
    {
        if ($entityType == \vc\config\EntityTypes::FORUM_THREAD) {
            $joinTable = 'vc_forum_thread';
        } elseif ($entityType == \vc\config\EntityTypes::FORUM_COMMENT) {
            $joinTable = 'vc_forum_thread_comment';
        } else {
            return array();
        }

        $query = 'SELECT vc_like.profile_id, vc_profile.nickname FROM vc_like
                  INNER JOIN ' . $joinTable. ' hash_table ON vc_like.entity_id = hash_table.id
                  INNER JOIN vc_profile ON vc_profile.id = vc_like.profile_id
                  WHERE vc_like.entity_type = ? AND hash_table.hash_id = ? and vc_like.up_down = ?
                  ORDER BY vc_like.created_at DESC';
        $statement = $this->getDb()->queryPrepared($query, array($entityType, $entityHashId, $upDown));
        $statement->bind_result(
            $profileId,
            $profileNickname
        );
        $profiles = array();
        while ($statement->fetch()) {
            $profiles[$profileId] = $profileNickname;
        }
        $statement->close();
        return $profiles;
    }
}
