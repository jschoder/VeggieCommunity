<?php
$tabs = array(
array(
'path' => 'mysite',
'caption' => gettext('mysite.tab.general')
)
);
if ($this->site == 'friend/list' || !empty($this->ownFriendsConfirmed)):
$tabs[] = array(
'path' => 'friend/list',
'caption' => gettext('mysite.tab.friends') . (empty($this->ownFriendsConfirmed) ? '' : ' (' . count($this->ownFriendsConfirmed) . ')')
);
endif;
if ($this->site == 'favorite/list' || !empty($this->ownFavorites)):
$tabs[] = array(
'path' => 'favorite/list',
'caption' => gettext('mysite.tab.favorites') . (empty($this->ownFavorites) ? '' : ' (' . count($this->ownFavorites) . ')')
);
endif;
if (count($tabs) > 1):
echo $this->element('tabs.links',
array('class' => 'majorTabs',
'tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));
endif;
