<?php
if (!isset($this->id)) {
$this->id = 'profile' . $this->profile->id;
}
?><li id="<?php echo $this->id?>">
<a href="<?php echo $this->path ?>user/view/<?php echo $this->profile->id ?>/"><?php
if (in_array($this->profile->id, $this->usersOnline)):
?><span class="online" title="<?php echo gettext('profile.isonline') ?>"></span><?php
endif;
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->picture));
?></a>
<div><?php
$class = array();
if ($this->profile->realMarker):
$class[] = 'real';
endif;
if ($this->profile->plusMarker):
$class[] = 'plus';
endif;
?><a <?php if(!empty($class)) { echo 'class="' . implode(' ', $class) . '" '; } ?>href="<?php echo $this->path ?>user/view/<?php echo $this->profile->id ?>/"><?php echo prepareHTML($this->profile->nickname, false) ?></a>
<ul><?php
if (!$this->profile->hideAge) {
?><li><?php echo $this->profile->age?></li><?php
}
if ($this->profile->gender == 4) {
?><li title="<?php echo gettext('profile.gender.female')?>"><span class="female"></span></li><?php
} elseif ($this->profile->gender == 2) {
?><li title="<?php echo gettext('profile.gender.male')?>"><span class="male"></span></li><?php
} elseif ($this->profile->gender == 6) {
?><li title="<?php echo gettext('profile.gender.other')?>"><span class="queer"></span></li><?php
}
?><li><?php 
echo prepareHTML(\vc\config\Fields::getNutritionCaption($this->profile->nutrition, null, $this->profile->gender));
if ($this->currentUser !== null &&
$this->currentUser->id != $this->profile->id &&
($this->currentUser->latitude  != 0 || $this->currentUser->longitude != 0) &&
($this->profile->latitude  != 0 || $this->profile->longitude != 0)) {
if ($this->sessionSettings->getValue(\vc\object\Settings::DISTANCE_UNIT) == \vc\object\Settings::DISTANCE_UNIT_MILE) {
$distanceUnitShort = "mi";
} else {
$distanceUnitShort = "km";
}
$distance = \vc\component\GeoComponent::getDistance(
$this->currentUser->latitude,
$this->currentUser->longitude,
$this->profile->latitude,
$this->profile->longitude,
$distanceUnitShort
);
echo' <strong>(' . $distance . gettext('profile.distance.' . $distanceUnitShort) . ')</strong>';
}
?></li><?php
if (!empty($this->matchPercentage)) {
?><li class="match match<?php echo round($this->matchPercentage / 10) ?>"><?php echo $this->matchPercentage ?>%</li><?php
}
?></ul>
<p title="<?php echo prepareHTML($this->profile->getHtmlLocation()) ?>"><?php 
echo prepareHTML($this->profile->getHtmlLocation(true, $this->currentUser)) 
?></p>    
</div>
<aside class="popup">
<span class="context jTrigger" onclick="void(0)" tabindex="0"></span>
<nav class="menu"><?php
?><a class="pm" href="<?php echo $this->path ?>pm/#<?php echo $this->profile->id ?>"><?php echo gettext('profile.compose') ?></a><?php
if (in_array($this->profile->id, $this->ownFriendsConfirmed)) {
?><a class="deleteFriend" href="#" data-user-id="<?php echo $this->profile->id ?>"><?php echo gettext('mysite.friends.deletefriend') ?></a><?php
} else if (in_array($this->profile->id, $this->ownFriendsToConfirm)) {
?><a class="confirmFriend" href="#" data-user-id="<?php echo $this->profile->id ?>"><?php echo gettext('mailbox.friendinbox.confirm') ?></a><?php
?><a class="denyFriend" href="#" data-user-id="<?php echo $this->profile->id ?>"><?php echo gettext('mailbox.friendinbox.deny') ?></a><?php
} else if (in_array($this->profile->id, $this->ownFriendsWaitForConfirm)) {
?><a class="cancelFriend" href="#" data-user-id="<?php echo $this->profile->id ?>"><?php echo gettext('profile.friend.cancel') ?></a><?php
} else if (!in_array($this->profile->id, $this->blocked)) {
?><a class="addFriend" href="#" data-user-id="<?php echo $this->profile->id ?>"><?php echo gettext('profile.addfriend') ?></a><?php
}
if (in_array($this->profile->id, $this->ownFavorites)) {
?><a class="deleteFavorite" href="#" data-user-id="<?php echo $this->profile->id ?>"><?php echo gettext('mysite.friends.deletefavorite') ?></a><?php
} else if (!in_array($this->profile->id, $this->blocked)) {
?><a class="addFavorite" href="#" data-user-id="<?php echo $this->profile->id ?>"><?php echo gettext('profile.addfavorite') ?></a><?php
}
if (isset($this->actions)) {
echo implode('', $this->actions);
}
?><a class="block" href="javascript:blockUser(<?php echo $this->profile->id ?>)"><?php echo gettext('profile.blockprofile') ?></a><?php
?><a class="flag" href="<?php echo $this->path ?>help/support/reportuser/<?php echo $this->profile->id ?>/"><?php echo gettext('profile.reportprofile') ?></a><?php
if ($this->isAdmin) {
?><a class="mod" href="<?php echo $this->path ?>mod/switch/<?php echo $this->profile->id ?>/"><?php echo gettext('profile.mod.switch') ?></a><?php
}
?></nav>
</aside><?php
if (!empty($this->additionalBoxContent)):
echo $this->additionalBoxContent;
endif;
?></li>
