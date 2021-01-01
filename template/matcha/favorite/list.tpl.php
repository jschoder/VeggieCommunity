<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('mysite.tab.favorites') ?></h1><?php
echo $this->element('tabs/mysite',
array('path' => $this->path,
'site' => $this->site,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFavorites' => $this->ownFavorites));
?><div class="notifyInfo"><?php echo gettext("mysite.favorites.infotext") ?></div><?php
?><ul id="userList" class="itembox"><?php
foreach ($this->favoriteProfiles as $profile):
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'id'=>'favoriteProfilebox' . $profile->id,
'profile'=>$profile,
'picture'=>$this->pictures[$profile->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline));
endforeach;
?></ul><?php
$this->echoWideAd($this->locale, $this->plusLevel);