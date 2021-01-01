<?php
namespace vc\model\db;

class PollDbModel extends AbstractDbModel
{
    public function loadPoll($locale, $currentProfile, $id)
    {
        $polls = $this->loadPolls($locale, $currentProfile, array($id));
        if (count($polls) === 0) {
            return null;
        } else {
            return $polls[0];
        }
    }

    public function loadPolls($locale, $currentProfile, $id_filter = array())
    {
        $polls = array();
        $query = sprintf(
            'SELECT poll_id, question_%s, end_time FROM vc_poll WHERE ' .
            'question_%s IS NOT NULL AND ' .
            // Poll has started
            'start_time <= \'%s\' AND ' .
            // Poll is still running
            'end_time >= \'%s\' AND ' .
            // User was already member when poll started
            'start_time > \'%s\'',
            $locale,
            $locale,
            date('Y-m-d'),
            date('Y-m-d'),
            date('Y-m-d', strtotime($currentProfile->firstEntry))
        );
        if (count($id_filter) > 0) {
            $query .= ' AND poll_id IN (' . implode(',', $id_filter) . ')';
        }
        $result = $this->getDb()->select($query);
        while ($row = $result->fetch_row()) {
            $poll = new \vc\object\Poll();
            $poll->id = $row[0];
            $poll->question = $row[1];
            $poll->end_time = strtotime($row[2]);

            $query2 = sprintf(
                'SELECT vc_poll_option.option_id, vc_poll_option.option_%s, ' .
                '(SELECT count(*) FROM vc_poll_selection WHERE ' .
                'vc_poll_selection.poll_id = vc_poll_option.poll_id AND ' .
                'vc_poll_selection.option_id = vc_poll_option.option_id) as selections ' .
                'FROM vc_poll_option WHERE vc_poll_option.poll_id=%d',
                $locale,
                intval($poll->id)
            );
            $result2 = $this->getDb()->select($query2);
            $poll->options = array();
            $max_votes = 0;
            $total_votes = 0;
            while ($row2 = $result2->fetch_row()) {
                $poll->options[] = array('ID' => $row2[0],
                                                   'OPTION' => $row2[1],
                                                   'COUNT' => $row2[2]);
                $max_votes = max($max_votes, $row2[2]);
                $total_votes += $row2[2];
            }
            $poll->max = $max_votes;
            $poll->total = $total_votes;
            $result2->free();

            $query2 = sprintf(
                'SELECT option_id FROM vc_poll_selection ' .
                'WHERE poll_id = %d AND profile_id = %d',
                intval($poll->id),
                $currentProfile->id
            );
            $result2 = $this->getDb()->select($query2);
            if ($result2->num_rows > 0) {
                $row2 = $result2->fetch_row();
                $poll->own_vote = $row2[0];
            } else {
                $poll->own_vote = 0;
            }
            $result2->free();

            $polls[] = $poll;
        }
        $result->free();
        return $polls;
    }

    public function addVote($pollId, $optionId, $profileId)
    {
        $query = 'INSERT INTO vc_poll_selection SET
                  poll_id = ?,
                  option_id = ?,
                  profile_id = ?;
                  DUPLICATE KEY UPDATE
                  option_id = ?';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                intval($pollId),
                intval($optionId),
                intval($profileId),
                intval($optionId),
            )
        );
        return $success;
    }
}
