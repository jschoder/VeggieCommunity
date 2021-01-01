<?php
namespace vc\model\db;

class GroupForumDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_group_forum';
    const OBJECT_CLASS = '\\vc\object\\GroupForum';

    public function getCurrentForum($forums, $currentForumHashId)
    {
        $mainForum = null;
        foreach ($forums as $forum) {
            // Fall back to the main forum if the selected forum can't be found (e.g. has been deleted)
            if ($forum->isMain) {
                $mainForum = $forum;
            }

            if ($forum->hashId == $currentForumHashId) {
                return $forum;
            }
        }
        return $mainForum;
    }

    public function setAutoMainForum($groupId)
    {
        $query = 'UPDATE vc_group_forum SET is_main = 1 '.
                 'WHERE group_id = ? AND deleted_at IS NULL ORDER BY weight LIMIT 1';
        $this->getDb()->queryPrepared($query, array(intval($groupId)));
    }

    public function getLastUpdates($profileId)
    {
        $query = 'SELECT
                  temp.*,
                  ft.created_at, ftp.id, ftp.nickname, ftp.active,
                  ftc.created_at, ftcp.id, ftcp.nickname, ftcp.active
                  FROM (
                      SELECT
                          g.hash_id as g_hash_id, g.image, g.name as g_name,
                          f.hash_id as f_hash_id, f.name as_f_name, f.is_main,
                          max(ft.id) AS last_thread,
                          max(ftc.id) AS last_comment
                      FROM vc_group_member gm
                      INNER JOIN vc_group g ON gm.group_id = g.id AND g.deleted_at IS NULL
                      INNER JOIN vc_group_forum f ON f.group_id = g.id AND f.deleted_at IS NULL
                      LEFT JOIN vc_forum_thread ft ON ft.context_type = ' . \vc\config\EntityTypes::GROUP_FORUM . '
                              AND ft.context_id = f.id AND ft.deleted_at IS NULL
                      LEFT JOIN vc_forum_thread_comment ftc ON ftc.thread_id = ft.id AND ftc.deleted_at IS NULL
                      WHERE gm.profile_id = ?
                      GROUP BY f.id
                  ) as temp
                  LEFT JOIN vc_forum_thread ft ON ft.id = last_thread
                  LEFT JOIN vc_profile ftp ON ftp.id = ft.created_by
                  LEFT JOIN vc_forum_thread_comment ftc ON ftc.id = last_comment
                  LEFT JOIN vc_profile ftcp ON ftcp.id = ftc.created_by';
        $statement = $this->getDb()->queryPrepared($query, array(intval($profileId)));

        $groups = array();
        $statement->bind_result(
            $groupHashId,
            $groupImage,
            $groupName,
            $forumHashId,
            $forumName,
            $forumIsMain,
            $lastThreadId,
            $lastCommentId,
            $lastThreadCreated,
            $lastThreadAuthorId,
            $lastThreadAuthorNickname,
            $lastThreadAuthorActive,
            $lastCommentCreated,
            $lastCommentAuthorId,
            $lastCommentAuthorNickname,
            $lastCommentAuthorActive
        );
        $lastUpdates = array();
        while ($statement->fetch()) {
            if ($lastThreadCreated !== null) {
                $lastThreadCreated = strtotime($lastThreadCreated);
            }
            if ($lastCommentCreated !== null) {
                $lastCommentCreated = strtotime($lastCommentCreated);
            }
            if (!array_key_exists($groupHashId, $lastUpdates)) {
                $lastUpdates[$groupHashId] = array(
                    'image' => $groupImage,
                    'name' => $groupName,
                    'lastEntry' => max($lastThreadCreated, $lastCommentCreated),
                    'forums' => array()
                );
            } else {
                $lastUpdates[$groupHashId]['lastEntry'] = max(
                    $lastUpdates[$groupHashId]['lastEntry'],
                    $lastThreadCreated,
                    $lastCommentCreated
                );
            }
            $lastUpdates[$groupHashId]['forums'][$forumHashId] = array(
                'name' => $forumName,
                'isMain' => $forumIsMain,
                'lastThread' => $lastThreadCreated,
                'lastThreadAuthorId' => $lastThreadAuthorId,
                'lastThreadAuthorNickname' => $lastThreadAuthorNickname,
                'lastThreadAuthorActive' => $lastThreadAuthorActive,
                'lastComment' => $lastCommentCreated,
                'lastCommentAuthorId' => $lastCommentAuthorId,
                'lastCommentAuthorNickname' => $lastCommentAuthorNickname,
                'lastCommentAuthorActive' => $lastCommentAuthorActive,
                'lastEntry' => max($lastThreadCreated, $lastCommentCreated)
            );
        }
        $statement->close();

        // Sorting the groups by last thread/comments
        uasort($lastUpdates, function ($group1, $group2) {
            return $group2['lastEntry'] - $group1['lastEntry'];
        });
        foreach ($lastUpdates as &$group) {
            uasort($group['forums'], function ($forum1, $forum2) {
                return $forum2['lastEntry'] - $forum1['lastEntry'];
            });
        }
        return $lastUpdates;
    }

    public function createDefaultForum($groupId, $currentUser)
    {
        $forumObject = new \vc\object\GroupForum();
        $forumObject->groupId = $groupId;
        $forumObject->name = 'Forum'; // Same in both languages
        $forumObject->isMain = 1;
        $forumObject->weight = 0;
        $savedForum = $this->insertObject($currentUser, $forumObject);
        return $savedForum;
    }

    /**
     * Returns the entire list of last updates for all groupForums
     */
    public function getLastUpdatesAllForums()
    {
        $query = 'SELECT ft.context_id, max(ft.created_at), max(ftc.created_at)
                  FROM vc_forum_thread ft
                  LEFT JOIN vc_forum_thread_comment ftc ON ftc.thread_id = ft.id
                  WHERE ft.context_type = ' . \vc\config\EntityTypes::GROUP_FORUM . '
                  GROUP BY ft.context_id';
        $statement = $this->getDb()->queryPrepared($query);

        $statement->bind_result(
            $forumId,
            $lastThreadCreated,
            $lastCommentCreated
        );
        $lastUpdates = array();
        while ($statement->fetch()) {
            if ($lastCommentCreated === null) {
                $maxTimestamp = strtotime($lastThreadCreated);
            } else {
                $maxTimestamp = max(strtotime($lastThreadCreated), strtotime($lastCommentCreated));
            }
            $lastUpdates[$forumId] = $maxTimestamp;
        }
        $statement->close();

        return $lastUpdates;
    }

    public function getDefaultForum($groupId)
    {
        $query = 'SELECT id FROM vc_group_forum WHERE group_id = ? AND is_main = 1 AND deleted_at IS NULL LIMIT 1';
        $statement = $this->getDb()->queryPrepared($query, array($groupId));

        $forumId = null;
        $statement->bind_result($forumId);
        $statement->fetch();
        $statement->close();

        return $forumId;
    }
}
