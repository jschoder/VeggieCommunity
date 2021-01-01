<?php
$tabs = array(
array('path' => 'user/edit',
'caption' => gettext('account.tab.editUser')),
array('path' => 'user/pictures',
'caption' => gettext('account.tab.editPictures')),
array('path' => 'user/matching',
'caption' => gettext('account.tab.matching')),
array('path' => 'account/settings',
'caption' => gettext('account.tab.settings')),
);
echo $this->element('tabs.links',
array('tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));