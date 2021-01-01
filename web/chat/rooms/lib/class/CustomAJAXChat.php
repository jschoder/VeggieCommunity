<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license GNU Affero General Public License
 * @link https://blueimp.net/ajax/
 */

class CustomAJAXChat extends AJAXChat {

	var $_inviteOnlyChannels;

	function initDataBaseConnection() {
		parent::initDataBaseConnection();
		$this->db->sqlQuery("SET NAMES 'utf8'");
	}

	// Returns an associative array containing userName, userID and userRole
	// Returns null if login is invalid
	function getValidLoginUserData() {

		if($this->getRequestVar('password')) {
			// Check if we have a valid registered user:

			$userName = $this->getRequestVar('userName');
			$userName = $this->convertEncoding($userName, $this->getConfig('contentEncoding'), $this->getConfig('sourceEncoding'));
			$userName = mysqli_real_escape_string($this->db->getConnectionID(), $userName);
			
			$password = $this->getRequestVar('password');
			$password = $this->convertEncoding($password, $this->getConfig('contentEncoding'), $this->getConfig('sourceEncoding'));
			$password = mysqli_real_escape_string($this->db->getConnectionID(), $password);
			
			$query = sprintf("SELECT id, nickname, admin, chat_marker, first_entry FROM vc_profile " .
			                 "LEFT JOIN vc_blocked_login ON vc_blocked_login.user_id = vc_profile.id AND vc_blocked_login.blocked_till > NOW() " .
			                 "WHERE (id='%s' OR email='%s' OR nickname='%s') AND " .
			                 "password=SHA1(CONCAT(salt,'%s',salt)) AND active>0 AND chat_banned = 0 AND vc_blocked_login.user_id IS NULL",
			                 $userName, $userName, $userName, $password);
			$result = $this->db->sqlQuery($query);

			// Stop if an error occurs:
			if($result->error()) {
				echo $result->getError();
				die();
			}

			if($result->numRows() > 0) {
				$row = $result->fetch();
				$userData = array();
				$userData['userID'] = $row['id'];
				$userData['userName'] = $this->getValidUnusedNickname($userData['userID'], $row['nickname']);
				// Chat-Admin (session.php)
				if ($row['admin'] % 4 > 1) {
					$userData['userRole'] = AJAX_CHAT_ADMIN;
				// Chat-Moderator (session.php)
				} else if ($row['admin'] % 2 > 0) {
					$userData['userRole'] = AJAX_CHAT_MODERATOR;
				// Chat-User
				} else {
					$userData['userRole'] = AJAX_CHAT_USER;
				}
				$userData['chatMarker'] = $row['chat_marker'];
				$userData['createdAt'] = strtotime($row['first_entry']);

				$query = sprintf("UPDATE vc_profile SET last_chat_login = NOW() where id='%s'",
                                                 $userData['userID']);
				$this->db->sqlQuery($query);


				$query2 = sprintf("SELECT value FROM vc_setting WHERE profileid=%d AND field=31",
				                  $userData['userID']);
				$result2 = $this->db->sqlQuery($query2);
				if($result2->numRows() > 0) {
					$row2 = $result2->fetch();
					if($row2['value'] == 'de') {
						$this->setRequestVar('channelName', 'Deutsch');
					} else if($row2['value'] == 'en') {
						$this->setRequestVar('channelName', 'English');
					} else {
						$this->setRequestVar('channelName', 'English');
					}
				} else {
					$this->setRequestVar('channelName', 'English');
				}
				$result2->free();
			} else {
				$userData = null;
			}
			$result->free();

			return $userData;
			
//			$customUsers = $this->getCustomUsers();
//			foreach($customUsers as $key=>$value) {
//				if(($value['userName'] == $userName) && ($value['password'] == $password)) {
//					$userData = array();
//					$userData['userID'] = $key;
//					$userData['userName'] = $this->trimUserName($value['userName']);
//					$userData['userRole'] = $value['userRole'];
//					return $userData;
//				}
//			}
		} else {
			// Guest users:
			return $this->getGuestUser();
		}
	}

	function getValidUnusedNickname($userID, $nickname) {
		$encoded_nickname = str_ireplace(' ', '_', $nickname);
		$nickname = $encoded_nickname;
		for($i = 2; $this->isNicknameTaken($userID, $nickname); $i++) {
			$nickname = $encoded_nickname . $i;
		}
		return $nickname;
	}

