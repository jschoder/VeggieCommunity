<?php
namespace vc\shell\cron\task\aggregate;

class GroupActivityTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $groupModel = $this->getDbModel('Group');
        $groupIds = $groupModel->getFieldList(
            'id',
            array(
                'deleted_at IS NULL',
                'confirmed_at IS NOT NULL'
            )
        );

        $query = 'SELECT vc_group.id, count(vc_forum_thread.id) AS thread_count
                  FROM vc_group
                  INNER JOIN vc_group_forum
                    ON vc_group.id = vc_group_forum.group_id
                  INNER JOIN vc_forum_thread
                    ON vc_forum_thread.context_type = ' . \vc\config\EntityTypes::GROUP_FORUM . '
                    AND vc_forum_thread.context_id = vc_group_forum.id
                  WHERE vc_group.deleted_at IS NULL
                    AND vc_forum_thread.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY vc_group.id';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($groupId, $threadCount);
        $groupThreadCount = array();
        $groupThreadSum = 0;
        while ($statement->fetch()) {
            $groupThreadCount[$groupId] = $threadCount;
            $groupThreadSum += $threadCount;
        }
        $statement->close();

        $query = 'SELECT vc_group.id, count(vc_forum_thread_comment.id) AS comment_count
                  FROM vc_group
                  INNER JOIN vc_group_forum
                    ON vc_group.id = vc_group_forum.group_id
                  INNER JOIN vc_forum_thread
                    ON vc_forum_thread.context_type = ' . \vc\config\EntityTypes::GROUP_FORUM . '
                    AND vc_forum_thread.context_id = vc_group_forum.id
                  INNER JOIN vc_forum_thread_comment
                    ON vc_forum_thread_comment.thread_id = vc_forum_thread.id
                  WHERE vc_group.deleted_at IS NULL
                    AND vc_forum_thread_comment.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY vc_group.id';
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($groupId, $threadCommentCount);
        $groupCommentCount = array();
        $groupCommentSum = 0;
        while ($statement->fetch()) {
            $groupCommentCount[$groupId] = $threadCommentCount;
            $groupCommentSum += $threadCommentCount;
        }
        $statement->close();

        // Setting the range of activities
        $avgGroupEntry = ($groupThreadSum + $groupCommentSum) / count($groupIds);
        $avgGroupEntryFrom = $avgGroupEntry * 0.9;
        $avgGroupEntryTo = $avgGroupEntry * 1.1;
        $doubleAvgActivity = $avgGroupEntry * 2;

        if ($this->isTestMode()) {
            echo "\n";
            echo 'Groups: ' . count($groupIds) . "\n";
            echo 'Threads: ' . $groupThreadSum . "\n";
            echo 'ThreadComments: ' . $groupCommentSum . "\n";
            echo 'AvgEntry: ' . $avgGroupEntry . '(' . $avgGroupEntryFrom . '/' . $avgGroupEntryTo .  ")\n\n";
        }

        $groupActivityStats = array(
            \vc\object\Group::ACTIVITY_VERY_LOW => 0,
            \vc\object\Group::ACTIVITY_LOW => 0,
            \vc\object\Group::ACTIVITY_AVERAGE => 0,
            \vc\object\Group::ACTIVITY_HIGH => 0,
            \vc\object\Group::ACTIVITY_VERY_HIGH => 0
        );
        foreach ($groupIds as $groupId) {
            $groupEntries = (empty($groupThreadCount[$groupId]) ? 0 : $groupThreadCount[$groupId]) +
                            (empty($groupCommentCount[$groupId]) ? 0 : $groupCommentCount[$groupId]);
            if ($groupEntries === 0) {
                $activity = \vc\object\Group::ACTIVITY_VERY_LOW;
            } else if ($groupEntries < $avgGroupEntryFrom) {
                $activity = \vc\object\Group::ACTIVITY_LOW;
            } else if ($groupEntries < $avgGroupEntryTo) {
                $activity = \vc\object\Group::ACTIVITY_AVERAGE;
            } else if ($groupEntries < $doubleAvgActivity) {
                $activity = \vc\object\Group::ACTIVITY_HIGH;
            } else {
                $activity = \vc\object\Group::ACTIVITY_VERY_HIGH;
            }
            $groupActivityStats[$activity] = $groupActivityStats[$activity] + 1;

            if ($this->isTestMode()) {
                echo ' Group: ' . $groupId . "\t" .
                     'Threads: ' . (empty($groupThreadCount[$groupId]) ? 0 : $groupThreadCount[$groupId]) . "\t" .
                     'Comments: ' . (empty($groupCommentCount[$groupId]) ? 0 : $groupCommentCount[$groupId]) . "\t" .
                     'Activity: ' . $activity . "\n";
            } else {
                $groupModel->update(
                    array(
                        'id' => $groupId
                    ),
                    array(
                        'activity' => $activity
                    )
                );
            }
        }

        // :TODO: JOE - group into fewer queries update set orange where id IN ()

        if ($this->isTestMode()) {
            echo "\n\nNew Activity Charts: \n";
            foreach ($groupActivityStats as $activity => $count) {
                echo $activity . ' => ' . $count . "\n";
            }
            echo "\n\n";
        }
        $this->setDebugInfo('groupActivityStats', $groupActivityStats);
    }
}
