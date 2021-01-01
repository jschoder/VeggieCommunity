<?php
$tabs = array();
$tabs[] = array('path' => 'user/view/' . $this->profile->id,
'caption' => gettext('profile.tab.info'),
'tabId' => 'profileTabInfo',
'active' => $this->defaultTab == 'info');
if (count($this->profilePictures) > 0) {
$tabs[] = array('path' => 'user/view/' . $this->profile->id . '/album',
'caption' => gettext('profile.tab.album'),
'tabId' => 'profileTabAlbum',
'active' => $this->defaultTab == 'album');
}
if (!empty($this->publicGroups) || !empty($this->sharedGroups)) {
$tabs[] = array('path' => 'user/view/' . $this->profile->id . '/groups',
'caption' => gettext('profile.tab.groups'),
'tabId' => 'profileTabGroups',
'active' => $this->defaultTab == 'groups');
}
if (count($this->friends) > 0) {
$tabs[] = array('path' => 'user/view/' . $this->profile->id . '/friends',
'caption' => gettext('profile.tab.friends') . ' (' . count($this->friends) . ')',
'tabId' => 'profileTabFriends',
'active' => $this->defaultTab == 'friends');
}
if ($this->isAdmin) {
$tabs[] = array('path' => 'user/view/' . $this->profile->id . '/mod',
'caption' => 'Mod',
'tabId' => 'profileTabMod',
'active' => $this->defaultTab == 'mod');
}
echo $this->element('tabs.js',
array('class' => 'tabs',
'tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));