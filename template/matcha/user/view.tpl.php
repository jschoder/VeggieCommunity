<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><div class="basics"><?php
$picture = $this->defaultPicture;
if ($picture instanceof vc\object\SavedPicture):
if (!empty($_SERVER["HTTP_USER_AGENT"]) &&
(strpos("Bot", $_SERVER["HTTP_USER_AGENT"]) || strpos("Spider", $_SERVER["HTTP_USER_AGENT"]))):
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture
)
);
else:
?><a class="jZoom" title="<?php echo prepareHTML($picture->getDescription(), false)?>" href="/user/picture/full/<?php echo $picture->getFilename()?>"><?php
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture
)
);
?></a><?php
endif;
else:
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture
)
);
endif;
?><h1><?php echo prepareHTML($this->profile->nickname, false)?></h1>
<ul><?php
if ($this->threadReceivedSent === vc\object\PmThread::STATUS_BIDIRECTIONAL):
?><li class="receivedSent bidirectional" title="<?php echo gettext('profile.threadReceivedSent.bidirectional') ?>"></li><?php
elseif ($this->threadReceivedSent === vc\object\PmThread::STATUS_RECEIVED):
?><li class="receivedSent received" title="<?php echo gettext('profile.threadReceivedSent.received') ?>"></li><?php
elseif ($this->threadReceivedSent === vc\object\PmThread::STATUS_SENT):
?><li class="receivedSent sent" title="<?php echo gettext('profile.threadReceivedSent.sent') ?>"></li><?php
endif;
if ($this->profile->hideAge !== true):
?><li><?php echo $this->profile->age?></li><?php
endif;
if ($this->profile->gender == 4):
?><li class="female"> <?php echo gettext('profile.gender.female')?></li><?php
elseif ($this->profile->gender == 2):
?><li class="male"> <?php echo gettext('profile.gender.male')?></li><?php
elseif ($this->profile->gender == 6):
?><li class="other"> <?php echo gettext('profile.gender.other')?></li><?php
endif;
?><li><?php echo prepareHTML($this->profile->getHtmlLocation()) ?></li><?php
if ($this->profile->realMarker):
?><li><a class="real realInfo" href="<?php echo $this->path ?>account/real/" title="<?php echo gettext('profile.realmember.title') ?>"><?php echo gettext('profile.realmember') ?></a></li><?php
endif;
if ($this->profile->plusMarker):
?><li><a class="plus plusInfo" href="<?php echo $this->path ?>plus/"><?php echo gettext('profile.plusmember') ?></a></li><?php
endif;
if (in_array($this->profile->id, $this->usersOnline)):
?><li class="online" title="<?php echo gettext('profile.isonline') ?>"><?php echo gettext('profile.isonline.short')?></li><?php
endif;
if (!empty($this->matchPercentage)):
?><li class="match match<?php echo round($this->matchPercentage / 10) ?>"><?php echo $this->matchPercentage ?>%</li><?php
endif;
?></ul>
</div><?php
echo $this->element('actionbar',
array('actions' => array_merge($this->actions, $this->extendedActions)));
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
?><div id="profileTabInfo" class="tabInfoContent" <?php if ($this->defaultTab !== 'info') { ?>style="display:none"<?php } ?>>
<section class="collapsible">
<h2><?php echo gettext("profile.tab.info")?></h2>
<div>
<dl><?php
if ($this->profile->zodiac > 1) echoField(
gettext("profile.zodiac"),
$this->profile->output->zodiac);
echoListField(
gettext("profile.search"),
\vc\config\Fields::getSearchFields(),
$this->profile->search);
if (vc\config\Fields::containsFriendsSearchField($this->profile->search) &&
($this->profile->ageFromFriends !== 8 || $this->profile->ageToFriends != 120)):
echoField(
gettext("profile.agespectrum.friends"),
$this->profile->ageFromFriends . ' - ' . $this->profile->ageToFriends);
endif;
if (vc\config\Fields::containsRomanticSearchField($this->profile->search) &&
($this->profile->ageFromRomantic > 18 || $this->profile->ageToRomantic != 120)):
echoField(
gettext("profile.agespectrum.romantic"),
$this->profile->ageFromRomantic . ' - ' . $this->profile->ageToRomantic);
endif;
echoField(
gettext("profile.nutrition"),
\vc\config\Fields::getNutritionCaption($this->profile->nutrition, $this->profile->nutritionFreetext, $this->profile->gender),
false,
true);
if (!empty($this->profile->city) && $this->profile->city != ""):
echoField(
gettext("profile.city"),
$this->profile->city,
false,
true);
endif;
if ( $this->currentUser !== null &&
($this->profile->latitude  != 0 || $this->profile->longitude != 0)):
if (($this->currentUser->id != $this->profile->id) &&
($this->currentUser->latitude  != 0 || $this->currentUser->longitude != 0)):
if ($this->sessionSettings->getValue(\vc\object\Settings::DISTANCE_UNIT) == \vc\object\Settings::DISTANCE_UNIT_MILE):
$distanceUnitShort = "mi";
else:
$distanceUnitShort = "km";
endif;
$distance = \vc\component\GeoComponent::getDistance(
$this->currentUser->latitude,
$this->currentUser->longitude,
$this->profile->latitude,
$this->profile->longitude,
$distanceUnitShort);
echoField(
gettext("profile.distance"),
$distance . ' ' . $distanceUnitShort);
endif;
endif;
if (!empty($this->profile->region) && $this->profile->region != ""):
echoField(
gettext("profile.region"),
$this->profile->region);
endif;
echoField(
gettext("profile.country"),
$this->profile->countryname);
if ($this->profile->smoking > 1) echoField(
gettext("profile.smoking"),
$this->profile->output->smoking);
if ($this->profile->alcohol > 1) echoField(
gettext("profile.alcohol"),
$this->profile->output->alcohol);
if ($this->profile->religion > 1) echoField(
gettext("profile.religion"),
$this->profile->output->religion);
if (count($this->profile->political) > 0) echoListField(
gettext("profile.political"),
\vc\config\Fields::getPoliticalFields(),
$this->profile->political);
if ($this->profile->marital > 1) echoField(
gettext("profile.marital"),
$this->profile->output->marital,
false,
$this->profile->marital == 2);
if ($this->profile->children > 1) echoField(
gettext("profile.children"),
$this->profile->output->children);
if ($this->profile->relocate > 1) echoField(
gettext("profile.relocate"),
$this->profile->output->relocate);
if ($this->profile->bodytype > 1) echoField(
gettext("profile.bodytype"),
$this->profile->output->bodytype);
if ($this->profile->bodyheight > 1) echoField(
gettext("profile.bodyheight"),
$this->profile->output->bodyheight);
if ($this->profile->clothing > 1) echoField(
gettext("profile.clothing"),
$this->profile->output->clothing);
if ($this->profile->haircolor > 1) echoField(
gettext("profile.haircolor"),
$this->profile->output->haircolor);
if ($this->profile->eyecolor > 1) echoField(
gettext("profile.eyecolor"),
$this->profile->output->eyecolor);
?></dl>
<dl>
<dt><?php echo gettext('profile.created') ?></dt>
<dd><?php echo $this->prepareDate(gettext('profile.created.dateformat'), strtotime($this->profile->firstEntry)) ?></dd>
<dt><?php echo gettext('profile.lastupdate') ?></dt>
<dd><?php echo $this->prepareDate(gettext('profile.lastupdate.dateformat'), strtotime($this->profile->lastUpdate)) ?></dd>
<dt><?php echo gettext('profile.lastlogin') ?></dt>
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
if ($this->isAdmin):
if ($this->profile->reminderDate > 0):
?><dt><?php echo gettext('profile.warndate') ?></dt>
<dd><?php echo $this->prepareDate(gettext('profile.warndate.dateformat'), strtotime($this->profile->reminderDate)) ?></dd><?php
endif;
if ($this->profile->deleteDate > 0):
?><dt><?php echo gettext('profile.deletedate') ?></dt>
<dd><?php echo $this->prepareDate(gettext('profile.deletedate.dateformat'), strtotime($this->profile->deleteDate)) ?></dd><?php
endif;
endif;
?></dl>
</div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></section>
<?php
if (!empty($this->profile->word1) ||
!empty($this->profile->word2) ||
!empty($this->profile->word3) ||
!empty($this->questionaires[1])):
?><section class="collapsible">
<a id="question1"></a>
<h2><?php echo gettext('profile.tab.questionaire.1') ?></h2>
<div><?php
if ($this->profile->tabQuestionaire1Hide && $this->currentUser === null):
echo $this->element('login.register', array('path' => $this->path));
else:
?><div class="textblock"><?php
if (!empty($this->profile->word1) ||
!empty($this->profile->word2) ||
!empty($this->profile->word3)):
?><h3><?php echo gettext("profile.threewords")?></h3><?php
$threeWords = array();
if (!empty($this->profile->word1)):
$threeWords[] = prepareHTML($this->profile->word1);
endif;
if (!empty($this->profile->word2)):
$threeWords[] = prepareHTML($this->profile->word2);
endif;
if (!empty($this->profile->word3)):
$threeWords[] = prepareHTML($this->profile->word3);
endif;
?><p><?php echo implode('&nbsp;&nbsp;', $threeWords)?></p><?php
endif;
foreach ($this->questionaires[1] as $caption=>$value):
?><h3><?php echo gettext('profile.questionaire.1.' . $caption)?></h3><?php
?><p><?php echo prepareHTML(trim($value))?></p><?php
endforeach;
?></div><?php
endif;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></section><?php
endif;
if (!empty($this->questionaires[2])):
?><section class="collapsible">
<a id="question2"></a>
<h2><?php echo gettext('profile.tab.questionaire.2') ?></h2>
<div><?php
if ($this->profile->tabQuestionaire2Hide && $this->currentUser === null):
echo $this->element('login.register', array('path' => $this->path));
else:
?><div class="textblock"><?php
foreach ($this->questionaires[2] as $caption=>$value):
?><h3><?php echo gettext('profile.questionaire.2.' . $caption)?></h3><?php
?><p><?php echo prepareHTML(trim($value))?></p><?php
endforeach;
?></div><?php
endif;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></section><?php
endif;
if (!empty($this->questionaires[3])):
?><section class="collapsible">
<a id="question3"></a>
<h2><?php echo gettext('profile.tab.questionaire.3') ?></h2>
<div><?php
if ($this->profile->tabQuestionaire3Hide && $this->currentUser === null):
echo $this->element('login.register', array('path' => $this->path));
else:
?><div class="textblock"><?php
foreach ($this->questionaires[3] as $caption=>$value):
?><h3><?php echo gettext('profile.questionaire.3.' . $caption)?></h3><?php
?><p><?php echo prepareHTML(trim($value))?></p><?php
endforeach;
?></div><?php
endif;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></section><?php
endif;
if (!empty($this->questionaires[4])):
?><section class="collapsible">
<a id="question4"></a>
<h2><?php echo gettext('profile.tab.questionaire.4') ?></h2>
<div><?php
if ($this->profile->tabQuestionaire4Hide && $this->currentUser === null):
echo $this->element('login.register', array('path' => $this->path));
else:
?><div class="textblock"><?php
foreach ($this->questionaires[4] as $caption=>$value):
?><h3><?php echo gettext('profile.questionaire.4.' . $caption)?></h3><?php
?><p><?php echo prepareHTML(trim($value))?></p><?php
endforeach;
?></div><?php
endif;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></section><?php
endif;
if (!empty($this->questionaires[5])):
?><section class="collapsible">
<a id="question5"></a>
<h2><?php echo gettext('profile.tab.questionaire.5') ?></h2>
<div><?php
if ($this->profile->tabQuestionaire5Hide && $this->currentUser === null):
echo $this->element('login.register', array('path' => $this->path));
else:
?><div class="textblock"><?php
foreach ($this->questionaires[5] as $caption=>$value):
?><h3><?php echo gettext('profile.questionaire.5.' . $caption)?></h3><?php
?><p><?php echo prepareHTML(trim($value))?></p><?php
endforeach;
?></div><?php
endif;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></section><?php
endif;
if (!empty($this->profile->homepage) ||
!empty($this->profile->favlink1) ||
!empty($this->profile->favlink2) ||
!empty($this->profile->favlink3) ||
!empty($this->profileHobbies) ||
!empty($this->questionaires[6])):
?><section class="collapsible">
<a id="hobbies"></a>
<h2><?php echo gettext('profile.tab.hobbies') ?></h2>
<div><?php
if ($this->currentUser === null):
echo $this->element('login.register', array('path' => $this->path));
else:
if (!empty($this->profile->homepage) ||
!empty($this->profile->favlink1) ||
!empty($this->profile->favlink2) ||
!empty($this->profile->favlink3)) :
?><dl><?php
echoField(
gettext('profile.homepage'),
$this->profile->homepage,
true);
echoField(
gettext('profile.favlink1'),
$this->profile->favlink1,
true);
echoField(
gettext('profile.favlink2'),
$this->profile->favlink2,
true);
echoField(
gettext('profile.favlink3'),
$this->profile->favlink3,
true);
?></dl><?php
endif;
foreach ($this->hobbies as $groupId => $groupValues):
?><div class="textblock"><?php
if (   array_key_exists($groupId, $this->questionaires[6])
|| count($groupValues['hobbies']) > 0):
?><div><?php
?><h3><?php echo $groupValues['title']?></h3><?php
if (count($groupValues['hobbies']) > 0):
?><ul class="list"><?php
foreach ($groupValues['hobbies'] as $hobbyId => $hobbyName):
?><li><?php echo prepareHTML($hobbyName, false) ?></li><?php
endforeach;
?></ul><?php
endif;
if (array_key_exists($groupId, $this->questionaires[6])):
if (count($groupValues['hobbies']) > 0):
?><h4><?php echo gettext('profile.hobbies.customtext')?></h4><?php
endif;
?><p><?php echo prepareHTML(trim($this->questionaires[6][$groupId]))?></p><?php
endif;
?></div><?php
endif;
?></div><?php
endforeach;
endif;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></section><?php
endif;
?></div><?php
if (count($this->profilePictures) > 0 || $this->profilePicturesHidden):
?><div id="profileTabAlbum" <?php if ($this->defaultTab !== 'album'): ?>style="display:none"<?php endif; ?>><?php
if ($this->currentUser === null && $this->profilePicturesHidden):
echo $this->element('login.register', array('path' => $this->path));
endif;
if (count($this->profilePictures) > 0):
?><ul class="thumblist big"><?php
foreach ($this->profilePictures as $picture):
?><li>
<a class="jZoom" title="<?php echo prepareHTML($picture->getDescription(), false)?>" href="/user/picture/full/<?php echo $picture->getFilename()?>"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture,
'big' => true));
?></a>
</li><?php
endforeach;
?></ul><?php
endif;
?></div><?php
endif;
if (!empty($this->sharedGroups) || !empty($this->publicGroups)):
?><div id="profileTabGroups" <?php if ($this->defaultTab !== 'groups') { ?>style="display:none"<?php } ?>><?php
if (!empty($this->sharedGroups)):
?><h2><?php echo gettext('profile.groups.shared') ?></h2>
<ul class="itembox"><?php
foreach ($this->sharedGroups as $group):
?><li>
<a href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/" class="image"><?php
if (empty($group->image)):
?><img alt="" src="/img/matcha/thumb/default-group.png" /><?php
else:
?><img alt="" src="/groups/picture/crop/74/74/<?php echo $group->image ?>" /><?php
endif;
?></a>
<div>
<a class="groupname" href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/"><?php echo prepareHTML($group->name) ?></a>
</div>
</li><?php
endforeach;
?></ul><?php
endif;
if (!empty($this->publicGroups)):
?><h2><?php echo gettext('profile.groups.public') ?></h2>
<ul class="itembox"><?php
foreach ($this->publicGroups as $group):
?><li>
<a href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/" class="image"><?php
if (empty($group->image)):
?><img alt="" src="/img/matcha/thumb/default-group.png" /><?php
else:
?><img alt="" src="/groups/picture/crop/74/74/<?php echo $group->image ?>" /><?php
endif;
?></a>
<div>
<a class="groupname" href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/"><?php echo prepareHTML($group->name) ?></a>
</div>
</li><?php
endforeach;
?></ul><?php
endif;
?></div><?php
endif;
if (count($this->friends) > 0):
?><div id="profileTabFriends" <?php if ($this->defaultTab !== 'friends'): ?>style="display:none"<?php endif; ?>><?php
if ($this->currentUser === null):
echo $this->element('login.register', array('path' => $this->path));
else: ?>
<ul class="itembox"><?php
for($i=0; $i<count($this->friends) && $i<count($this->friendProfiles); $i++):
$friendProfile = $this->friendProfiles[$i];
$friend = $this->friends[$friendProfile->id];
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$friendProfile,
'picture'=>$this->friendPictures[$friendProfile->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline));
endfor;
?></ul><?php
endif;
?></div><?php
endif;
if ($this->isAdmin):
?><div id="profileTabMod" <?php if ($this->defaultTab !== 'mod') { ?>style="display:none"<?php } ?>>
<div style="color:#C00;font-weight:bold;margin:8px;">This view should only be available to moderators. Please contact the admins if you see it anyway!</div>
<form action="<?php echo $this->path ?>mod/user/comment/" method="post" accept-charset="UTF-8">
<input type="hidden" name="profileid" value="<?php echo $this->profile->id ?>" />
<ul><?php
foreach ($this->profileCommentLogs as $log):
?><li>
<a href="<?php echo $this->path ?>user/view/<?php echo $log->createdBy ?>/"><?php
echo $log->createdBy
?></a> | <strong><?php echo $log->createdAt ?></strong>
<p><?php echo prepareHTML($log->comment) ?></p>
</li><?php
endforeach;
?><li>
<label for="addModComment">Add Comment</label>
<textarea id="addModComment" name="comment"></textarea>
</li>
<li>
<button class="submit" type="submit">Save</button>
</li>
</ul>
</form>
<section class="collapsed">
<h2>Data</h2>
<div>
<dl><?php
echoField(
gettext('profile.mail'),
$this->profile->email);
echoField(
'Birthdate',
$this->profile->birth);
echoField(
'Age',
$this->profile->age);
echoField(
gettext('profile.status'),
$this->profile->active);
echoField(
'latitude',
$this->profile->latitude);
echoField(
'longitude',
$this->profile->longitude);
echoField(
'ForumThreads',
$this->forumThreadCount);
echoField(
'ForumComments',
$this->forumThreadCommentCount);
if (!empty($this->recipientStatusCounts)):
$recipientStatusConstants = \vc\helper\ConstantHelper::getConstants(
'vc\object\Mail',
array(),
'RECIPIENT_STATUS_'
);
?><dt>PM recipient statuses</dt><?php
?><dd>
<ul class="list"><?php
foreach ($this->recipientStatusCounts as $recipientStatus => $count):
?><li><?php
if (empty($recipientStatusConstants[$recipientStatus])):
echo $recipientStatus;
else:
echo $recipientStatusConstants[$recipientStatus];
endif;
echo ': ' . $count;
?></li><?php
endforeach;
?></ul>
</dd><?php
endif;
if (!empty($this->blockedUserList)):
?><dt>Being blocked (<?php echo count($this->blockedUserList) ?>)</dt><?php
?><dd>
<ul class="h"><?php
foreach ($this->blockedUserList as $profileId):
?><li><a href="<?php echo $this->path ?>user/view/<?php echo $profileId ?>/"><?php
echo $profileId;
?></a></li><?php
endforeach;
?></ul>
</dd><?php
endif;
if (!empty($this->relationBlockedList)):
?><dt>Blocked relations (<?php echo count($this->relationBlockedList) ?>)</dt><?php
?><dd>
<ul class="h"><?php
foreach ($this->relationBlockedList as $profileId):
?><li><a href="<?php echo $this->path ?>user/view/<?php echo $profileId ?>/"><?php
echo $profileId;
?></a></li><?php
endforeach;
?></ul>
</dd><?php
endif;
if (!empty($this->blockingUserList)):
?><dt>Blocking others (<?php echo count($this->blockingUserList) ?>)</dt><?php
?><dd>
<ul class="h"><?php
foreach ($this->blockingUserList as $profileId):
?><li><a href="<?php echo $this->path ?>user/view/<?php echo $profileId ?>/"><?php
echo $profileId;
?></a></li><?php
endforeach;
?></ul>
</dd><?php
endif;
echoField(
'deleteReason',
$this->deleteReason);
?></dl><?php
if (!empty($this->pictureWarningStates)):
?><h3>Picture Warnings</h3>
<dl><?php
foreach ($this->pictureWarningStates as $state => $count):
?><dt><?php echo $state; ?></dt>
<dd><?php echo $count; ?></dd><?php
endforeach;
?></dl><?php
endif;
if (!empty($this->activationTokens)):
?><h3>ActivationTokens</h3>
<ul class="list"><?php
foreach ($this->activationTokens as $activationToken):
$url = $this->path . 'account/activate/' . urlencode($activationToken->profileId) . '/' . urlencode($activationToken->token) . '/'
?><li><a href="<?php echo $url ?>"><?php
echo $activationToken->token
?></a> (<?php echo $activationToken->createdAt ?> / <?php echo $activationToken->usedAt ?>)</li><?php
endforeach;
?></ul>
<a href="<?php echo $this->path ?>mod/createactivationtoken/<?php echo $this->profile->id ?>/">Create new token</a><?php
endif;
?></div>
</section>
<section class="collapsed">
<h2>Settings</h2>
<div>
<dl><?php
$settingConstants = \vc\helper\ConstantHelper::getConstants(
'vc\object\Settings',
array('SAVEDSEARCH_DISPLAY_', 'DISTANCE_UNIT_')
);
foreach ($this->settings->values as $settingKey => $settingValue):
if (array_key_exists($settingKey, $settingConstants)) {
echoField(
strtolower(str_replace('_', ' ', $settingConstants[$settingKey])),
$settingValue);
} else {
echoField(
'DEPRECATED ::: ' . $settingKey,
$settingValue);
}
endforeach;
?></dl>
</div>
</section>
<?php if (!empty($this->recentLogins)): ?>
<section class="collapsed">
<h2>Recent Logins</h2>
<div>
<dl><?php
foreach ($this->recentLogins as $recentLogin):
?><dt><?php
echo $recentLogin[0];
?></dt>
<dd><?php
echo '[' . $recentLogin[2] . '] ';
echo '<a href="http://whatismyipaddress.com/ip/' . $recentLogin[1] . '">' . $recentLogin[1] . '</a>';
?></dd><?php
endforeach;
?></dl>
</div>
</section>
<?php endif; ?>
<section class="collapsed">
<h2>Action :: Change Birthdate</h2>
<div>
<form action="<?php echo $this->path ?>mod/user/birthday/" method="post" accept-charset="UTF-8">
<input type="hidden" name="profileid" value="<?php echo $this->profile->id ?>" />
<ul>
<li class="mandatory">
<label for="birthday">New birthday</label>
<div class="datepicker<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>">
<input id="birthday"
name="birthday"
data-date-start="<?php echo floor((strtotime('1920-01-01 00:00:00') - time()) / 86400) ?>"
data-date-end="<?php echo floor((strtotime(\vc\config\Globals::MINIMUM_BIRTH_YEAR . '-01-01 00:00:00') - time()) / 86400) ?>"
type="text" /><label class="icon" for="birthday"></label>
</div>
</li>,
<li>
<button class="submit" type="submit">Save</button>
</li>
</ul>
</form>
</div>
</section>
<section class="collapsed">
<h2>Action :: Give Away Plus</h2>
<div>
<form action="<?php echo $this->path ?>mod/user/giftplus/" method="post" accept-charset="UTF-8">
<input type="hidden" name="profileid" value="<?php echo $this->profile->id ?>" />
<ul>
<li>
<label for="amount">Month</label>
<input id="amount" name="amount" pattern="[0-9]+" maxlength="3" type="text" />
</li>
<li>
<button class="submit" type="submit">Save</button>
</li>
</ul>
</form>
</div>
</section>
<section class="collapsed">
<h2>Action :: Add Watchlist</h2>
<div>
<form action="<?php echo $this->path ?>mod/watchlist/add/" method="post" accept-charset="UTF-8">
<input type="hidden" name="profileid" value="<?php echo $this->profile->id ?>" />
<ul>
<li>
<input id="undesirable" name="undesirable" type="checkbox" value="1" />
<label for="undesirable">Undesirable</label>
</li>
<li>
<button class="submit" type="submit">Save</button>
</li>
</ul>
</form>
</div>
</section>
<section class="collapsed">
<h2>Action :: Block user</h2>
<div>
<form action="<?php echo $this->path ?>mod/user/block/" method="post" accept-charset="UTF-8">
<input type="hidden" name="profileid" value="<?php echo $this->profile->id ?>" />
<ul>
<li>
<label for="days">Days</label>
<input id="days" name="days" pattern="[0-9]+" maxlength="3" type="text" />
</li>
<li>
<label for="blockReason">Reason</label>
<input id="blockReason" name="reason" maxlength="250" type="text" />
<label class="help">Internally stored reason for the block. Won't be communicated to the user.</label>
</li>
<li>
<button class="submit" type="submit">Save</button>
</li>
</ul>
</form>
</div>
</section>
<section class="collapsed">
<h2>Action :: Delete user</h2>
<div>
<form action="<?php echo $this->path ?>mod/user/delete/" method="post" accept-charset="UTF-8">
<input type="hidden" name="profileid" value="<?php echo $this->profile->id ?>" />
<ul>
<li>
<label for="status">New status</label>
<dd><select id="status" name="status">
<option value="1">1 - active</option>
<option value="0">0 - wait for unlock</option>
<option value="-1">-1 - deleted by user</option>
<option value="-21">-21 - spam</option>
<option value="-22">-22 - e-mail not found</option>
<option value="-23">-23 - deprecated (no 'recent' update)</option>
<option value="-24">-24 - suspect (no crime yet)</option>
<option value="-25">-25 - inbox overflown</option>
<option value="-26">-26 - double entries</option>
<option value="-50">-50 - sexist</option>
<option value="-51">-51 - racist</option>
<option value="-52">-52 - specisist</option>
<option value="-53">-53 - insult</option>
<option value="-54">-54 - misuse of site</option>
<option value="-55">-55 - stalker</option>
<option value="-56">-56 - commercial</option>
<option value="-57">-57 - complete fake</option>
<option value="-100">-100 - unlock timed out</option>
<option value="-101">-101 - Widerruf nach §13 TMG</option>
</select>
</li>
<li>
<label for="deleteReason">Delete reason</label>
<textarea id="deleteReason" name="reason"></textarea>
</li>
<li>
<input id="markimages" name="markimages" type="checkbox" value="1" />
<label for="markimages">Mark images</label>
</li>
<li>
<input id="deletemessages" name="deletemessages" type="checkbox" value="1" />
<label for="deletemessages">Delete messages</label>
</li>
<li>
<input id="deleteforum" name="deleteforum" type="checkbox" value="1" <?php
if($this->forumThreadCount > 0 || $this->forumThreadCommentCount > 0):
echo ' checked="checked"';
endif;
?> />
<label for="deleteforum">Delete forum threads/comments</label>
</li>
<li>
<button class="submit" type="submit">Save</button>
</li>
</ul>
</form>
</div>
</section>
</div><?php
endif;
function echoField($caption, $value, $link = false, $emphasis = false)
{
if (!empty($value) && $value != ""):
?><dt><?php echo $caption?></dt>
<dd><?php
if ($link):
$url = prepareURL($value);
if ($url === FALSE):
echo prepareHTML($value);
else:
?><a target="_blank" href="<?php echo $url ?>" rel="nofollow"><?php echo prepareHTML($value) ?></a><?php
endif;
else:
if ($emphasis):
?><em><?php
endif;
echo prepareHTML($value);
if ($emphasis):
?></em><?php
endif;
endif;
?></dd><?php
endif;
}
function echoListField($caption, $captionArray, $values)
{
if (!empty($values)):
?><dt><?php echo $caption ?></dt>
<dd><ul class="list"><?php
foreach ($captionArray as $fieldKey => $fieldValue):
if (in_array($fieldKey, $values)):
?><li><?php echo $fieldValue ?></li><?php
endif;
endforeach;
?></ul></dd><?php
endif;
}
