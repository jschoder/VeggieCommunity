<?php
namespace vc\shell\cron\task\notify;

class OldMemberRequestsTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $query = 'SELECT DISTINCT
                  vc_group.hash_id, vc_group.name,
                  vc_profile.nickname, vc_profile.email,
                  vc_setting.value
                  FROM vc_group_member
                  INNER JOIN vc_profile member_profile
                  ON member_profile.id = vc_group_member.profile_id AND member_profile.active > 0
                  INNER JOIN vc_group
                  ON vc_group_member.group_id = vc_group.id
                  INNER JOIN vc_group_role
                  ON vc_group_role.group_id = vc_group_member.group_id AND vc_group_role.role = 2
                  INNER JOIN vc_profile
                  ON vc_profile.id = vc_group_role.profile_id
                  INNER JOIN vc_setting
                  ON vc_setting.profileid = vc_profile.id
                  AND vc_setting.field = ' . \vc\object\Settings::USER_LANGUAGE . '
                  WHERE vc_group_member.created_at < DATE_SUB(NOW(),INTERVAL 7 DAY)
                  AND vc_group_member.confirmed_at IS NULL';
        $statement = $this->getDb()->queryPrepared($query);
        $notifications = array();
        $statement->bind_result(
            $groupHashId,
            $groupName,
            $adminNickname,
            $adminEmail,
            $adminLocale
        );
        while ($statement->fetch()) {
            $notifications[] = array(
                'group' => array(
                    'hash_id' => $groupHashId,
                    'name' => $groupName
                ),
                'admin' => array(
                    'nickname' => $adminNickname,
                    'email' => $adminEmail,
                    'locale' => $adminLocale
                )
            );
        }
        $statement->close();

        $i14nComponent = $this->getComponent('I14n');
        $mailComponent = $this->getComponent('Mail');
        foreach ($notifications as $notification) {
            if ($this->isTestMode()) {
                echo 'Sending mail to ' .
                      $notification['admin']['nickname'] .
                      ' <' . $notification['admin']['email'] . '>' .
                      ' about group ' .
                      $notification['group']['name'] .
                      ' (' . $notification['group']['hash_id'] . ')' .
                      ' in ' . $notification['admin']['locale'] . "\n";
            } else {
                $i14nComponent->loadLocale($notification['admin']['locale']);
                $mailComponent->sendMail(
                    $notification['admin']['nickname'],
                    $notification['admin']['email'],
                    'group.join.mailNotification.waiting',
                    $notification['admin']['locale'],
                    'group-member-request-waiting',
                    array(
                        'GROUPNAME' => $notification['group']['name'],
                        'LINK' => 'groups/info/' . $notification['group']['hash_id'] . '/'
                    )
                );
            }
        }
    }
}
