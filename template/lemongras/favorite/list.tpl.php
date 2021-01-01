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
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
} else {
?><div class="notifyInfo"><?php echo gettext("mysite.favorites.infotext") ?></div><?php
}
?><ul id="userList" class="clearfix"><?php
foreach ($this->favoriteProfiles as $profile)
{
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
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
'blocked' => $this->blocked));
}
?></ul>