<?php
$tabs = array(
array('path' => 'events',
'caption' => gettext('events.tab.myevents')),
array('path' => 'events/calendar',
'caption' => gettext('events.tab.calendar')),
array('path' => 'events/add',
'caption' => gettext('events.tab.add')),
);
echo $this->element('tabs.links',
array('class' => 'majorTabs',
'tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));