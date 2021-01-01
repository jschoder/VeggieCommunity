<?php
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div id="userBasics" class="clearfix">
<div id="basics" class="clearfix">
<?php
$picture = $this->defaultPicture;
if ($picture instanceof vc\object\SavedPicture) {
if (!empty($_SERVER["HTTP_USER_AGENT"]) &&
(strpos("Bot", $_SERVER["HTTP_USER_AGENT"]) || strpos("Spider", $_SERVER["HTTP_USER_AGENT"]))) {
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture,
'width' => 50,
'height' => 50
)
);
} else {
?><a class="jZoom" title="<?php echo prepareHTML($picture->getDescription(), false)?>" href="/user/picture/full/<?php echo $picture->getFilename()?>"><?php
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture,
'width' => 50,
'height' => 50
)
);
?></a><?php
}
} else {
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture,
'width' => 50,
'height' => 50
)
);
}
?><div>
<div class="clearfix">
<strong class="<?php if ($this->profile->realMarker) { echo 'real'; } if ($this->profile->plusMarker) { echo ' plus'; } ?>"><?php echo prepareHTML($this->profile->nickname, false)?></strong>
</div>
<ul class="userinfo clearfix"><?php
if ($this->threadReceivedSent === vc\object\PmThread::STATUS_BIDIRECTIONAL):
?><li class="receivedSent bidirectional" title="<?php echo gettext('profile.threadReceivedSent.bidirectional') ?>"></li><?php
elseif ($this->threadReceivedSent === vc\object\PmThread::STATUS_RECEIVED):
?><li class="receivedSent received" title="<?php echo gettext('profile.threadReceivedSent.received') ?>"></li><?php
elseif ($this->threadReceivedSent === vc\object\PmThread::STATUS_SENT):
?><li class="receivedSent sent" title="<?php echo gettext('profile.threadReceivedSent.sent') ?>"></li><?php
endif;
if ($this->profile->hideAge !== true) { ?>
<li><?php echo $this->profile->age?></li><?php
}
if ($this->profile->gender == 4) {
?><li title="<?php echo gettext('profile.gender.female')?>">&#x2640; <?php echo gettext('profile.gender.female')?></li><?php
} elseif ($this->profile->gender == 2) {
?><li title="<?php echo gettext('profile.gender.male')?>">&#x2642; <?php echo gettext('profile.gender.male')?></li><?php
} elseif ($this->profile->gender == 6) {
?><li title="<?php echo gettext('profile.gender.other')?>">&Oslash; <?php echo gettext('profile.gender.other')?></li><?php
}
?><li><?php echo prepareHTML($this->profile->getHtmlLocation()) ?></li><?php
if (!empty($this->matchPercentage)) {
?><li class="match match<?php echo round($this->matchPercentage / 10) ?>"><?php echo $this->matchPercentage ?>%</li><?php
}
if (in_array($this->profile->id, $this->usersOnline)) {
?><li class="online"><?php echo gettext('profile.isonline.short')?></li><?php
}  else {
?><li class="offline"><?php echo gettext('profile.isoffline.short')?></li><?php
} ?>
</ul>
</div>
</div><!-- #basics -->
<dl class="clearfix">
<dt><?php echo gettext("profile.created")?></dt> <dd><?php echo $this->prepareDate(gettext("profile.created.dateformat"),  strtotime($this->profile->firstEntry)) ?></dd>
<dt><?php echo gettext("profile.lastupdate")?></dt> <dd><?php echo $this->prepareDate(gettext("profile.lastupdate.dateformat"),  strtotime($this->profile->lastUpdate)) ?></dd>
<dt><?php echo gettext("profile.lastlogin")?></dt>
<dd><?php
$lastLogin = time() - strtotime($this->profile->lastLogin);
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
if (!empty($this->profile->lastChatLogin)) {
?><dt><?php echo gettext('profile.lastchatlogin') ?></dt>
<dd><?php
$lastChatLogin = time() - strtotime($this->profile->lastChatLogin);
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
if ($this->isAdmin) { ?>
<?php if ($this->profile->reminderDate > 0) { ?><dt><?php echo gettext("profile.warndate")?>:</dt> <dd><?php echo $this->prepareDate(gettext("profile.warndate.dateformat"),  strtotime($this->profile->reminderDate)) ?></dd><?php } ?>
<?php if ($this->profile->deleteDate > 0) { ?><dt><?php echo gettext("profile.deletedate")?>:</dt> <dd><?php echo $this->prepareDate(gettext("profile.deletedate.dateformat"),  strtotime($this->profile->deleteDate)) ?></dd><?php } ?>
<?php }
?></dl>
</div><!-- #userBasics -->
<div class="userActions">
<?php
foreach ($this->actions as $action) {
echo $action->__toString();
}
?><div class="context">
<a class="show-context">...</a>
<div class="context-menu jThreadContextMenu">
<ul><?php
foreach ($this->extendedActions as $action) {
echo '<li>' . $action->__toString() . '</li>';
}
?></ul>
</div>
</div><?php
?></div><?php
echo $this->element('tabs/user',
array('path' => $this->path,
'site' => $this->site,
'defaultTab' => $this->defaultTab,
'profile' => $this->profile,
'profilePictures' => $this->profilePictures,
'sharedGroups' => $this->sharedGroups,
'publicGroups' => $this->publicGroups,
'friends' => $this->friends,
'isAdmin' => $this->isAdmin));
?>
<div class="slideBox profile">
<div id="profileTabInfo" class="tabInfoContent" <?php if ($this->defaultTab !== 'info') { ?>style="display:none"<?php } ?>>
<div><?php
?><h2 id="slide-header-info" class="openslide" onclick="setSlideVisible('info', false)"><?php echo gettext("profile.tab.info")?></h2><?php
?><div id="slide-content-info" >
<dl id="info" class="clearfix"><?php
if ($this->profile->zodiac > 1) echoField(
gettext("profile.zodiac"),
"",
$this->profile->output->zodiac);
echoListField(
gettext("profile.search"),
"",
\vc\config\Fields::getSearchFields(),
$this->profile->search);
if (vc\config\Fields::containsFriendsSearchField($this->profile->search) &&
($this->profile->ageFromFriends !== 8 || $this->profile->ageToFriends != 120)) {
echoField(
gettext("profile.agespectrum.friends"),
"",
$this->profile->ageFromFriends . ' - ' . $this->profile->ageToFriends);
}
if (vc\config\Fields::containsRomanticSearchField($this->profile->search) &&
($this->profile->ageFromRomantic > 18 || $this->profile->ageToRomantic != 120)) {
echoField(
gettext("profile.agespectrum.romantic"),
"",
$this->profile->ageFromRomantic . ' - ' . $this->profile->ageToRomantic);
}
echoField(
gettext("profile.nutrition"),
"",
\vc\config\Fields::getNutritionCaption($this->profile->nutrition, $this->profile->nutritionFreetext, $this->profile->gender));
if (!empty($this->profile->city) && $this->profile->city != "")
{
echoField(
gettext("profile.city"),
"",
$this->profile->city);
}
if ( $this->currentUser !== null &&
($this->profile->latitude  != 0 || $this->profile->longitude != 0))
{
if (($this->currentUser->id != $this->profile->id) &&
($this->currentUser->latitude  != 0 || $this->currentUser->longitude != 0))
{
if ($this->sessionSettings->getValue(\vc\object\Settings::DISTANCE_UNIT) == \vc\object\Settings::DISTANCE_UNIT_MILE)
{
$distanceUnitShort = "mi";
}
else
{
$distanceUnitShort = "km";
}
$distance = \vc\component\GeoComponent::getDistance(
$this->currentUser->latitude,
$this->currentUser->longitude,
$this->profile->latitude,
$this->profile->longitude,
$distanceUnitShort);
echoField(
gettext("profile.distance"),
"",
$distance . ' ' . $distanceUnitShort);
}
}
if (!empty($this->profile->region) && $this->profile->region != "")
{
echoField(
gettext("profile.region"),
"",
$this->profile->region);
}
echoField(
gettext("profile.country"),
"",
$this->profile->countryname);
if ($this->profile->smoking > 1) echoField(
gettext("profile.smoking"),
"",
$this->profile->output->smoking);
if ($this->profile->alcohol > 1) echoField(
gettext("profile.alcohol"),
"",
$this->profile->output->alcohol);
if ($this->profile->religion > 1) echoField(
gettext("profile.religion"),
"",
$this->profile->output->religion);
if (count($this->profile->political) > 0) echoListField(
gettext("profile.political"),
"",
\vc\config\Fields::getPoliticalFields(),
$this->profile->political);
if ($this->profile->marital > 1) echoField(
gettext("profile.marital"),
"",
$this->profile->output->marital);
if ($this->profile->children > 1) echoField(
gettext("profile.children"),
"",
$this->profile->output->children);
if ($this->profile->relocate > 1) echoField(
gettext("profile.relocate"),
"",
$this->profile->output->relocate);
if ($this->profile->bodytype > 1) echoField(
gettext("profile.bodytype"),
"",
$this->profile->output->bodytype);
if ($this->profile->bodyheight > 1) echoField(
gettext("profile.bodyheight"),
"",
$this->profile->output->bodyheight);
if ($this->profile->clothing > 1) echoField(
gettext("profile.clothing"),
"",
$this->profile->output->clothing);
if ($this->profile->haircolor > 1) echoField(
gettext("profile.haircolor"),
"",
$this->profile->output->haircolor);
if ($this->profile->eyecolor > 1) echoField(
gettext("profile.eyecolor"),
"",
$this->profile->output->eyecolor);
?></dl>
</div><?php
?></div>
<?php
if (!empty($this->profile->word1) ||
!empty($this->profile->word2) ||
!empty($this->profile->word3) ||
!empty($this->questionaires[1])) { ?>
<div>
<a id="question1"></a>
<h2 id="slide-header-question1" class="openslide" onclick="setSlideVisible('question1', false)"><?php echo gettext('profile.tab.questionaire.1') ?></h2>
<div id="slide-content-question1">
<div id="content">
<?php if ($this->profile->tabQuestionaire1Hide && $this->currentUser === null) {
echo $this->element('login.register', array('path' => $this->path));
} else { ?>
<div class="questionaire"><?php
if (!empty($this->profile->word1) ||
!empty($this->profile->word2) ||
!empty($this->profile->word3)) {
?><h3><?php echo gettext("profile.threewords")?></h3><?php
$threeWords = array();
if (!empty($this->profile->word1)) { $threeWords[] = prepareHTML($this->profile->word1); }
if (!empty($this->profile->word2)) { $threeWords[] = prepareHTML($this->profile->word2); }
if (!empty($this->profile->word3)) { $threeWords[] = prepareHTML($this->profile->word3); }
?><div><?php echo implode('&nbsp;&nbsp;', $threeWords)?></div><?php
}
foreach ($this->questionaires[1] as $caption=>$value)
{
?><h3><?php echo gettext('profile.questionaire.1.' . $caption)?></h3><?php
?><div class="question"><?php echo prepareHTML(trim($value))?></div><?php
}
?></div>
<?php } ?>
</div>
</div>
</div>
<?php } ?>
<?php if (!empty($this->questionaires[2])) { ?>
<div>
<a id="question2"></a>
<h2 id="slide-header-question2" class="openslide" onclick="setSlideVisible('question2', false)"><?php echo gettext('profile.tab.questionaire.2') ?></h2>
<div id="slide-content-question2">
<div id="content">
<?php if ($this->profile->tabQuestionaire2Hide && $this->currentUser === null) {
echo $this->element('login.register', array('path' => $this->path));
} else { ?>
<div class="questionaire"><?php
foreach ($this->questionaires[2] as $caption=>$value)
{
?><h3><?php echo gettext('profile.questionaire.2.' . $caption)?></h3><?php
?><div class="question"><?php echo prepareHTML(trim($value))?></div><?php
}
?></div>
<?php } ?>
</div>
</div>
</div>
<?php } ?>
<?php if (!empty($this->questionaires[3])) { ?>
<div>
<a id="question3"></a>
<h2 id="slide-header-question3" class="openslide" onclick="setSlideVisible('question3', false)"><?php echo gettext('profile.tab.questionaire.3') ?></h2>
<div id="slide-content-question3">
<div id="content">
<?php if ($this->profile->tabQuestionaire3Hide && $this->currentUser === null) {
echo $this->element('login.register', array('path' => $this->path));
} else { ?>
<div class="questionaire"><?php
foreach ($this->questionaires[3] as $caption=>$value)
{
?><h3><?php echo gettext('profile.questionaire.3.' . $caption)?></h3><?php
?><div class="question"><?php echo prepareHTML(trim($value))?></div><?php
}
?></div>
<?php } ?>
</div>
</div>
</div>
<?php } ?>
<?php if (!empty($this->questionaires[4])) { ?>
<div>
<a id="question4"></a>
<h2 id="slide-header-question4" class="openslide" onclick="setSlideVisible('question4', false)"><?php echo gettext('profile.tab.questionaire.4') ?></h2>
<div id="slide-content-question4">
<div id="content">
<?php if ($this->profile->tabQuestionaire4Hide && $this->currentUser === null) {
echo $this->element('login.register', array('path' => $this->path));
} else { ?>
<div class="questionaire"><?php
foreach ($this->questionaires[4] as $caption=>$value)
{
?><h3><?php echo gettext('profile.questionaire.4.' . $caption)?></h3><?php
?><div class="question"><?php echo prepareHTML(trim($value))?></div><?php
}
?></div>
<?php } ?>
</div>
</div>
</div>
<?php } ?>
<?php if (!empty($this->questionaires[5])) { ?>
<div>
<a id="question5"></a>
<h2 id="slide-header-question5" class="openslide" onclick="setSlideVisible('question5', false)"><?php echo gettext('profile.tab.questionaire.5') ?></h2>
<div id="slide-content-question5">
<div id="content">
<?php if ($this->profile->tabQuestionaire5Hide && $this->currentUser === null) {
echo $this->element('login.register', array('path' => $this->path));
} else { ?>
<div class="questionaire"><?php
foreach ($this->questionaires[5] as $caption=>$value)
{
?><h3><?php echo gettext('profile.questionaire.5.' . $caption)?></h3><?php
?><div class="question"><?php echo prepareHTML(trim($value))?></div><?php
}
?></div>
<?php } ?>
</div>
</div>
</div>
<?php } ?>
<?php if (!empty($this->profileHobbies) ||
!empty($this->questionaires[6]) ||
!empty($this->profile->homepage) ||
!empty($this->profile->favlink1) ||
!empty($this->profile->favlink2) ||
!empty($this->profile->favlink3)) { ?>
<div>
<a id="hobbies"></a>
<h2 id="slide-header-hobbies" class="openslide" onclick="setSlideVisible('hobbies', false)"><?php echo gettext('profile.tab.hobbies') ?></h2>
<div id="slide-content-hobbies">
<div class="hobbies clearfix">
<dl id="faveLinks" class="clearfix"><?php
echoField(
gettext("profile.homepage"),
"",
$this->profile->homepage,
true);
echoField(
gettext("profile.favlink1"),
"",
$this->profile->favlink1,
true);
echoField(
gettext("profile.favlink2"),
"",
$this->profile->favlink2,
true);
echoField(
gettext("profile.favlink3"),
"",
$this->profile->favlink3,
true);
?></dl>
<?php if ($this->currentUser === null) {
echo $this->element('login.register', array('path' => $this->path));
} else {
foreach ($this->hobbies as $groupId => $groupValues)
{
if (   array_key_exists($groupId, $this->questionaires[6])
|| count($groupValues["hobbies"]) > 0)
{
?><div><?php
?><h3><?php echo $groupValues["title"]?></h3><?php
if (count($groupValues["hobbies"]) > 0)
{
?><ul class="clearfix"><?php
foreach ($groupValues["hobbies"] as $hobbyId => $hobbyName)
{
?><li><?php echo prepareHTML($hobbyName, false)?></li><?php
}
?></ul><?php
}
if (array_key_exists($groupId, $this->questionaires[6]))
{
if (count($groupValues["hobbies"]) > 0)
{
?><h4><?php echo gettext('profile.hobbies.customtext')?></h4><?php
}
?><div class="question"><?php echo prepareHTML(trim($this->questionaires[6][$groupId]))?></div><?php
}
?></div><?php
}
}
}
?>
</div>
</div>
</div>
<?php } ?>
</div>
<?php if (count($this->profilePictures) > 0 || $this->profilePicturesHidden) { ?>
<div id="profileTabAlbum" <?php if ($this->defaultTab !== 'album') { ?>style="display:none"<?php } ?>><?php
if ($this->currentUser === null && $this->profilePicturesHidden):
echo $this->element('login.register', array('path' => $this->path));
endif;
if (count($this->profilePictures) > 0):
?><table class="album">
<colgroup>
<col width="25%" />
<col width="25%" />
<col width="25%" />
<col width="25%" />
</colgroup>
<tr><?php
for($i=0; $i<count($this->profilePictures); $i++)
{
$picture = $this->profilePictures[$i];
?><td>
<a class="jZoom" title="<?php echo prepareHTML($picture->getDescription(), false)?>" href="/user/picture/full/<?php echo $picture->getFilename()?>">
<?php
echo $this->element('picture.small',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture,
'class' => 'img-link'));
?>
<span><?php echo gettext('profile.album.fullsize')?></span>
</a>
</td><?php
if (($i + 1) % 4 == 0 && $i + 1 < count($this->profilePictures))
{
?></tr><tr><?php
}
}
while($i%4>0)
{
?><td>&nbsp;</td><?php
$i++;
}
?></tr>
</table><?php
endif;
?></div>
<?php } ?>
<?php if (!empty($this->sharedGroups) || !empty($this->publicGroups)) { ?>
<div id="profileTabGroups" <?php if ($this->defaultTab !== 'groups') { ?>style="display:none"<?php } ?>><?php
if (!empty($this->sharedGroups)) {
?><h2 id="slide-header-sharedGroups" class="openslide" onclick="setSlideVisible('sharedGroups', false)"><?php echo gettext('profile.groups.shared') ?></h2>
<div id="slide-content-sharedGroups">
<ul id="groupList" class="groups clearfix">
<?php foreach ($this->sharedGroups as $group) { ?>
<li class="groupBox clearfix">
<a href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/" class="image"><?php
if (empty($group->image)) {
?><img src="/img/lemongras/default-group.png" width="70" height="70" /><?php
} else {
?><img src="/groups/picture/crop/74/74/<?php echo $group->image ?>" width="70" height="70" /><?php
}
?></a>
<div class="groupinfo">
<a class="groupname" href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/"><?php echo prepareHTML($group->name) ?></a>
</div>
</li>
<?php } ?>
</ul>
</div><?php
}
if (!empty($this->publicGroups)) {
?><h2 id="slide-header-publicGroups" class="openslide" onclick="setSlideVisible('publicGroups', false)"><?php echo gettext('profile.groups.public') ?></h2>
<div id="slide-content-publicGroups">
<ul id="groupList" class="groups clearfix">
<?php foreach ($this->publicGroups as $group) { ?>
<li class="groupBox clearfix">
<a href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/" class="image"><?php
if (empty($group->image)) {
?><img src="/img/lemongras/default-group.png" width="70" height="70" /><?php
} else {
?><img src="/groups/picture/crop/74/74/<?php echo $group->image ?>" width="70" height="70" /><?php
}
?></a>
<div class="groupinfo">
<a class="groupname" href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/"><?php echo prepareHTML($group->name) ?></a>
</div>
</li>
<?php } ?>
</ul>
</div><?php
}
?></div>
<?php } ?>
<?php if (count($this->friends) > 0) { ?>
<div id="profileTabFriends" <?php if ($this->defaultTab !== 'friends') { ?>style="display:none"<?php } ?>>
<?php if ($this->currentUser === null) {
echo $this->element('login.register', array('path' => $this->path));
} else { ?>
<ul id="userList" class="friends clearfix"><?php
for($i=0; $i<count($this->friends) && $i<count($this->friendProfiles); $i++)
{
$friendProfile = $this->friendProfiles[$i];
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$friendProfile,
'picture'=>$this->friendPictures[$friendProfile->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked));
}
?>
</ul>
<?php } ?>
</div>
<?php } ?>
</div>
<?php
// :TODO: - replace with hardcoded html
function echoField($caption, $img, $value, $link = false)
{
if (!empty($value) && $value != "")
{
?><dt class="caption"><?php echo $caption?></dt><?php
?><dd class="field"><?php
if ($link)
{
$url = prepareURL($value);
if ($url === FALSE):
echo prepareHTML($value);
else:
?><a target="_blank" href="<?php echo $url ?>" rel="nofollow"><?php echo prepareHTML($value) ?></a><?php
endif;
}
else
{
echo($value);
}
?></dd><?php
}
}
//-----------------------------------------------------------------------------
function echoListField($caption, $img, $captionArray, $values)
{
if (count($values) > 0)
{
?><dt class="caption"><?php echo $caption?><?php
if ($img != "")
{
?> <img src="<?php echo $this->imagesPath?><?php echo $img?>" class="link" alt="" width="16" height="16"/><?php
}
?></dt><?php
?><dd class="field"><ul><?php
foreach ($captionArray as $fieldKey=>$fieldValue)
{
if (in_array($fieldKey, $values))
{
?><li><?php echo $fieldValue?></li><?php
}
}
?></ul></dd><?php
}
}