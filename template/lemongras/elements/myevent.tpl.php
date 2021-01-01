<?php
if (array_key_exists($this->event->categoryId, $this->eventCategories)) {
$category = $this->eventCategories[$this->event->categoryId];
} else {
$category = $this->eventCategories[100];
}
?><li style="border-color:#<?php echo $category['color'] ?>" class="clearfix"><?php
$url = $this->path . 'events/view/' . $this->event->hashId . '/';
if (empty($this->event->image)) {
$imageUrl = '/img/lemongras/default-event.png';
} else {
$imageUrl = '/events/picture/crop/74/74/' . $this->event->image;
}
?><a class="img" href="<?php echo $url ?>">
<img src="<?php echo $imageUrl ?>" width="50" height="50" />
</a>
<div>
<a href="<?php echo $url ?>"><?php
echo prepareHTML($this->event->name);
?></a> (<?php echo gettext($category['title']) ?>)
</div>
<div><?php
echo formatLongDate(strtotime($this->event->startDate), true);
?></div><?php
if (isset($this->invitor)) {
?><div><?php
echo sprintf(
gettext('events.myevents.invitedBy'),
'<a ' .
($this->invitor->plusMarker ? 'class="plus" ' : '') .
'href="' . $this->path . 'user/view/' . $this->invitor->id . '/">' .
prepareHTML($this->invitor->nickname) .
'</a>'
);
?></div><?php
} else {
switch ($this->eventParticipant->degree) {
case \vc\object\EventParticipant::STATUS_PARTICIPATING_SURE:
echo '<div>' . gettext('event.degree.participatingSure') . '</div>';
break;
case \vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY:
echo '<div>' . gettext('event.degree.participatingLikely') . '</div>';
break;
case \vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY:
echo '<div>' . gettext('event.degree.participatingUnlikely') . '</div>';
break;
}
}
?></li>