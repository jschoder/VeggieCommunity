<?php
namespace vc\shell\cron\task\notify;

class NotUnlockedProfilesTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $mailComponent = $this->getComponent('Mail');
        $unlockedProfiles = array();

        $query = 'SELECT profile_id, vc_profile.email, token, vc_setting.value FROM vc_activation_token ' .
                 'INNER JOIN vc_profile ON vc_profile.id = vc_activation_token.profile_id ' .
                 'INNER JOIN vc_setting ON ' .
                 'vc_setting.profileid = vc_activation_token.profile_id AND vc_setting.field = 31 ' .
                 'WHERE used_at IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) AND ' .
                 'created_at > DATE_SUB(NOW(), INTERVAL ' . \vc\config\Globals::TOKEN_EXPIRE_HOURS . ' HOUR)';
        $result = $this->getDb()->select($query);

        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $profileId = $row[0];
            $email = $row[1];
            $token = $row[2];
            $locale = $row[3];

            $unlockedProfiles[] = array('profileId' => $profileId,
                                        'email' => $email,
                                        'token' => $token,
                                        'locale' => $locale);

            $this->getComponent('I14n')->loadLocale($locale);
            if (!$this->isTestMode()) {
                $mailComponent->sendMailToUser(
                    $locale,
                    intval($profileId),
                    'edit.locked.subject',
                    'account-activate',
                    array(
                        'LINK' => 'account/activate/' . urlencode($profileId) . '/' . urlencode($token) . '/'
                    )
                );
            }
        }
        $this->setDebugInfo('unlockedProfiles', $unlockedProfiles);
    }
}
