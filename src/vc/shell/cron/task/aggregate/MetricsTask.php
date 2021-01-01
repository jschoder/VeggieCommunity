<?php
namespace vc\shell\cron\task\aggregate;

class MetricsTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
             SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_LOGINS_TOTAL . ',
                count(*)
            FROM vc_user_ip_log
            WHERE access >= TIMESTAMP(subdate(current_date, 1)) AND
                  access < TIMESTAMP(current_date)'
        );
        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
             SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_LOGINS_DISTINCT . ',
                count(DISTINCT profile_id)
            FROM vc_user_ip_log
            WHERE access >= TIMESTAMP(subdate(current_date, 1)) AND
                  access < TIMESTAMP(current_date)'
        );

        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
             SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_PM_SENT . ',
                count(*)
            FROM vc_message
            WHERE created >= TIMESTAMP(subdate(current_date, 1)) AND
                  created < TIMESTAMP(current_date)'
        );

        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
             SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_PM_NEW_CONTACTS . ',
                count(*)
            FROM vc_pm_thread
            WHERE
                created_at BETWEEN TIMESTAMP(subdate(current_date, 1)) AND TIMESTAMP(current_date)'
        );

        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
             SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_THREADS . ',
                count(*)
            FROM vc_forum_thread
            WHERE created_at >= TIMESTAMP(subdate(current_date, 1)) AND
                  created_at < TIMESTAMP(current_date)  AND
                  thread_type IN (' . implode(',', \vc\object\ForumThread::$manualThreadTypes) . ')'
        );
        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
             SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_THREAD_COMMENTS . ',
                count(*)
            FROM vc_forum_thread_comment
            WHERE created_at >= TIMESTAMP(subdate(current_date, 1)) AND
                  created_at < TIMESTAMP(current_date)'
        );

        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
            SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_EVENTS_UPCOMING . ',
                count(*)
            FROM vc_event
            WHERE start_date >= TIMESTAMP(subdate(current_date, 1))'
        );
        $this->getDb()->executePrepared(
            'SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_EVENTS_TODAY . ',
                count(*)
            FROM vc_event
            WHERE
                TIMESTAMP(subdate(current_date, 1)) BETWEEN start_date AND end_date OR
                start_date BETWEEN TIMESTAMP(subdate(current_date, 1)) AND TIMESTAMP(current_date) OR
                end_date BETWEEN TIMESTAMP(subdate(current_date, 1)) AND TIMESTAMP(current_date)'
        );
        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
            SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_EVENT_PARTICIPATION_LIKELY . ',
                count(*)
            FROM vc_event_participant
            INNER JOIN vc_event ON
                vc_event_participant.event_id = vc_event.id AND
                (
                    TIMESTAMP(subdate(current_date, 1)) BETWEEN start_date AND end_date OR
                    start_date BETWEEN TIMESTAMP(subdate(current_date, 1)) AND TIMESTAMP(current_date) OR
                    end_date BETWEEN TIMESTAMP(subdate(current_date, 1)) AND TIMESTAMP(current_date)
                )
            WHERE degree IN (' .
                \vc\object\EventParticipant::STATUS_PARTICIPATING_SURE . ',' .
                \vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY .
            ')'
        );

        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
            SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_EVENT_PARTICIPATION_UNLIKELY . ',
                count(*)
            FROM vc_event_participant
            INNER JOIN vc_event ON
                vc_event_participant.event_id = vc_event.id AND
                (
                    TIMESTAMP(subdate(current_date, 1)) BETWEEN start_date AND end_date OR
                    start_date BETWEEN TIMESTAMP(subdate(current_date, 1)) AND TIMESTAMP(current_date) OR
                    end_date BETWEEN TIMESTAMP(subdate(current_date, 1)) AND TIMESTAMP(current_date)
                )
            WHERE degree IN (' .
                \vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY . ',' .
                \vc\object\EventParticipant::STATUS_ENDORSING . ',' .
                \vc\object\EventParticipant::STATUS_BOOKMARK .
            ')'
        );

        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
            SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_CHAT_MESSAGES . ',
                count(*)
            FROM ajax_chat_messages
            WHERE dateTime >= TIMESTAMP(subdate(current_date, 1)) AND
                  dateTime < TIMESTAMP(current_date)'
        );


        $this->getDb()->executePrepared(
            'INSERT INTO vc_metric (day, type, value)
            SELECT
                subdate(current_date, 1),
                ' . \vc\object\Metric::TYPE_ACTIVE_PLUS . ',
                count(*)
            FROM vc_plus
            WHERE plus_type >= ' . \vc\object\Plus::PLUS_TYPE_STANDARD . ' AND
                  payment_type != ' . \vc\object\Plus::PAYMENT_TYPE_GIFT . ' AND
                  (
                    TIMESTAMP(subdate(current_date, 1)) BETWEEN start_date AND end_date OR
                    TIMESTAMP(current_date, 1) BETWEEN start_date AND end_date
                  )'
        );
    }
}