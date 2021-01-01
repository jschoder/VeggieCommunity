<?php
$tabs = array(
array('path' => 'groups',
'caption' => gettext('groups.tab.mygroups')),
array('path' => 'groups/search',
'caption' => gettext('groups.tab.search')),
array('path' => 'groups/add',
'caption' => gettext('groups.tab.create')),
);
echo $this->element('tabs.links',
array('class' => 'majorTabs',
'tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));