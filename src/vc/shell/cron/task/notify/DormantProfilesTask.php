<?php
namespace vc\shell\cron\task\notify;

class DormantProfilesTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $oldProfiles = array();

        $now = date('Y-m-d H:i:s');
        $query = 'SELECT vc_profile.id, vc_profile.nickname, vc_profile.email, vc_profile.last_update, ' .
                 'vc_profile.last_login, vc_setting.value ' .
                 'FROM vc_profile ' .
                 'INNER JOIN vc_setting ON vc_setting.profileid = vc_profile.id AND vc_setting.field = 31 ' .
                 'WHERE active=1 AND ' .
                 'last_update < DATE_SUB(NOW(), INTERVAL 2 YEAR) AND ' .
                 'last_login < DATE_SUB(NOW(), INTERVAL 2 YEAR)';
        $result = $this->getDb()->select($query);
        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();

            $profileId = $row[0];
            $nickname = $row[1];
            $email = $row[2];
            $lastUpdate = $row[3];
            $lastLogin = $row[4];
            $locale = $row[5];

            $oldProfiles[] = array('profileId' => $profileId,
                                   'nickname' => $nickname,
                                   'email' => $email,
                                   'lastUpdate' => $lastUpdate,
                                   'lastLogin' => $lastLogin,
                                   'locale' => $locale);

            $this->getComponent('I14n')->loadLocale($locale);

            $systemMessageModel = $this->getDbModel('SystemMessage');
            $subject = gettext('unused.subject');
            $body = gettext('unused.body');
            if (!$this->isTestMode()) {
                $systemMessageModel->add(
                    $email,
                    $subject,
                    $body
                );
            }

            $this->getDb()->executePrepared(
                'UPDATE vc_profile set active = 2, reminder_date = ? WHERE id = ?',
                array(
                    $now,
                    $profileId
                )
            );
        }
        $this->setDebugInfo('oldProfiles', $oldProfiles);
    }
}
