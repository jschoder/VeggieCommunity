<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext("menu.chat") ?></h1><?php
?><h2><?php echo gettext('chat.openchat')?></h2>
<nav class="ctabar">
<a class="chat" href="/chat/rooms/" target="_blank"><?php echo gettext('chat.openchat')?></a>
</nav><?php
?><h2><?php echo gettext('chat.security.title')?></h2>
<p><?php
echo str_replace(array('%LINK_START%', '%LINK_END%'),
array('<a href="' . $this->path . 'help/feedback/">', '</a>'),
gettext('chat.security'))
?></p><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?><h3><?php echo gettext('chat.profilesOnline')?></h3><?php
if (count($this->profiles) == 0):
?><div class="notifyInfo"><?php echo gettext('chat.profilesNone'); ?></div><?php
else:
?><ul class="itembox"><?php
foreach ($this->profiles as $profile):
echo $this->element(
'profilebox',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$profile,
'picture'=>$this->pictures[$profile->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline
)
);
endforeach;
?></ul><?php
endif;
$this->echoWideAd($this->locale, $this->plusLevel);