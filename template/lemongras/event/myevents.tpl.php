<?php
echo $this->element('tabs/events',
array('path' => $this->path,
'site' => $this->site,));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div id="myevents">
<div class="mycalendar">
<h2><?php echo gettext('events.myevents.myCalendar') ?></h2><?php
if (empty($this->myCalendar)) {
?><p><?php echo gettext('events.myevents.myCalendar.empty') ?></p><?php
} else {
?><ul><?php
foreach ($this->myCalendar as $event) {
echo $this->element(
'myevent',
array(
'path' => $this->path,
'eventCategories' => $this->eventCategories,
'event' => $event['event'],
'eventParticipant' => $event['participation']
)
);
}
?></ul><?php
}
?></div>
<div class="secondary"><?php
if (!empty($this->invitations)) {
?><h2><?php echo gettext('events.myevents.invitations') ?></h2>
<ul><?php
foreach ($this->invitations as $event) {
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
}
?></ul><?php
}
if (!empty($this->bookmarks)) {
?><h2><?php echo gettext('events.myevents.myBookmarks') ?></h2>
<ul><?php
foreach ($this->bookmarks as $event) {
echo $this->element(
'myevent',
array(
'path' => $this->path,
'eventCategories' => $this->eventCategories,
'event' => $event['event'],
'eventParticipant' => $event['participation']
)
);
}
?></ul><?php
}
if (!empty($this->endorsements)) {
?><h2><?php echo gettext('events.myevents.myEndorsements') ?></h2>
<ul><?php
foreach ($this->endorsements as $event) {
echo $this->element(
'myevent',
array(
'path' => $this->path,
'eventCategories' => $this->eventCategories,
'event' => $event['event'],
'eventParticipant' => $event['participation']
)
);
}
?></ul><?php
}
?></div>
</div>