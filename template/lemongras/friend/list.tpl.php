<?php
echo $this->element('mysite.header',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser' => $this->currentUser,
'ownPicture' => $this->ownPicture,
'onlineSetting' => $this->sessionSettings->getValue(\vc\object\Settings::VISIBLE_ONLINE)));
echo $this->element('tabs/mysite',
array('path' => $this->path,
'site' => $this->site,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFavorites' => $this->ownFavorites));
if ($this->notification)
{
echo $this->element('notification',
array('notification' => $this->notification));
}
if (!empty($this->unconfirmedProfiles)):
?><div id="friendInbox">
<h2><?php echo gettext('mailbox.friendinbox') ?></h2>    
<ul class="clearfix"><?php
for($i=0; $i<count($this->unconfirmedProfiles); $i++):
$profile = $this->unconfirmedProfiles[$i];
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'id'=>'friendProfilebox' . $profile->id,
'profile'=>$profile,
'picture'=>$this->unconfirmedPictures[$profile->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked));
endfor;
?></ul>
</div><?php
endif;
?><ul id="userList" class="friends own clearfix"><?php
for($i=0; $i<count($this->friends) && $i<count($this->profiles); $i++) {
$profile = $this->profiles[$i];
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'id'=>'friendProfilebox' . $profile->id,
'profile'=>$profile,
'picture'=>$this->pictures[$profile->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked));
}?>
</ul>