<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('mysite.tab.friends') ?></h1><?php
echo $this->element('tabs/mysite',
array('path' => $this->path,
'site' => $this->site,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFavorites' => $this->ownFavorites));
if (!empty($this->unconfirmedProfiles)):
?><div id="friendInbox">
<h2><?php echo gettext('mailbox.friendinbox') ?></h2>   
<ul class="itembox"><?php
for($i=0; $i<count($this->unconfirmedProfiles); $i++):
$profile = $this->unconfirmedProfiles[$i];
$additionalBoxContent = '<aside class="actions">
<nav>
<span class="circle confirm">
<a class="confirmFriend" href="#" data-user-id="' . $profile->id . '" title="' . gettext('mailbox.friendinbox.confirm') . '"></a>
</span>
<span class="circle deny">
<a class="denyFriend" href="#" data-user-id="' . $profile->id . '" title="' . gettext('mailbox.friendinbox.deny') . '"></a>
</span>
</nav>
</aside>';
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
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
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline,
'additionalBoxContent' => $additionalBoxContent));
endfor;
?></ul>
</div><?php
endif;
?><ul id="userList" class="itembox"><?php
for($i=0; $i<count($this->friends) && $i<count($this->profiles); $i++):
$profile = $this->profiles[$i];
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
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
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline));
endfor;
?></ul><?php
$this->echoWideAd($this->locale, $this->plusLevel);