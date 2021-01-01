<?php
namespace vc\shell\cron;

class DailyCronShell extends AbstractCronShell
{
    protected function getLockName()
    {
        return 'cron.daily';
    }

    protected function getTasks()
    {
        $tasks = array();

        // Aggregate all relevant data before cleaning up
        $tasks[] = new task\aggregate\LastVisitorsTask();
        $tasks[] = new task\aggregate\MetricsTask();
        $tasks[] = new task\aggregate\ProfileCounterTask();
        $tasks[] = new task\aggregate\ZipReportsTask();

        // Cleanup and remove data that is no longer required
        $tasks[] = new task\cleanup\ArchiveChatMessagesTask();
        $tasks[] = new task\cleanup\CacheTask();
        $tasks[] = new task\cleanup\ChatArchiveTask();
        $tasks[] = new task\cleanup\DeletedMessagesTask();
        $tasks[] = new task\cleanup\DeletedProfilesTask();
        $tasks[] = new task\cleanup\LogsTask();
        $tasks[] = new task\cleanup\OnlineStatusTask();
        $tasks[] = new task\cleanup\PicsTask();
        $tasks[] = new task\cleanup\TimedOutProfilesTask();
        $tasks[] = new task\cleanup\TokenTask();

        // Send out notifications to different users
        $tasks[] = new task\notify\DormantProfilesTask();
        $tasks[] = new task\notify\NotUnlockedProfilesTask();
        $tasks[] = new task\notify\OldMemberRequestsTask();
        $tasks[] = new task\notify\SavedSearchesTask();
        $tasks[] = new task\notify\UnreadMessagesTask();

        // Update data as required
        $tasks[] = new task\workers\daily\CalculateAgeTask();
        $tasks[] = new task\workers\daily\GeoIpTask();
        $tasks[] = new task\workers\daily\OrphanGroupsTask();

        // Aggregate all relevant data after cleaning up
        $tasks[] = new task\aggregate\StatisticsChartTask();

        // Optimize all tables after being done
        $tasks[] = new task\workers\daily\DbOptimizeTask();

        return $tasks;
    }
}
