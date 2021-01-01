<h1><?php echo gettext("menu.chat") ?></h1><?php
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><h3><?php echo gettext('chat.openchat')?></h3><?php
?><div class="chat links"><?php
?><a href="/chat/rooms/" class="chatImg" target="_blank"><?php
?><img src="<?php echo $this->imagesPath?>chat64.png" alt="<?php echo gettext('chat.openchat')?>" title="<?php echo gettext('chat.openchat')?>" width="64" height="64" /><?php
?></a><?php
?><a class="link" href="/chat/rooms/" target="_blank"><?php echo gettext('chat.openchat')?></a><?php
?></div><?php
?><h3><?php echo gettext('chat.security.title')?></h3><?php
?><div class="chat"><?php echo str_replace(array('%LINK_START%', '%LINK_END%'),
array('<a href="' . $this->path . 'help/">', '</a>'),
gettext('chat.security'))?></div><?php
?><h3><?php echo gettext('chat.profilesOnline')?></h3><?php
if (count($this->profiles) == 0) {
?><div class="notifyInfo"><?php echo gettext('chat.profilesNone'); ?></div><?php
} else {
?><ul id="userList" class="clearfix"><?php
foreach ($this->profiles as $profile)
{
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$profile,
'picture'=>$this->pictures[$profile->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked));
}
?></ul><?php
}