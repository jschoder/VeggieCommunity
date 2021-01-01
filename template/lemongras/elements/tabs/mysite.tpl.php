<?php
$tabs = array(
array('path' => 'mysite',
'caption' => gettext('mysite.tab.general')),
array('path' => 'friend/list',
'caption' => gettext('mysite.tab.friends') . (empty($this->ownFriendsConfirmed) ? '' : ' (' . count($this->ownFriendsConfirmed) . ')')),
array('path' => 'favorite/list',
'caption' => gettext('mysite.tab.favorites') . (empty($this->ownFavorites) ? '' : ' (' . count($this->ownFavorites) . ')')),
);
echo $this->element('tabs.links',
array('tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));