<?php

//	vc_subscription 	  	 	 	 	 	 	 	635 	 	 	64 KiB 	-

namespace vc\controller\web\mod;

class TmgController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin() || empty($this->siteParams)) {
            throw new \vc\exception\NotFoundException();
        }

        $userId = intval($this->siteParams[0]);

        $entityTypes = \vc\helper\ConstantHelper::getConstants(
            'vc\config\EntityTypes'
        );
        $eventVisibilities = \vc\helper\ConstantHelper::getConstants(
            'vc\object\Event',
            null,
            'EVENT_VISIBILITY_'
        );
        $eventGuestVisibilities = \vc\helper\ConstantHelper::getConstants(
            'vc\object\Event',
            null,
            'GUEST_VISIBILITY_'
        );
        $eventCategories = array();
        foreach (\vc\config\Fields::getEventCategories() as $key => $eventCategory) {
            $eventCategories[$key] = $eventCategory['class'];
        }
        $eventParticipantDegree = \vc\helper\ConstantHelper::getConstants(
            'vc\object\EventParticipant',
            null,
            'STATUS_'
        );
        $groupRoles = \vc\helper\ConstantHelper::getConstants(
            'vc\object\GroupRole',
            null,
            'ROLE_'
        );
        $recipientStatus = \vc\helper\ConstantHelper::getConstants(
            'vc\object\Mail',
            null,
            'RECIPIENT_STATUS_'
        );
        $senderStatus = \vc\helper\ConstantHelper::getConstants(
            'vc\object\Mail',
            null,
            'SENDER_STATUS_'
        );
        $settings = \vc\helper\ConstantHelper::getConstants(
            'vc\object\Settings',
            array('SAVEDSEARCH_DISPLAY_', 'DISTANCE_UNIT_')
        );
        $realCheckStatus = \vc\helper\ConstantHelper::getConstants(
            'vc\object\RealCheck',
            null,
            'STATUS_'
        );
        $plusTypes = \vc\helper\ConstantHelper::getConstants(
            'vc\object\Plus',
            null,
            'PLUS_TYPE_'
        );
        $plusPaymentTypes = \vc\helper\ConstantHelper::getConstants(
            'vc\object\Plus',
            null,
            'PAYMENT_TYPE_'
        );

        header("Content-Type: plain/text");

        $this->queryTable(
            'ACTIVATION TOKEN',
            'SELECT * FROM vc_activation_token WHERE profile_id = ?',
            array($userId),
            array(
                'token' => 'token',
                'created_at' => 'created at',
                'used_at' => 'activated'
            )
        );
        $this->queryTable(
            'CHANGED PW TOKEN',
            'SELECT * FROM vc_change_pw_token WHERE profile_id = ?',
            array($userId),
            array(
                'token' => 'token',
                'created_at' => 'created at',
                'used_at' => 'activated',
                'ip' => 'ip'
            )
        );
        $this->queryTable(
            'BLOCKED',
            'SELECT * FROM vc_blocked WHERE profile_id = ? AND blocked_by_admin = 0',
            array($userId),
            array(
                'blocked_id' => 'blocked user',
                'created_at' => 'created at',
                'deleted_at' => 'deleted at'
            )
        );
        $this->queryTable(
            'CHAT',
            'SELECT channel, dateTime, ip, text FROM ajax_chat_messages_archive WHERE userID = ?
            UNION
            SELECT channel, dateTime, ip, text FROM ajax_chat_messages WHERE userID = ?
            UNION
            SELECT channel, dateTime, ip, text FROM ajax_chat_messages_censored WHERE userID = ?',
            array($userId, $userId, $userId),
            array(
                'channel' => 'channel',
                'dateTime' => 'created at',
                'ip' => 'ip address',
                'text' => 'text'
            )
        );
        $this->queryTable(
            'EVENT',
            'SELECT * FROM vc_event WHERE created_by = ?',
            array($userId),
            array(
                'hash_id' => 'id',
                // group_id
                'name' => 'name',
                'description' => 'description',
                'start_date' => 'start',
                'end_date' => 'end',
                'image' => 'image',
                'location_caption' => 'location',
                'location_lat' => 'location (lat)',
                'location_lng' => 'location (lng)',
                'url' => 'url',
                'fb_url' => 'url (fb)',
                'event_visibility' => 'visibility event',
                'guest_visibility' => 'visibility guests',
                'can_guest_invite' => 'guests can invite',
                'category_id' => 'category',
                'created_at' => 'created at',
                'deleted_by' => 'deleted by',
                'deleted_at' => 'deleted at'
            ),
            array(
                'event_visibility' => $eventVisibilities,
                'guest_visibility' => $eventGuestVisibilities,
                'category_id' => $eventCategories
            )
        );
        $this->queryTable(
            'EVENT PARTICIPANT',
            'SELECT vc_event_participant.*, vc_event.hash_id FROM vc_event_participant ' .
            'INNER JOIN vc_event ON vc_event.id = vc_event_participant.event_id ' .
            'WHERE vc_event_participant.created_by = ?',
            array($userId),
            array(
                'hash_id' => 'event',
                'degree' => 'degree',
                'is_host' => 'host',
                'created_at' => 'created at',
                'last_update' => 'last change'
            ),
            array(
                'degree' => $eventParticipantDegree
            )
        );
        $this->queryTable(
            'FORUM THREAD',
            'SELECT * FROM vc_forum_thread WHERE created_by = ?',
            array($userId),
            array(
                'hash_id' => 'id',
                'subject' => 'subject',
                'body' => 'text',
                'picture' => 'picture',
                'is_sticky' => 'sticky',
                'created_at' => 'created at',
                'updated_at' => 'updated at',
                'deleted_at' => 'deleted at'
            )
        );
        $this->queryTable(
            'LIKES',
            'SELECT * FROM vc_like WHERE profile_id = ?',
            array($userId),
            array(
                'up_down' => 'up / down',
                'created_at' => 'created at'
            )
        );
        $this->queryTable(
            'FRIEND',
            'SELECT * FROM vc_friend WHERE friend1id = ? OR friend2id = ?',
            array($userId, $userId),
            array(
                'friend1id' => 'friend a',
                'friend2id' => 'friend b',
                'friend2_accepted' => 'confirmed',
                'created' => 'created at'
            )
        );
        $this->queryTable(
            'TERMS OF USE / PRIVACY POLICY',
            'SELECT * FROM vc_termsofuse_confirm WHERE profile_id = ?',
            array($userId),
            array(
                'confirmation_date' => 'confirmed at',
                'ip' => 'ip address'
            )
        );
        $this->queryTable(
            'PERSISTENT LOGIN',
            'SELECT * FROM vc_persistent_login WHERE profile_id = ?',
            array($userId),
            array(
                'token' => 'token',
                'user_agent' => 'agent',
                'expires_at' => 'expires at',
                'active' => 'is active'
            )
        );
        $this->queryTable(
            'LOGIN',
            'SELECT * FROM vc_user_ip_log WHERE profile_id = ?',
            array($userId),
            array(
                'access' => 'created at',
                'ip' => 'ip address'
            )
        );
        $this->queryTable(
            'GROUP',
            'SELECT * FROM vc_group WHERE created_by = ?',
            array($userId),
            array(
                'hash_id' => 'id',
                'image' => 'image',
                'name' => 'name',
                'description' => 'description',
                'rules' => 'rules',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
                'language' => 'language',
                'member_visibility' => 'visibility of Members',
                'auto_confirm_members' => 'automatic Confirmation of Members',
                'activity' => 'activity',
                'created_by' => 'created at'
            )
        );
        $this->queryTable(
            'BANNED GROUP MEMBERS',
            'SELECT * FROM vc_group_ban ' .
            'INNER JOIN vc_group ON vc_group_ban.group_id = vc_group.id ' .
            'WHERE banned_by = ?',
            array($userId),
            array(
                'hash_id' => 'group',
                'profile_id' => 'user',
                'banned_at' => 'banned at',
                'reason' => 'reason'
            )
        );
        $this->queryTable(
            'GROUP MEMBERSHIP',
            'SELECT vc_group_member.*, vc_group.hash_id as group_hash_id FROM vc_group_member
             INNER JOIN vc_group ON vc_group_member.group_id = vc_group.id
             WHERE profile_id = ?',
            array($userId),
            array(
                'group_hash_id' => 'group',
                'created_at' => 'created at',
                'confirmed_at' => 'confirmed at'
            )
        );
        $this->queryTable(
            'GROUP ROLE',
            'SELECT vc_group_role.*, vc_group.hash_id as group_hash_id FROM vc_group_role
             INNER JOIN vc_group ON vc_group_role.group_id = vc_group.id
             WHERE profile_id = ?',
            array($userId),
            array(
                'group_hash_id' => 'group',
                'role' => 'role'
            ),
            array(
                'role' => $groupRoles
            )
        );
        $this->queryTable(
            'GROUP INVITATION',
            'SELECT vc_group_invitation.*, vc_group.hash_id as group_hash_id FROM vc_group_invitation
             INNER JOIN vc_group ON vc_group.id = vc_group_invitation.group_id
             WHERE vc_group_invitation.created_by = ?',
            array($userId),
            array(
                'group_hash_id' => 'group',
                'profile_id' => 'user',
                'comment' => 'comment',
                'created_at' => 'created at'
            )
        );
        $this->queryTable(
            'GROUP FORUM',
            'SELECT vc_group_forum.*, vc_group.hash_id as group_hash_id FROM vc_group_forum
             INNER JOIN vc_group ON vc_group.id = vc_group_forum.group_id
             WHERE vc_group.created_by = ?',
            array($userId),
            array(
                'group_hash_id' => 'group',
                'hash_id' => 'forum',
                'name' => 'name',
                'content_visibility' => 'visibility of content',
                'is_main' => 'main forum',
                'weight' => 'sort'
            )
        );
        $this->queryTable(
            'FORUM COMMENT',
            'SELECT * FROM vc_forum_thread_comment WHERE created_by = ?',
            array($userId),
            array(
                'hash_id' => 'id',
                'body' => 'text',
                'created_at' => 'created at',
                'updated_at' => 'updated at',
                'deleted_at' => 'deleted at'
            )
        );
        $this->queryTable(
            'SUBSCRIPTION',
            'SELECT vc_subscription.*,
                    vc_group_forum.hash_id as forum_hash_id,
                    vc_forum_thread.hash_id as thread_hash_id
             FROM vc_subscription
             LEFT JOIN vc_group_forum
                ON vc_subscription.entity_type = 20 AND vc_group_forum.id = vc_subscription.entity_id
             LEFT JOIN vc_forum_thread
                ON vc_subscription.entity_type = 21 AND vc_forum_thread.id = vc_subscription.entity_id
             WHERE profile_id = ?',
            array($userId),
            array(
                'entity_type' => 'type',
                'forum_hash_id' => 'id',
                'thread_hash_id' => 'id',
            ),
            array(
                'entity_type' => $entityTypes
            )
        );
        $this->queryTable(
            'MESSAGE (INCOMING)',
            'SELECT * FROM vc_message WHERE recipientid = ?',
            array($userId),
            array(
                'recipientstatus' => 'status',
                'recipientreplied' => 'replied',
                'recipient_delete_date' => 'deleted at',
                'subject' => 'subject',
                'body' => 'text',
                'created' => 'created at'
            ),
            array(
                'recipientstatus' => $recipientStatus
            )
        );
        $this->queryTable(
            'MESSAGE (OUTGOING)',
            'SELECT * FROM vc_message WHERE senderid = ?',
            array($userId),
            array(
                'senderstatus' => 'status',
                'subject' => 'subject',
                'body' => 'text',
                'created' => 'created at'
            ),
            array(
                'senderstatus' => $senderStatus
            )
        );
        $this->queryTable(
            'PROFILE',
            'SELECT * FROM vc_profile WHERE id = ?',
            array($userId),
            array(
                'nickname' => 'name',
                'gender' => 'gender',
                'birth' => 'birthdate',
                'age' => 'age',
                'hide_age' => 'hide age',
                'age_from_friends' => 'age from (friends)',
                'age_to_friends' => 'age to (friends)',
                'age_from_romantic' => 'age from (romantic)',
                'age_to_romantic' => 'age to (romantic)',
                'email' => 'email',
                'password' => 'password',
                'salt' => 'salt',
                'postalcode' => 'postal',
                'residence' => 'city',
                'region' => 'region',
                'country' => 'country',
                'search' => 'search',
                'zodiac' => 'zodiac',
                'nutrition' => 'nutrition',
                'nutrition_freetext' => 'nutrition (freetext)',
                'smoking' => 'smoking',
                'alcohol' => 'alcohol',
                'religion' => 'religion',
                'children' => 'children',
                'political' => 'political',
                'marital' => 'marital',
                'bodyheight' => 'height',
                'bodytype' => 'bodytype',
                'clothing' => 'clothing',
                'haircolor' => 'hair color',
                'eyecolor' => 'eye color',
                'relocate' => 'willing to relocate',
                'word1' => 'word1',
                'word2' => 'word2',
                'word3' => 'word3',
                'tabQuestionaire1Hide' => 'hide questionaire tab 1',
                'tabQuestionaire2Hide' => 'hide questionaire tab 2',
                'tabQuestionaire3Hide' => 'hide questionaire tab 3',
                'tabQuestionaire4Hide' => 'hide questionaire tab 4',
                'tabQuestionaire5Hide' => 'hide questionaire tab 5',
                'facebook_id' => 'facebookid',
                'first_entry' => 'created at',
                'last_update' => 'updated at',
                'last_login' => 'last login',
                'last_chat_login' => 'last chat login',
                'reminder_date' => 'last login reminder',
                'counter' => 'visit count',
                'locale' => 'language',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
                'homepage' => 'homepage',
                'favlink1' => 'favorite link 1',
                'favlink2' => 'favorite link 2',
                'favlink3' => 'favorite link 3',
                'plus_marker' => 'plus highlight',
                'real_marker' => 'real highlight',
                'admin' => 'admin',
                'chat_marker' => 'chat highlight',
                'chat_banned' => 'chat banned',
                'debuginfo' => 'debuginfo',
                'registration_referer' => 'referer',
            )
        );
        $this->queryTable(
            'PROFILE (SEARCH)',
            'SELECT * FROM vc_profile_field_search WHERE profile_id = ?',
            array($userId),
            array(
                'field_value' => 'value'
            )
        );
        $this->queryTable(
            'PROFILE (POLITICAL)',
            'SELECT * FROM vc_profile_field_political WHERE profile_id = ?',
            array($userId),
            array(
                'field_value' => 'value'
            )
        );
        $this->queryTable(
            'PROFILE (HOBBIES)',
            'SELECT * FROM vc_profile_hobby WHERE profileid = ?',
            array($userId),
            array(
                'hobbyid' => 'value'
            )
        );
        $this->queryTable(
            'PROFILE (QUESTIONAIRE)',
            'SELECT * FROM vc_questionaire WHERE profileid = ?',
            array($userId),
            array(
                'topic' => 'group',
                'item' => 'item',
                'content' => 'text'
            )
        );
        $this->queryTable(
            'PICTURE',
            'SELECT vc_picture.*, vc_real_picture.real_check_id FROM vc_picture
             LEFT JOIN vc_real_picture ON vc_real_picture.picture_id = vc_picture.id
             WHERE profileid = ?',
            array($userId),
            array(
                'filename' => 'name',
                'description' => 'description',
                'weight' => 'order',
                'defaultpic' => 'default picture',
                'width' => 'picture width',
                'height' => 'picture height',
                'real_check_id' => 'real check',
                'creation' => 'created at'
            )
        );
        $this->queryTable(
            'SETTINGS',
            'SELECT * FROM vc_setting WHERE profileid = ?',
            array($userId),
            array(
                'field' => 'key',
                'value' => 'value'
            ),
            array(
                'field' => $settings
            )
        );
        $this->queryTable(
            'USER ACTIVITY',
            'SELECT * FROM vc_activity WHERE profileid = ?',
            array($userId),
            array(
                'activity_type' => 'type',
                'message' => 'message',
                'related_profileid' => 'related user',
                'created' => 'created at'
            )
        );
        $this->queryTable(
            'STORED SEARCH',
            'SELECT * FROM vc_search WHERE profileid = ?',
            array($userId),
            array(
                'name' => 'name',
                'url' => 'url',
                'message_interval' => 'message interval',
                'message_type' => 'message type',
                'last_message' => 'sent at'
            )
        );
        $this->queryTable(
            'USER FAVORITES',
            'SELECT * FROM vc_favorite WHERE profileid = ?',
            array($userId),
            array(
                'favoriteid' => 'favorite'
            )
        );
        $this->queryTable(
            'USER DESIGN',
            'SELECT * FROM vc_custom_design WHERE profile_id = ?',
            array($userId),
            array(
                'colors' => 'colors',
                'css' => 'css'
            )
        );
        $this->queryTable(
            'MATCH QUESTIONAIRE',
            'SELECT * FROM vc_matching WHERE user_id = ?',
            array($userId),
            array(
                'adventure' => 'adventure',
                'bed_ds' => 'bed ds',
                'calm' => 'calm',
                'conflict' => 'conflict',
                'couch' => 'couch',
                'driven' => 'driven',
                'extroverted' => 'extroverted',
                'individuality' => 'individuality',
                'logic' => 'logic',
                'messy' => 'messy',
                'mood' => 'mood',
                'optimistic' => 'optimistic',
                'other_ds' => 'other ds',
                'poly' => 'poly',
                'proactive' => 'proactive',
                'stayhome' => 'stayhome',
                'weird' => 'weird',
                'fitness' => 'fitness',
                'money' => 'money',
                'my_looks' => 'my looks',
                'their_looks' => 'their looks',
                'updated_at' => 'updated at'
            )
        );
        $this->queryTable(
            'MATCHES',
            'SELECT IF(min_user_id = ?, max_user_id, min_user_id) AS match_user_id, percentage FROM vc_match
             WHERE min_user_id = ? OR max_user_id = ?',
            array($userId, $userId, $userId),
            array(
                'match_user_id' => 'user id',
                'percentage' => 'match value'
            )
        );
        $this->queryTable(
            'VISITS',
            'SELECT * FROM vc_last_visitor WHERE visitor_id = ?',
            array($userId),
            array(
                'profile_id' => 'visited',
                'last_visit' => 'created at'
            )
        );
        $this->queryTable(
            'INVITED USERS',
            'SELECT * FROM vc_toldafriend WHERE profileid = ?',
            array($userId),
            array(
                'sender' => 'sender',
                'reciever1' => 'recipient a',
                'reciever2' => 'recipient b',
                'reciever3' => 'recipient c',
                'reciever4' => 'recipient d',
                'reciever5' => 'recipient e',
                'reciever6' => 'recipient f',
                'body' => 'text',
                'created' => 'created at'
            )
        );
        $this->queryTable(
            'POLL',
            'SELECT
                vc_poll.question_de,
                vc_poll.question_en,
                vc_poll_option.option_de,
                vc_poll_option.option_en
            FROM vc_poll_selection
            LEFT JOIN vc_poll_option
                ON vc_poll_option.poll_id = vc_poll_selection.poll_id AND
                   vc_poll_option.option_id = vc_poll_selection.option_id
            LEFT JOIN vc_poll ON vc_poll.poll_id = vc_poll_selection.poll_id
            WHERE vc_poll_selection.profile_id =  ?',
            array($userId),
            array(
                'question_de' => 'question (DE)',
                'question_en' => 'question (EN)',
                'option_de' => 'selection (DE)',
                'option_en' => 'selection (EN)'
            )
        );
        $this->queryTable(
            'TICKET',
            'SELECT vc_ticket.*, vc_ticket_message.body, vc_ticket_message.created_at as message_created FROM vc_ticket
             LEFT JOIN vc_ticket_message ON vc_ticket_message.ticket_id = vc_ticket.id
             WHERE vc_ticket.profile_id = ?',
            array($userId),
            array(
                'hash_id' => 'id',
                'lng' => 'language',
                'nickname' => 'name',
                'email' => 'email',
                'category' => 'category',
                'subject' => 'subject',
                'body' => 'body',
                'message_created' => 'message sent'
            )
        );
        $this->queryTable(
            'REAL CHECK',
            'SELECT * FROM vc_real_check WHERE profile_id = ?',
            array($userId),
            array(
                'code' => 'code',
                'picture' => 'picture',
                'status' => 'status',
                'created_at' => 'created at',
            ),
            array(
                'status' => $realCheckStatus
            )
        );
        $this->queryTable(
            'PLUS',
            'SELECT * FROM vc_plus WHERE user_id = ?',
            array($userId),
            array(
                'plus_type' => 'type',
                'start_date' => 'start',
                'end_date' => 'end',
                'payment_type' => 'payment'
            ),
            array(
                'plus_type' => $plusTypes,
                'payment_type' => $plusPaymentTypes
            )
        );
        $this->queryTable(
            'PLUS (PAYPAL)',
            'SELECT * FROM vc_plus_paypal_payment
             INNER JOIN vc_plus ON vc_plus.payment_type = 10 AND vc_plus.payment_id = vc_plus_paypal_payment.id
             WHERE vc_plus.user_id = ?',
            array($userId),
            array(
                'payer_id' => 'payer id',
                'paypal_create_time' => 'created at paypal',
                'paypal_update_time' => 'updated at paypal',
                'created_at' => 'created at'
            )
        );
        $this->queryTable(
            'WEBSOCKET USERS',
            'SELECT * FROM vc_websocket_user WHERE user_id = ?',
            array($userId),
            array(
                'websocket_key' => 'key',
                'created_at' => 'created at'
            )
        );
    }
    private function queryTable($name, $query, $whereValues, $fieldIndexes, $replacements = null)
    {
        echo '### ' . $name . " ###\n\n";
        $statement = $this->getDb()->queryPrepared($query, $whereValues);
        $result = $statement->get_result();
        while ($row = $result->fetch_assoc()) {
            foreach ($fieldIndexes as $sourceName => $fieldLabel) {
                if (!empty($row[$sourceName]) || $row[$sourceName] === 0) {
                    $value = $row[$sourceName];
                    if (!empty($replacements) && (!empty($replacements[$sourceName][$value]))) {
                        $value = $replacements[$sourceName][$value];
                    }
                    echo '   ' . $fieldLabel . ' :: ' . $value . "\n";
                }
            }
            echo "\n";
        }
        $statement->close();
        echo "\n";
    }
}
