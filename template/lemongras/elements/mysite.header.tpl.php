<div id="userBasics" class="clearfix">
<div id="basics" class="clearfix">
<?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'width' => 50,
'height' => 50));
?>
<div>
<div>
<strong class="<?php if ($this->currentUser->realMarker) { echo 'real'; }  if ($this->currentUser->plusMarker) { echo ' plus'; } ?>"><?php echo prepareHTML($this->currentUser->nickname, false)?></strong>
</div>
<ul><?php
if ($this->currentUser->hideAge !== true) {
?><li><?php echo $this->currentUser->age?></li><?php
}
if ($this->currentUser->gender == 4) {
?><li title="<?php echo gettext('profile.gender.female')?>">&#x2640; <?php echo gettext('profile.gender.female')?></li><?php
} elseif ($this->currentUser->gender == 2) {
?><li title="<?php echo gettext('profile.gender.male')?>">&#x2642; <?php echo gettext('profile.gender.male')?></li><?php
} elseif ($this->currentUser->gender == 6) {
?><li title="<?php echo gettext('profile.gender.other')?>">&Oslash; <?php echo gettext('profile.gender.other')?></li><?php
}
?><li><?php echo prepareHTML($this->currentUser->getHtmlLocation()) ?></li><?php
if ($this->onlineSetting) {
?><li class="online"><?php echo gettext('profile.isonline.short')?></li><?php
}
?></ul>
</div>
</div><!-- #basics -->
<dl>
<dt><?php echo gettext("profile.created")?></dt> <dd><?php echo $this->prepareDate(gettext("profile.created.dateformat"),  strtotime($this->currentUser->firstEntry))?></dd>
<dt><?php echo gettext("profile.lastupdate")?></dt> <dd><?php echo $this->prepareDate(gettext("profile.lastupdate.dateformat"),  strtotime($this->currentUser->lastUpdate))?></dd>
<dt><?php echo gettext("profile.lastlogin")?></dt>
<dd><?php
$lastLogin = time() - strtotime($this->currentUser->lastLogin);
if ($lastLogin < 86400) {
echo gettext('profile.lastlogin.last24h');
} else if ($lastLogin < 604800) {
echo gettext('profile.lastlogin.lastWeek');
} else if ($lastLogin < 2635200) {
echo gettext('profile.lastlogin.lastMonth');
} else if ($lastLogin < 7884000) {
echo gettext('profile.lastlogin.lastThreeMonth');
} else if ($lastLogin < 15768000) {
echo gettext('profile.lastlogin.lastHalfYear');
} else if ($lastLogin < 31536000) {
echo gettext('profile.lastlogin.lastYear');
} else {
echo gettext('profile.lastlogin.beyondLastYear');
}
?></dd><?php
if (!empty($this->currentUser->lastChatLogin)) {
?><dt><?php echo gettext('profile.lastchatlogin') ?></dt>
<dd><?php
$lastChatLogin = time() - strtotime($this->currentUser->lastChatLogin);
if ($lastChatLogin < 86400) {
echo gettext('profile.lastlogin.last24h');
} else if ($lastChatLogin < 604800) {
echo gettext('profile.lastlogin.lastWeek');
} else if ($lastChatLogin < 2635200) {
echo gettext('profile.lastlogin.lastMonth');
} else if ($lastChatLogin < 7884000) {
echo gettext('profile.lastlogin.lastThreeMonth');
} else if ($lastChatLogin < 15768000) {
echo gettext('profile.lastlogin.lastHalfYear');
} else if ($lastChatLogin < 31536000) {
echo gettext('profile.lastlogin.lastYear');
} else {
echo gettext('profile.lastlogin.beyondLastYear');
}
?></dd><?php
}
?></dl>
</div><!-- #userBasics -->