<?php
if (!isset($this->id)) {
$this->id = 'profile' . $this->profile->id;
}
?><li id="<?php echo $this->id?>" class="profileBox clearfix">
<a href="<?php echo $this->path ?>user/view/<?php echo $this->profile->id?>/" class="image"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->picture,
'width' => 70,
'height' => 70));
if (in_array($this->profile->id, $this->usersOnline)) {
?><span class="online"><?php echo gettext('profile.isonline.short')?></span><?php
}
?></a>
<div class="profileinfo">
<a class="username<?php if($this->profile->realMarker) { echo ' real'; } if($this->profile->plusMarker) { echo ' plus'; } ?>" href="<?php echo $this->path ?>user/view/<?php echo $this->profile->id?>/"><?php
echo prepareHTML($this->profile->nickname, false)
?></a>
<ul class="fields clearfix"><?php
if (!$this->profile->hideAge) {
?><li><?php echo($this->profile->age == 0 ? '' : $this->profile->age); ?></li><?php
}
if ($this->profile->gender == 4) {
?><li title="<?php echo gettext('profile.gender.female')?>">&#x2640;</li><?php
} elseif ($this->profile->gender == 2) {
?><li title="<?php echo gettext('profile.gender.male')?>">&#x2642;</li><?php
} elseif ($this->profile->gender == 6) {
?><li title="<?php echo gettext('profile.gender.other')?>">&Oslash;</li><?php
}
?><li><?php echo prepareHTML(\vc\config\Fields::getNutritionCaption($this->profile->nutrition, $this->profile->nutritionFreetext, $this->profile->gender)); ?></li><?php
if (!empty($this->matchPercentage)) {
?><li class="match match<?php echo round($this->matchPercentage / 10) ?>"><?php echo $this->matchPercentage ?>%</li><?php
}
?></ul>
<p><?php
echo prepareHTML($this->profile->getHtmlLocation());
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
?></p><?php
$actions = array();
$actions[] = sprintf(
'<a class="pn" href="' . $this->path . 'pm/#%d">%s</a>',
$this->profile->id,
gettext('profile.compose')
);
if (in_array($this->profile->id, $this->ownFriendsConfirmed)) {
$actions[] = sprintf(
'<a class="deleteFriend" href="#" data-user-id="%d">%s</a>',
$this->profile->id,
gettext('mysite.friends.deletefriend')
);
} else if (in_array($this->profile->id, $this->ownFriendsToConfirm)) {
$actions[] = sprintf(
'<a class="confirmFriend" href="#" data-user-id="%d">%s</a>',
$this->profile->id,
gettext('mailbox.friendinbox.confirm')
);
$actions[] = sprintf(
'<a class="denyFriend" href="#" data-user-id="%d">%s</a>',
$this->profile->id,
gettext('mailbox.friendinbox.deny')
);
} else if (in_array($this->profile->id, $this->ownFriendsWaitForConfirm)) {
$actions[] = sprintf(
'<a class="cancelFriend" href="#" data-user-id="%d">%s</a>',
$this->profile->id,
gettext('profile.friend.cancel')
);
} else if (!in_array($this->profile->id, $this->blocked)) {
$actions[] = sprintf(
'<a class="addFriend" href="#" data-user-id="%d">%s</a>',
$this->profile->id,
gettext('profile.addfriend')
);
}
if (in_array($this->profile->id, $this->ownFavorites)) {
$actions[] = sprintf(
'<a class="deleteFavorite" href="#" data-user-id="%d">%s</a>',
$this->profile->id,
gettext('mysite.friends.deletefavorite')
);
} else if (!in_array($this->profile->id, $this->blocked)) {
$actions[] = sprintf(
'<a class="addFavorite" href="#" data-user-id="%d">%s</a>',
$this->profile->id,
gettext('profile.addfavorite')
);
}
if (isset($this->actions)) {
$actions = array_merge($actions, $this->actions);
}
$actions[] = sprintf(
'<a class="flag" href="javascript:blockUser(%d)">%s</a>',
$this->profile->id,
gettext('profile.blockprofile')
);
$actions[] = sprintf(
'<a class="report" href="help/support/reportuser/%d/">%s</a>',
$this->profile->id,
gettext('profile.reportprofile')
);
if ($this->isAdmin) {
$actions[] = sprintf(
'<a class="modswitch" href="' . $this->path . 'mod/switch/%d/">%s</a>',
$this->profile->id,
gettext('profile.mod.switch')
);
}
?><div class="context">
<a class="show-context">...</a>
<div class="context-menu">
<ul><?php
echo '<li>' . implode('</li><li>', $actions) . '</li>';
?></ul>
</div>
</div>
</div><?php
if (!empty($this->additionalBoxContent))
{
?><div><?php
echo $this->additionalBoxContent
?></div><?php
}
?></li>