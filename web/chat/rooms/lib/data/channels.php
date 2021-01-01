<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license GNU Affero General Public License
 * @link https://blueimp.net/ajax/
 */

// List containing the custom channels:
$channels = array();

/*
$channels[] = 'English';
$channels[] = 'Deutsch';
$channels[] = 'AnimalRights';
$channels[] = 'Adult/FSK18';
$channels[] = 'Niveaulos';
*
$channels[] = 'Français';
$channels[] = 'Español';
*/

			
$query = sprintf("SELECT channel_id, channel_name FROM ajax_chat_channels WHERE died IS NULL ORDER BY channel_id ASC");
$result = $this->db->sqlQuery($query);

// Stop if an error occurs:
if($result->error()) {
	echo $result->getError();
	die();
}

while($row = $result->fetch()) {
	$channel_id = $row['channel_id'];
	$channel_name = $row['channel_name'];
	$channels[$channel_id] = $channel_name;
}
$result->free();

?>