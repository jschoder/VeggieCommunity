<ul class="followed itembox"><?php
foreach ($this->profiles as $profile) {
echo $this->element(
'profilebox',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$profile,
'picture'=>$this->pictures[$profile->id],
'matchPercentage'=>(
array_key_exists($profile->id, $this->matchPercentages)
? $this->matchPercentages[$profile->id]
: null
),
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline
)
);
}
?></ul>