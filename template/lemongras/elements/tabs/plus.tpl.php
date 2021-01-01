<?php
$tabs = array(
array('path' => 'plus',
'caption' => gettext('plus.tab.features')),
array('path' => 'plus/book',
'caption' => gettext('plus.tab.book')),
array('path' => 'plus/history',
'caption' => gettext('plus.tab.history')),
);
echo $this->element('tabs.links',
array('tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));