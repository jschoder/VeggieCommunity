<?php
$tabs = array();
$tabs[] = array('path' => 'groups/info/' . $this->group->hashId,
'caption' => gettext('group.tab.info'));
foreach ($this->forums as $forum) {
$path = 'groups/forum/' . $this->group->hashId;
if ($forum->isMain === false) {
$path .= '/' . $forum->hashId;
}
if ($forum->contentVisibility == vc\object\GroupForum::CONTENT_VISIBILITY_MEMBER) {
$title = gettext('group.visibility.forum.member');
$iconClass = 'forumMember';
} else if ($forum->contentVisibility == vc\object\GroupForum::CONTENT_VISIBILITY_REGISTERED) {
$title = gettext('group.visibility.forum.registered');
$iconClass = 'forumRegistered';
} else if ($forum->contentVisibility == vc\object\GroupForum::CONTENT_VISIBILITY_PUBLIC) {
$title = gettext('group.visibility.forum.public');
$iconClass = 'forumPublic';
} else {
$title = '';
$iconClass = null;
}
$tabs[] = array('path' => $path,
'caption' => $forum->name,
'title' => $title,
'iconClass' => $iconClass);
}
if ($this->groupRole == \vc\object\GroupRole::ROLE_ADMIN) {
$tabs[] = array('path' => 'groups/settings/' . $this->group->hashId,
'caption' => gettext('group.tab.settings'));
}
echo $this->element('tabs.links',
array('tabs' => $tabs,
'path' => $this->path,
'site' => $this->site,
'siteParams' => $this->siteParams));