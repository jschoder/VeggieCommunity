<?php
$tabs = array();
$tabs[] = array('path' => 'events/view/' . $this->event->hashId,
'caption' => gettext('event.view.tab.info'),
'tabId' => 'eventTabInfo',
'active' => $this->defaultTab == 'info');
$tabs[] = array('path' => 'events/view/' . $this->event->hashId . '/discussion',
'caption' => gettext('event.view.tab.discussion'),
'tabId' => 'eventTabDiscussion',
'active' => $this->defaultTab == 'discussion');
echo $this->element('tabs.js',
array('class' => 'tabs',
'tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));