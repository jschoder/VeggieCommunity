<?php
namespace vc\shell\cron\task\notify;

class SavedSearchesTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $savedSearches = array();

        $query = "SELECT
                  vc_search.id, vc_search.name, vc_search.url,
                  vc_profile.email, vc_setting.value
                  FROM vc_search
                  INNER JOIN vc_profile ON vc_profile.id = vc_search.profileid
                  INNER JOIN vc_setting ON vc_setting.profileid = vc_search.profileid AND vc_setting.field = 31
                  WHERE vc_search.message_interval>0 AND
                  vc_search.last_message <= DATE_SUB(NOW(), INTERVAL vc_search.message_interval DAY)";
        $result = $this->getDb()->select($query);
        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();

            $searchid = $row[0];
            $name = $row[1];
            $url = str_replace('&amp;', '&', $row[2]);
            $email = $row[3];
            $locale = $row[4];

            $mailbody = \vc\helper\CurlHelper::call(
                "https://www.veggiecommunity.org/" . $locale . "/user/result/mail?" . $url . "&searchid=" . $searchid
            );
            if ($mailbody != "") {
                $systemMessageModel = $this->getDbModel('SystemMessage');
                $this->getComponent('I14n')->loadLocale($locale);
                $subject = str_replace("%NAME%", "'" . $name . "'", gettext("savedsearch.mail.subject"));
                $mailbody = htmlspecialchars_decode($mailbody);
                $mailbody = html_entity_decode($mailbody, ENT_NOQUOTES, "UTF-8");

                $savedSearches[] = array('id' => $searchid,
                                         'name' => $name,
                                         'url' => $url);
                if (!$this->isTestMode()) {
                    $systemMessageModel->add(
                        $email,
                        $subject,
                        $mailbody
                    );
                }

                $query3 = "UPDATE LOW_PRIORITY vc_search SET last_message=NOW() WHERE id=" . intval($searchid);
                if (!$this->isTestMode()) {
                    // :TODO: JOE - deprecated
                    $this->getDb()->update($query3);
                }
            }

            $this->setDebugInfo('savedSearches', $savedSearches);
        }
    }
}