	function isNicknameTaken($userID, $nickname) {
		$encoded_nickname = $this->convertEncoding($nickname, $this->getConfig('contentEncoding'), $this->getConfig('sourceEncoding'));
		$query = sprintf("SELECT userID FROM ajax_chat_online " .
		                 "WHERE userID != '%d' AND userName = '%s'",
		                 intval($userID), mysqli_real_escape_string($this->db->getConnectionID(), $encoded_nickname));
		$result = $this->db->sqlQuery($query);

		// Stop if an error occurs:
		if($result->error()) {
			echo $result->getError();
			die();
		}

		return ($result->numRows() > 0);
	}

	// Store the channels the current user has access to
	// Make sure channel names don't contain any whitespace
        
	function &getChannels($reset = false) {
            if($this->_channels === null || true) {
                $this->_channels = array();

                // Add the valid channels to the channel list (the defaultChannelID is always valid):
                foreach($this->getAllChannels() as $key=>$value) {
                        // Check if we have to limit the available channels:
                        if($this->getConfig('limitChannelList') && !in_array($value, $this->getConfig('limitChannelList'))) {
                                continue;
                        }

                        if($value == $this->getConfig('defaultChannelID') || true) {
                                $this->_channels[$key] = $value;
                        }
                }
            }
            return $this->_channels;
	}
        

	// Store all existing channels
	// Make sure channel names don't contain any whitespace
	function &getAllChannels($reset = false) {
            if($this->_allChannels === null || $reset) {
                $this->_allChannels = array();
                $this->_inviteOnlyChannels = array();

                $query = 'SELECT channel_id, channel_name, invite_only FROM ajax_chat_channels WHERE died IS NULL ORDER BY channel_id ASC';
                $statement = $this->db->sqlPreparedQuery($query, null);

                // Stop if an error occurs:
                if(empty($statement)) {
                        echo 'CustomChannel-Statement failed';
                        die();
                }
                $binded = $statement->bind_result(
                    $channelId,
                    $channelName,
                    $inviteOnly
                );
                
                $defaultChannelFound = false;
                while ($statement->fetch()) {
                    $forumName = $this->trimChannelName($channelName);
                    $this->_allChannels[$forumName] = $channelId;
                    if($channelId == $this->getConfig('defaultChannelID')) {
                        $defaultChannelFound = true;
                    }
                    
                    if ($inviteOnly) {
                        $this->_inviteOnlyChannels[] = $channelId;
                    }
                }
                $statement->close();

                if(!$defaultChannelFound) {
                    // Add the default channel as first array element to the channel list:
                    $this->_allChannels = array_merge(
                        array(
                                $this->trimChannelName($this->getConfig('defaultChannelName'))=>$this->getConfig('defaultChannelID')
                        ),
                        $this->_allChannels
                    );
                }
            }
            return $this->_allChannels;
	}

    function getChatViewMessagesXML() {
        $messages = parent::getChatViewMessagesXML();

        $channels = '';
                
        $query = 'SELECT channel, count(*) as amount FROM ajax_chat_online GROUP BY channel';
        $statement = $this->db->sqlPreparedQuery($query, null);
        
        // Stop if an error occurs:
        if(empty($statement)) {
                echo 'ChannelCount-Statement failed';
                die();
        }
        $binded = $statement->bind_result(
            $channel,
            $amount
        );

        // Add the messages in reverse order so it is ascending again:
        $channel_active_users = array();
        while ($statement->fetch()) {
            $channel_active_users[$channel] = $amount;
        }
        $statement->close();

        $channels = $this->getChannels();
        $channels_string = '';
        foreach($channels as $channel_name => $channel_id) {

            if(array_key_exists($channel_id, $channel_active_users)) {
                $user_count = $channel_active_users[$channel_id];
            } else {
                $user_count = 0;
            }
            $inviteOnly = in_array($channel_id, $this->_inviteOnlyChannels) ? 1 : 0;
            
            
            $channels_string .= '<channel id="' . $channel_id . '" user_count="' . $user_count . '" invite_only="' . $inviteOnly . '"><![CDATA['.$this->encodeSpecialChars($channel_name).']]></channel>';
        }
        $messages = '<channels>' . $channels_string . '</channels>' . $messages;
        return $messages;
  }

  function replaceCustomText(&$text) {
    $channel = $this->getChannel();
    if ($channel == 3 || $channel > 1000) {
      return $text;
    } else {
      $text = str_ireplace('fotze', '*****', $text);
      $text = str_ireplace('nigger', '*****', $text);
      $text = str_ireplace('kitzler', '*****', $text);
      $text = str_ireplace('titten', 'T****', $text);
      $text = str_ireplace('penis', 'P****', $text);
      $text = str_ireplace('vagina', 'V****', $text);
      $text = str_ireplace('ficken', 'f****', $text);
      // $text = str_replace('ficken', 'f***en', $text);
      return $text;
    }
  }
}
