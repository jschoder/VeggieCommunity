<?php
if (array_key_exists($this->event->categoryId, $this->eventCategories)):
$category = $this->eventCategories[$this->event->categoryId];
else:
$category = $this->eventCategories[100];
endif;
?><li class="<?php echo $category['class'] ?>"><?php
$url = $this->path . 'events/view/' . $this->event->hashId . '/';
if (empty($this->event->image)):
$imageUrl = '/img/matcha/thumb/default-event.png';
else:
$imageUrl = '/events/picture/crop/74/74/' . $this->event->image;
endif;
?><h3>
<a href="<?php echo $url ?>" title="<?php echo prepareHTML($this->event->name) ?> (<?php echo gettext($category['title']) ?>)">
<img alt="" src="<?php echo $imageUrl ?>" /><?php
echo prepareHTML($this->event->name)
?></a>
</h3>
<ul class="list">
<li><?php
echo formatLongDate(strtotime($this->event->startDate), true);
?></li><?php
if (isset($this->invitor)):
?><li><?php
$class = array();
if ($this->invitor->realMarker):
$class[] = 'real';
endif;
if ($this->invitor->plusMarker):
$class[] = 'plus';
endif;
echo sprintf(
gettext('events.myevents.invitedBy'),
'<a ' .
(!empty($class) ? 'class="' . implode(' ', $class) . '" ' : '') .
'href="' . $this->path . 'user/view/' . $this->invitor->id . '/">' .
prepareHTML($this->invitor->nickname) .
'</a>'
);
?></li><?php
else:
switch ($this->eventParticipant->degree):
case \vc\object\EventParticipant::STATUS_PARTICIPATING_SURE:
echo '<li>' . gettext('event.degree.participatingSure') . '</li>';
break;
case \vc\object\EventParticipant::STATUS_PARTICIPATING_LIKELY:
echo '<li>' . gettext('event.degree.participatingLikely') . '</li>';
break;
case \vc\object\EventParticipant::STATUS_PARTICIPATING_UNLIKELY:
echo '<li>' . gettext('event.degree.participatingUnlikely') . '</li>';
break;
endswitch;
endif;
?></ul>
</li>