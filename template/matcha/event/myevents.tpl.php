<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('events.myevents.title') ?></h1><?php
echo $this->element('tabs/events',
array('path' => $this->path,
'site' => $this->site,));
?><div class="wideCol">
<div>
<h2><?php echo gettext('events.myevents.myCalendar') ?></h2><?php
if (empty($this->myCalendar)):
?><div class="notifyInfo"><?php echo gettext('events.myevents.myCalendar.empty') ?></div><?php
else:
?><ul class="events"><?php
foreach ($this->myCalendar as $event):
echo $this->element(
'myevent',
array(
'path' => $this->path,
'eventCategories' => $this->eventCategories,
'event' => $event['event'],
'eventParticipant' => $event['participation']
)
);
endforeach;
?></ul><?php
endif;
?></div>
<div><?php
if (!empty($this->invitations)):
?><h2><?php echo gettext('events.myevents.invitations') ?></h2>
<ul class="events"><?php
foreach ($this->invitations as $event):
echo $this->element(
'myevent',
array(
'path' => $this->path,
'eventCategories' => $this->eventCategories,
'event' => $event['event'],
'eventParticipant' => $event['participation'],
'invitor' => $event['invitor']
)
);
endforeach;
?></ul><?php
endif;
if (!empty($this->bookmarks)):
?><h2><?php echo gettext('events.myevents.myBookmarks') ?></h2>
<ul class="events"><?php
foreach ($this->bookmarks as $event):
echo $this->element(
'myevent',
array(
'path' => $this->path,
'eventCategories' => $this->eventCategories,
'event' => $event['event'],
'eventParticipant' => $event['participation']
)
);
endforeach;
?></ul><?php
endif;
if (!empty($this->endorsements)):
?><h2><?php echo gettext('events.myevents.myEndorsements') ?></h2>
<ul class="events"><?php
foreach ($this->endorsements as $event):
echo $this->element(
'myevent',
array(
'path' => $this->path,
'eventCategories' => $this->eventCategories,
'event' => $event['event'],
'eventParticipant' => $event['participation']
)
);
endforeach;
?></ul><?php
endif;
?></div>
</div><?php
$this->echoWideAd($this->locale, $this->plusLevel);