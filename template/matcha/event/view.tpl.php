<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><div class="basics"><?php
if (empty($this->event->image)):
?><img alt="" src="/img/matcha/thumb/default-event.png"  /><?php
else:
if (!empty($_SERVER["HTTP_USER_AGENT"]) &&
(strpos("Bot", $_SERVER["HTTP_USER_AGENT"]) || strpos("Spider", $_SERVER["HTTP_USER_AGENT"]))):
?><img alt="" src="/events/picture/crop/74/74/<?php echo $this->event->image ?>" /><?php
else:
?><a class="jZoom" href="/events/picture/full/<?php echo $this->event->image ?>">
<img alt="" src="/events/picture/crop/74/74/<?php echo $this->event->image ?>" />
</a><?php
endif;
endif;
?><h1><?php echo prepareHTML($this->event->name) ?></h1>
<ul>
<li><em><?php echo prepareHTML($this->event->locationCaption) ?></em></li><?php
?><li><?php
$fromDate = strtotime($this->event->startDate);
$fromDateDay = date(gettext('event.view.header.dateformat'), $fromDate);
$toDate = strtotime($this->event->endDate);
$toDateDay = date(gettext('event.view.header.dateformat'), $toDate);
if ($fromDateDay === $toDateDay):
echo $fromDateDay . ' ' . date('H:i', $fromDate) . ' - ' . date('H:i', $toDate);
else:
echo $fromDateDay . ' ' . date('H:i', $fromDate) . ' - ' . $toDateDay . ' ' . date('H:i', $toDate);
endif;
?></li>
</ul>
</div><?php
if ($this->currentUser !== null):
?><nav class="actionBar"><?php
if ($this->currentUser->id == $this->event->createdBy):
if(strtotime($this->event->endDate) > time()):
?><a class="button secondary edit" href="<?php echo $this->path ?>events/edit/<?php echo $this->event->hashId ?>/" title="<?php echo gettext('event.view.actions.edit'); ?>"><span><?php
echo gettext('event.view.actions.edit');
?></span></a> <?php
endif;
?><a class="button secondary copy" href="<?php echo $this->path ?>events/copy/<?php echo $this->event->hashId ?>/" title="<?php echo gettext('event.view.actions.copy'); ?>"><span><?php
echo gettext('event.view.actions.copy');
?></span></a> <?php
?><form action="<?php echo $this->path ?>events/delete/" method="post">
<input type="hidden" name="id" value="<?php echo $this->event->hashId ?>" />
<button class="delete secondary" type="submit" title="<?php echo gettext('event.view.actions.delete'); ?>"><span><?php echo gettext('event.view.actions.delete') ?></span></button>
</form><?php
?><span class="divider"></span> <?php
endif;
if (empty($this->eventParticipant)):
$participateSureClass = '';
$participateLikelyClass = '';
$participateUnlikelyClass = '';
$participateNoClass = '';
$endorsingClass = '';
$bookmarkClass = '';
else:
$participateSureClass = ' secondary';
$participateLikelyClass = ' secondary';
$participateUnlikelyClass = ' secondary';
$participateNoClass = ' secondary';
$endorsingClass = ' secondary';
$bookmarkClass = ' secondary';
switch($this->eventParticipant->degree):
case vc\object\EventParticipant::STATUS_PARTICIPATING_SURE:
$participateSureClass = '';
break;
case vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY:
$participateLikelyClass = '';
break;
case vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY:
$participateUnlikelyClass = '';
break;
case vc\object\EventParticipant::STATUS_PARTICIPATING_NOT:
$participateNoClass = '';
break;
case vc\object\EventParticipant::STATUS_ENDORSING:
$endorsingClass = '';
break;
case vc\object\EventParticipant::STATUS_BOOKMARK:
$bookmarkClass = '';
break;
endswitch;
endif;
?><a class="button jParticipation participateSure<?php echo $participateSureClass ?>" href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_SURE ?>" title="<?php echo gettext('event.view.actions.participate.sure'); ?>"><span><?php
echo gettext('event.view.actions.participate.sure');
?></span></a>
<a class="button jParticipation participateLikely<?php echo $participateLikelyClass ?>" href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY ?>" title="<?php echo gettext('event.view.actions.participate.likely'); ?>"><span><?php
echo gettext('event.view.actions.participate.likely');
?></span></a>
<a class="button jParticipation participateUnlikely<?php echo $participateUnlikelyClass ?>" href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY ?>" title="<?php echo gettext('event.view.actions.participate.unlikely'); ?>"><span><?php
echo gettext('event.view.actions.participate.unlikely');
?></span></a>
<a class="button jParticipation participateNot<?php echo $participateNoClass ?>" href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_PARTICIPATING_NOT ?>" title="<?php echo gettext('event.view.actions.participate.no'); ?>"><span><?php
echo gettext('event.view.actions.participate.no');
?></span></a>
<span class="divider"></span>
<a class="button jParticipation like<?php echo $endorsingClass ?>" href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_ENDORSING ?>" title="<?php echo gettext('event.view.actions.endorsing.title') ?>"><span><?php
echo gettext('event.view.actions.endorsing');
?></span></a>
<a class="button jParticipation bookmark<?php echo $bookmarkClass ?>" href="#" data-id="<?php echo $this->event->hashId ?>" data-degree="<?php echo vc\object\EventParticipant::STATUS_BOOKMARK ?>" title="<?php echo gettext('event.view.actions.bookmark'); ?>"><span><?php
echo gettext('event.view.actions.bookmark');
?></span></a><?php
if (!empty($this->ownFriendsConfirmed)):
?> <span class="divider"></span>
<a class="button secondary invite jInvite" href="#" data-event-id="<?php echo $this->event->hashId ?>" title="<?php echo gettext('event.view.actions.invite'); ?>"><span><?php
echo gettext('event.view.actions.invite');
?></span></a><?php
endif;
?></nav><?php
endif;
echo $this->element('tabs/event',
array('path' => $this->path,
'site' => $this->site,
'event' => $this->event,
'defaultTab' => $this->defaultTab ));
?><div class="event">
<div id="eventTabInfo" <?php if ($this->defaultTab !== 'info') { ?>style="display:none"<?php } ?>>
<dl id="info">
<dt class="caption"><?php echo gettext('event.form.startDate') ?></dt>
<dd class="field"><?php echo formatLongDate(strtotime($this->event->startDate), true)
?></dd>
<dt class="caption"><?php echo gettext('event.form.endDate') ?></dt>
<dd class="field"><?php echo formatLongDate(strtotime($this->event->endDate), true)
?></dd><?php
if (!empty($this->event->locationCaption)):
?><dt class="caption"><?php echo gettext('event.form.location') ?></dt>
<dd class="field"><em><?php
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
?></em></dd><?php
endif;
?></dl><?php
if ($this->currentUser !== null):
?><nav class="ctabar" data-id="<?php echo $this->event->hashId ?>">
<select name="degree"><?php
$participationOptions = array();
$participationOptions[vc\object\EventParticipant::STATUS_PARTICIPATING_SURE] = gettext('event.view.actions.participate.sure');
$participationOptions[vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY] = gettext('event.view.actions.participate.likely');
$participationOptions[vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY] = gettext('event.view.actions.participate.unlikely');
$participationOptions[vc\object\EventParticipant::STATUS_PARTICIPATING_NOT] = gettext('event.view.actions.participate.no');
$participationOptions[vc\object\EventParticipant::STATUS_ENDORSING] = gettext('event.view.actions.endorsing');
$participationOptions[vc\object\EventParticipant::STATUS_BOOKMARK] = gettext('event.view.actions.bookmark');
if (!empty($this->eventParticipant) && array_key_exists($this->eventParticipant->degree, $participationOptions)):
$defaultParticipation = $this->eventParticipant->degree;
else:
$defaultParticipation = vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY;
endif;
foreach ($participationOptions as $key => $value):
if ($defaultParticipation === $key):
echo '<option selected="selected" value="' . $key . '">' . $value . '</option>';
else:
echo '<option value="' . $key . '">' . $value . '</option>';
endif;
endforeach;
?></select>
<a class="participateSure" href="#" target="_blank"><?php echo gettext('Bestätigen')?></a>
</nav><?php
endif;
?><dl><?php
if (!empty($this->event->description)):
?><dt class="caption"><?php echo gettext('event.form.description') ?></dt>
<dd class="field"><?php echo prepareHTML($this->event->description, true, true) ?></dd><?php
endif;
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
if (array_key_exists($this->event->categoryId, $this->eventCategories)):
$category = $this->eventCategories[$this->event->categoryId];
?><dt class="caption"><?php echo gettext('event.form.category') ?></dt>
<dd class="field eventCategory" style="border-color:#<?php echo $category['color'] ?>"><?php
echo gettext($category['title'])
?></dd><?php
endif;
?></dl><?php
$this->echoWideAd($this->locale, $this->plusLevel);
$hasParticipants = false;
foreach ($this->participants as $degree => $participants):
if (!empty($participants)):
$hasParticipants = true;
switch ($degree):
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
endswitch;
?><ul class="itembox"><?php
foreach($participants as $participant => $isHost):
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$this->participantProfiles[$participant],
'picture'=>$this->participantPictures[$participant],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline));
endforeach;
?></ul><?php
endif;
endforeach;
if ($hasParticipants):
$this->echoWideAd($this->locale, $this->plusLevel);
endif;
?></div>
<div id="eventTabDiscussion" class="jsForum" <?php if ($this->defaultTab !== 'discussion') { ?>style="display:none"<?php } ?>><?php
if ($this->currentUser !== null):
echo $this->element('forum/newThreadForm',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser' => $this->currentUser,
'ownPicture' => $this->ownPicture,
'contextType' => vc\config\EntityTypes::EVENT,
'contextId' => $this->event->hashId));
endif;
?><div class="jThreads threads"><?php
foreach ($this->threads as $thread):
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
endforeach;
?></div><?php
echo $this->element(
'pagination',
array(
'pagination' => $this->pagination
)
);
$this->echoWideAd($this->locale, $this->plusLevel);
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