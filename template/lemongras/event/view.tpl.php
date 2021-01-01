<?php
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div id="eventBasics" class="clearfix">
<div id="basics" class="clearfix"><?php
if (empty($this->event->image)) {
?><img src="/img/lemongras/default-event.png" width="50" height="50" /><?php
} else {
if (!empty($_SERVER["HTTP_USER_AGENT"]) &&
(strpos("Bot", $_SERVER["HTTP_USER_AGENT"]) || strpos("Spider", $_SERVER["HTTP_USER_AGENT"]))) {
?><img src="/events/picture/crop/74/74/<?php echo $this->event->image ?>" width="50" height="50" /><?php
} else {
?><a class="jZoom" href="/events/picture/full/<?php echo $this->event->image ?>">
<img src="/events/picture/crop/74/74/<?php echo $this->event->image ?>" width="50" height="50" />
</a><?php
}
}
?><div>
<div>
<strong><?php echo prepareHTML($this->event->name) ?></strong>
</div>
<ul>
<li><?php echo prepareHTML($this->event->locationCaption) ?></li>
<li><?php
$fromDate = strtotime($this->event->startDate);
$fromDateDay = date(gettext('event.view.header.dateformat'), $fromDate);
$toDate = strtotime($this->event->endDate);
$toDateDay = date(gettext('event.view.header.dateformat'), $toDate);
if ($fromDateDay === $toDateDay) {
echo $fromDateDay . ' ' . date('H:i', $fromDate) . ' - ' . date('H:i', $toDate);
} else {
echo $fromDateDay . ' ' . date('H:i', $fromDate) . ' - ' . $toDateDay . ' ' . date('H:i', $toDate);
}
?></li>
</ul>
</div>
</div><!-- #basics -->
</div><!-- #eventBasics --><?php
if ($this->currentUser !== null) {
?><div class="eventActions"><?php
if ($this->currentUser->id == $this->event->createdBy):
if(strtotime($this->event->endDate) > time()):
?><a href="<?php echo $this->path ?>events/edit/<?php echo $this->event->hashId ?>/" class="fa fa-pencil"><?php
echo gettext('event.view.actions.edit.short');
?></a><?php
endif;
?><a href="<?php echo $this->path ?>events/copy/<?php echo $this->event->hashId ?>/" class="fa fa-copy"><?php
echo gettext('event.view.actions.copy.short');
?></a><?php
?><form action="<?php echo $this->path ?>events/delete/" method="post" class="inlineform">
<input type="hidden" name="id" value="<?php echo $this->event->hashId ?>" />
<a class="delete fa fa-delete jSubmit" href="#"><?php echo gettext('event.view.actions.delete.short') ?></a>
</form><?php
endif;
$participateClass = '';
$participateSureClass = '';
$participateLikelyClass = '';
$participateUnlikelyClass = '';
$participateNoClass = '';
$endorsingClass = '';
$bookmarkClass = '';
if (!empty($this->eventParticipant)) {
switch($this->eventParticipant->degree) {
case vc\object\EventParticipant::STATUS_PARTICIPATING_SURE:
$participateClass = ' marked';
$participateSureClass = ' marked';
break;
case vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY:
$participateClass = ' marked';
$participateLikelyClass = ' marked';
break;
case vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY:
$participateClass = ' marked';
$participateUnlikelyClass = ' marked';
break;
case vc\object\EventParticipant::STATUS_PARTICIPATING_NOT:
$participateClass = ' marked';
$participateNoClass = ' marked';
break;
case vc\object\EventParticipant::STATUS_ENDORSING:
$endorsingClass = ' marked';
break;
case vc\object\EventParticipant::STATUS_BOOKMARK:
$bookmarkClass = ' marked';
break;
}
}
?><div class="context">
<a class="show-participate fa fa-participate-likely<?php echo $participateClass ?>"><?php echo gettext('event.view.actions.participate'); ?></a>
<div class="context-menu jThreadContextMenu">
<ul>
<li><a href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_SURE ?>" class="jParticipation fa fa-participate-sure<?php echo $participateSureClass ?>"><?php
echo gettext('event.view.actions.participate.sure');
?></a></li>
<li><a href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY ?>" class="jParticipation fa fa-participate-likely<?php echo $participateLikelyClass ?>"><?php
echo gettext('event.view.actions.participate.likely');
?></a></li>
<li><a href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY ?>" class="jParticipation fa fa-participate-unlikely<?php echo $participateUnlikelyClass ?>"><?php
echo gettext('event.view.actions.participate.unlikely');
?></a></li>
<li><a href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_NOT ?>" class="jParticipation fa fa-participate-not<?php echo $participateNoClass ?>"><?php
echo gettext('event.view.actions.participate.no');
?></a></li>
</ul>
</div>
</div>
<a href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_ENDORSING ?>" class="jParticipation fa fa-thumbs-o-up<?php echo $endorsingClass ?>" title="<?php echo gettext('event.view.actions.endorsing.title') ?>"><?php
echo gettext('event.view.actions.endorsing');
?></a>
<a href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_BOOKMARK ?>" class="jParticipation fa fa-bookmark bookmark<?php echo $bookmarkClass ?>"><?php
echo gettext('event.view.actions.bookmark');
?></a><?php
if (!empty($this->ownFriendsConfirmed)) {
?><a href="#" data-event-id="<?php echo $this->event->hashId ?>" class="fa fa-user-plus jInvite"><?php
echo gettext('event.view.actions.invite');
?></a><?php
}
?></div><?php
}
echo $this->element('tabs/event',
array('path' => $this->path,
'site' => $this->site,
'event' => $this->event,
'defaultTab' => $this->defaultTab ));
?>
<div class="event">
<div id="eventTabInfo" <?php if ($this->defaultTab !== 'info') { ?>style="display:none"<?php } ?>>
<dl id="info" class="clearfix">
<dt class="caption"><?php echo gettext('event.form.startDate') ?></dt>
<dd class="field"><?php echo formatLongDate(strtotime($this->event->startDate), true)
?></dd>
<dt class="caption"><?php echo gettext('event.form.endDate') ?></dt>
<dd class="field"><?php echo formatLongDate(strtotime($this->event->endDate), true)
?></dd><?php
if (!empty($this->event->locationCaption)) {
?><dt class="caption"><?php echo gettext('event.form.location') ?></dt>
<dd class="field"><?php
echo prepareHTML($this->event->locationCaption);
if (!empty($this->event->locationStreet)) {
echo ', ' . prepareHTML($this->event->locationStreet);
}
if (!empty($this->event->locationPostal) ||
!empty($this->event->locationCity)) {
echo ', ';
if (!empty($this->event->locationPostal)) {
echo prepareHTML($this->event->locationPostal) . ' ';
}
echo prepareHTML($this->event->locationCity);
}
if (!empty($this->locationRegionName)) {
echo ', ' . prepareHTML($this->locationRegionName);
}
if (!empty($this->locationCountryName)) {
echo ', ' . prepareHTML($this->locationCountryName);
}
?></dd><?php
}
if (!empty($this->event->description)) {
?><dt class="caption"><?php echo gettext('event.form.description') ?></dt>
<dd class="field"><?php echo prepareHTML($this->event->description, true, true) ?></dd><?php
}
if (!empty($this->event->url)):
?><dt class="caption"><?php echo gettext('event.form.url') ?></dt>
<dd class="field"><?php
$url = prepareURL($this->event->url);
if ($url === FALSE):
echo prepareHTML($this->event->url);
else:
?><a target="_blank" href="<?php echo $url ?>" rel="nofollow"><?php echo prepareHTML($this->event->url) ?></a><?php
endif;
?></dd><?php
endif;
if (!empty($this->event->fbUrl)):
?><dt class="caption"><?php echo gettext('event.form.fbUrl') ?></dt>
<dd class="field"><?php
$url = prepareURL($this->event->fbUrl);
if ($url === FALSE):
echo prepareHTML($this->event->fbUrl);
else:
?><a target="_blank" href="<?php echo $url ?>" rel="nofollow"><?php echo prepareHTML($this->event->fbUrl) ?></a><?php
endif;
?></dd><?php
endif;
if (array_key_exists($this->event->categoryId, $this->eventCategories)) {
$category = $this->eventCategories[$this->event->categoryId];
?><dt class="caption"><?php echo gettext('event.form.category') ?></dt>
<dd class="field eventCategory" style="border-color:#<?php echo $category['color'] ?>"><?php
echo gettext($category['title'])
?></dd><?php
}
?></dl><?php
foreach ($this->participants as $degree => $participants) {
if (!empty($participants)) {
switch ($degree) {
case \vc\object\EventParticipant::STATUS_PARTICIPATING_SURE:
?><h3><?php echo gettext('event.view.participation.sure'); ?> (<?php echo count($participants) ?>)</h3><?php
break;
case \vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY:
?><h3><?php echo gettext('event.view.participation.likely'); ?> (<?php echo count($participants) ?>)</h3><?php
break;
case \vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY:
?><h3><?php echo gettext('event.view.participation.unlikely'); ?> (<?php echo count($participants) ?>)</h3><?php
break;
case \vc\object\EventParticipant::STATUS_ENDORSING:
?><h3><?php echo gettext('event.view.participation.supporting'); ?> (<?php echo count($participants) ?>)</h3><?php
break;
}
?><ul id="userList" class="members clearfix"><?php
foreach($participants as $participant => $isHost) {
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$this->participantProfiles[$participant],
'picture'=>$this->participantPictures[$participant],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked));
}
?></ul><?php
}
}
?></div>
<div id="eventTabDiscussion" class="jsForum" <?php if ($this->defaultTab !== 'discussion') { ?>style="display:none"<?php } ?>><?php
if ($this->currentUser !== null) {
echo $this->element('forum/newThreadForm',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser' => $this->currentUser,
'ownPicture' => $this->ownPicture,
'contextType' => vc\config\EntityTypes::GROUP_FORUM,
'contextId' => $this->event->hashId));
}
?><div class="jThreads threads"><?php
foreach ($this->threads as $thread) {
echo $this->element('forum/thread',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'displayAuthors' => $this->displayAuthors,
'thread' => $thread,
'currentUser' => $this->currentUser,
'isLikable' => $this->isLikable,
'canPostThread' => $this->canPostThread,
'canPostComment' => $this->canPostComment,
'ownPicture' => $this->ownPicture,
'pictures' => $this->pictures,
'profiles' => $this->profiles,
'actions' => $this->threadActions[$thread->id],
'commentActions' => $this->commentActions,
'likes' => $this->likes,
'flags' => $this->flags));
}
?></div><?php
echo $this->element(
'pagination',
array(
'imagesPath' => $this->imagesPath,
'pagination' => $this->pagination
)
);
?></div>
</div><?php
$this->addScript(
'vc.events.init();' .
'vc.forum.init(' .
\vc\config\EntityTypes::EVENT . ',' .
'\'' . $this->event->hashId . '\',' .
$this->page . ',' .
($this->currentUser === null ? 'false' : 'true') . ',' .
(empty($this->lastUpdateTimestamp) ? '0' : $this->lastUpdateTimestamp) .
');'
);
echo $this->element('forumTemplates');