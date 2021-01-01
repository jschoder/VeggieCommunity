<?php
namespace vc\model\db;

class FlagDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_flag';
    const OBJECT_CLASS = '\\vc\object\\Flag';

    public function getFlaggedIds($aggregateType, $aggregateId, $entityTypes)
    {
        $query = 'SELECT entity_type, entity_id, hash_id FROM vc_flag ' .
                 'WHERE aggregate_type = ? AND aggregate_id = ? AND processed_at IS NULL';
        $statement = $this->getDb()->queryPrepared($query, array(intval($aggregateType), intval($aggregateId)));

        $flags = array();
        $statement->bind_result(
            $entityType,
            $entityId,
            $hashId
        );
        while ($statement->fetch()) {
            if (!array_key_exists($entityType, $flags)) {
                $flags[$entityType] = array();
            }
            $flags[$entityType][$entityId] = $hashId;
        }
        $statement->close();

        return $flags;
    }

    public function getGroupFlagsForMods($profileId)
    {
        $query = 'SELECT * FROM (
            SELECT
            g.hash_id AS group_hash_id, g.name AS group_name,
            f.hash_id AS forum_hash_id, f.name AS forum_name,
            ft.hash_id as thread_id, "" as comment_id, ft.subject, ft.body,
            fl.hash_id, fl.entity_type, fl.comment, fl.flagged_at,
            flp.id AS flagger_id, flp.nickname AS flagger_nickname,
            flp.active AS flagger_active, flp.real_marker, flp.plus_marker
            FROM vc_group_role gr
            INNER JOIN vc_group_forum f ON f.group_id = gr.group_id AND f.deleted_at IS NULL
            INNER JOIN vc_group g ON g.id = gr.group_id
            INNER JOIN vc_flag fl ON
                fl.aggregate_type = ' . \vc\config\EntityTypes::GROUP_FORUM . ' AND
                fl.aggregate_id = f.id AND
                fl.entity_type = ' . \vc\config\EntityTypes::FORUM_THREAD . ' AND
                fl.processed_at IS NULL
            INNER JOIN vc_profile flp ON flp.id = fl.flagged_by
            INNER JOIN vc_forum_thread ft ON ft.id = fl.entity_id AND ft.deleted_at IS NULL
            WHERE gr.profile_id = ?

            UNION

            SELECT
            g.hash_id AS group_hash_id, g.name AS group_name,
            f.hash_id AS forum_hash_id, f.name AS forum_name,
            ft.hash_id AS thread_id, ftc.hash_id AS comment_id, "" AS subject, ftc.body,
            fl.hash_id, fl.entity_type, fl.comment, fl.flagged_at,
            flp.id AS flagger_id, flp.nickname AS flagger_nickname,
            flp.active AS flagger_active, flp.real_marker, flp.plus_marker
            FROM vc_group_role gr
            INNER JOIN vc_group_forum f ON f.group_id = gr.group_id AND f.deleted_at IS NULL
            INNER JOIN vc_group g ON g.id = gr.group_id
            INNER JOIN vc_flag fl ON
                fl.aggregate_type = ' . \vc\config\EntityTypes::GROUP_FORUM . ' AND
                fl.aggregate_id = f.id AND
                fl.entity_type = ' . \vc\config\EntityTypes::FORUM_COMMENT . ' AND
                fl.processed_at IS NULL
            INNER JOIN vc_profile flp ON flp.id = fl.flagged_by
            INNER JOIN vc_forum_thread_comment ftc ON ftc.id = fl.entity_id AND ftc.deleted_at IS NULL
            INNER JOIN vc_forum_thread ft ON ft.id = ftc.thread_id AND ft.deleted_at IS NULL
            WHERE gr.profile_id = ?
            ) AS temp_flags ORDER BY flagged_at';
        $statement = $this->getDb()->queryPrepared(
            $query,
            array(intval($profileId), intval($profileId))
        );

        $flags = array();
        $statement->bind_result(
            $groupHashId,
            $groupName,
            $forumHashId,
            $forumName,
            $threadId,
            $commentId,
            $subject,
            $body,
            $flagHashId,
            $entityType,
            $flagComment,
            $flaggedAt,
            $flaggerId,
            $flaggerNickname,
            $flaggerActive,
            $flaggerRealMarker,
            $flaggerPlusMarker
        );
        while ($statement->fetch()) {
            $flags[] = array(
                'group_hash_id' => $groupHashId,
                'group_name' => $groupName,
                'forum_hash_id' => $forumHashId,
                'forum_name' => $forumName,
                'thread_id' => $threadId,
                'comment_id' => $commentId,
                'subject' => $subject,
                'body' => $body,
                'entity_type' => $entityType,
                'flag_hash_id' => $flagHashId,
                'flag_comment' => $flagComment,
                'flagged_at' => strtotime($flaggedAt),
                'flagger_id' => $flaggerId,
                'flagger_nickname' => $flaggerNickname,
                'flagger_active' => $flaggerActive,
                'flagger_real_marker' => $flaggerRealMarker,
                'flagger_plus_marker' => $flaggerPlusMarker
            );
        }
        $statement->close();

        return $flags;
    }
}
