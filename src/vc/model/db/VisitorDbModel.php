<?php
namespace vc\model\db;

class VisitorDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_last_visitor';

    public function getLastVisitors($locale, $profileId, &$pictureProfileIDs, &$pictureProfiles, $limit, $checkForMore)
    {
        $sqlLimit = $limit;
        if ($checkForMore) {
            $sqlLimit++;
        }
        $query = "SELECT visitor_id, last_visit FROM vc_last_visitor WHERE profile_id=" . $profileId
               . " ORDER BY last_visit DESC LIMIT " . intval($sqlLimit);
        $result = $this->getDb()->select($query);
        $lastVisitorIDs = array();
        $lastVisits = array();
        while ($row = $result->fetch_row()) {
            $lastVisitorIDs[] = $row[0];
            $lastVisits[$row[0]] = strtotime($row[1]);
        }
        $result->free();

        $profileModel = $this->getDbModel('Profile');
        $visitors = $profileModel->getSmallProfiles($locale, $lastVisitorIDs);
        $visitorsIncludingVisit = array();
        foreach ($visitors as $visitor) {
            $visitor->lastVisit = $lastVisits[$visitor->id];
            $visitorsIncludingVisit[] = $visitor;
        }
        $pictureProfileIDs = array_merge($pictureProfileIDs, $lastVisitorIDs);
        $pictureProfiles = array_merge($pictureProfiles, $visitorsIncludingVisit);
        if (count($visitorsIncludingVisit) > $limit) {
            $visitorsIncludingVisit[count($visitorsIncludingVisit) - 1] = 'show_more';
        }
        return $visitorsIncludingVisit;
    }
}
