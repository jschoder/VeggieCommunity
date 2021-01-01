<?php
namespace vc\shell\cron\task\workers\daily;

class DbOptimizeTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $tables = array();
        $tables[] = 'ajax_chat_bans';
        $tables[] = 'ajax_chat_channels';
        $tables[] = 'ajax_chat_invitations';
        $tables[] = 'ajax_chat_messages';
        $tables[] = 'ajax_chat_online';
        $tables[] = 'vc_activation_token';
        $tables[] = 'vc_activity';
        $tables[] = 'vc_banned_email';
        $tables[] = 'vc_banned_picture';
        $tables[] = 'vc_blocked';
        $tables[] = 'vc_change_pw_token';
        $tables[] = 'vc_country';
        $tables[] = 'vc_cron_log';
        $tables[] = 'vc_event';
        $tables[] = 'vc_event_participant';
        $tables[] = 'vc_faq';
        $tables[] = 'vc_favorite';
        $tables[] = 'vc_flag';
        $tables[] = 'vc_forum_thread';
        $tables[] = 'vc_forum_thread_comment';
        $tables[] = 'vc_friend';
        $tables[] = 'vc_group';
        $tables[] = 'vc_group_activity';
        $tables[] = 'vc_group_ban';
        $tables[] = 'vc_group_forum';
        $tables[] = 'vc_group_invitation';
        $tables[] = 'vc_group_member';
        $tables[] = 'vc_group_notification';
        $tables[] = 'vc_group_role';
        $tables[] = 'vc_group_setting';
        $tables[] = 'vc_hobby';
        $tables[] = 'vc_hobby_group';
        $tables[] = 'vc_keyword';
        $tables[] = 'vc_last_visitor';
        $tables[] = 'vc_like';
        $tables[] = 'vc_message';
        $tables[] = 'vc_news';
        $tables[] = 'vc_online';
        $tables[] = 'vc_persistent_login';
        $tables[] = 'vc_picture';
        $tables[] = 'vc_picture_warning';
        $tables[] = 'vc_poll';
        $tables[] = 'vc_poll_option';
        $tables[] = 'vc_poll_selection';
        $tables[] = 'vc_profile';
        $tables[] = 'vc_profile_hobby';
        $tables[] = 'vc_profile_visit';
        $tables[] = 'vc_questionaire';
        $tables[] = 'vc_redirect';
        $tables[] = 'vc_registration_referer';
        $tables[] = 'vc_search';
        $tables[] = 'vc_searchstring_index';
        $tables[] = 'vc_setting';
        $tables[] = 'vc_subscription';
        $tables[] = 'vc_suspicion';
        $tables[] = 'vc_system_message';
        $tables[] = 'vc_termsofuse';
        $tables[] = 'vc_termsofuse_confirm';
        $tables[] = 'vc_ticket';
        $tables[] = 'vc_ticket_message';
        $tables[] = 'vc_tips';
        $tables[] = 'vc_toldafriend';
        $tables[] = 'vc_update';
        $tables[] = 'vc_user_ip_log';

        foreach ($tables as $table) {
            $query = 'OPTIMIZE TABLE ' . $table;
            if (!$this->isTestMode()) {
                $this->getDb()->execute($query);
            }
        }
    }
}
