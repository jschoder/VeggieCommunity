<?php
$tabs = array(
array('path' => 'help/faq',
'caption' => gettext('help.tab.faq')),
array('path' => 'help/support',
'caption' => gettext('help.tab.feedback')),
);
echo $this->element('tabs.links',
array('tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));
