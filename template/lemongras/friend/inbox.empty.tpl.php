<h1><?php echo gettext('mailbox.friendinbox')?></h1><?php
require(dirname(__FILE__) . '/_tabs.php');
?><div id="friendinbox-empty" class="notifyInfo"><?php echo $this->notification['message']; ?></div>