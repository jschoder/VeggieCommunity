<?php
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
echo $this->element('groups/header',
array('path' => $this->path,
'currentUser' => $this->currentUser,
'group' => $this->group,
'groupRoles' => $this->groupRoles,
'memberCount' => $this->memberCount,
'isConfirmedMember' => $this->isConfirmedMember,
'isMemberWaitingForConfirmation' => $this->isMemberWaitingForConfirmation,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed));
echo $this->element('tabs/group',
array('path' => $this->path,
'site' => $this->site,
'siteParams' => $this->siteParams,
'currentUser' => $this->currentUser,
'groupRole' => $this->groupRole,
'group' => $this->group,
'forums' => $this->forums));
?><div id="groupInfo"><?php
if ($this->currentUser !== null &&
count($this->invitations) > 0) {
?><h2><?php echo gettext('group.info.invitations') ?></h2>
<div class="invitations clearfix"><?php
foreach($this->invitations as $invitation) {
?><div><?php
$class = $invitation['profilePlusMarker'] ? 'class="plus" ' : '';
echo sprintf(
gettext('group.info.invitation'),
'<a ' . $class . 'href="' . $this->path . 'user/view/' . $invitation['profileId'] . '/">' .
$invitation['profileNickname'] .
'</a>',
'<span class="jAgo" data-ts="' . strtotime($invitation['createdAt']) . '"></span>'
);
if (!empty($invitation['comment'])) {
?><p><?php echo prepareHTML($invitation['comment']) ?></p><?php
}
?></div><?php
}
?><form class="join" action="<?php echo $this->path ?>groups/join/" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button type="submit"><?php echo gettext('group.detail.join') ?></button>
</form>
<form class="join" action="<?php echo $this->path ?>groups/invitation/ignore/" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button type="submit" class="secondary"><?php echo gettext('group.info.invitation.ignore') ?></button>
</form>
</div><?php
}
?><h2><?php echo gettext('group.info.description') ?></h2>
<p><?php echo prepareHTML($this->group->description) ?></p>
<?php if (!empty($this->group->rules)) { ?>
<h2><?php echo gettext('group.info.rules') ?></h2>
<p><?php echo prepareHTML($this->group->rules) ?></p>
<?php } ?>
<h2><?php echo gettext('group.info.language') ?></h2>
<p><?php echo gettext('language.' . $this->group->language) ?></p>
<?php
if ($this->group->memberVisibility == vc\object\Group::MEMBER_VISBILITY_MEMBER_ONLY) {
$title = gettext('group.visibility.member.member');
$iconClass = 'membersMember';
} else if ($this->group->memberVisibility == vc\object\Group::MEMBER_VISBILITY_SITE_MEMBERS) {
$title = gettext('group.visibility.member.registered');
$iconClass = 'membersRegistered';
} else {
$title = '';
$iconClass = null;
}
?>
<h2 title="<?php echo $title ?>" class="<?php echo $iconClass ?>"><?php echo gettext('group.info.members') ?></h2>
<div id="groupMembers">
<a id="members"></a><?php
if (isset($this->unconfirmedMembers)) {
?><h3><?php echo gettext('group.members.unconfirmed'); ?></h3><?php
?><ul id="unconfirmed-members" class="jUnconfirmedMembers"><?php
foreach ($this->unconfirmedMembers as $unconfirmedMember) {
$class = $unconfirmedMember->plusMarker ? 'class="plus" ' : '';
?><li id="unconfirmed-member-<?php echo $unconfirmedMember->id ?>">
<a <?php if ($unconfirmedMember->plusMarker) { echo ' class="plus"'; } ?>href="<?php echo $this->path ?>user/view/<?php echo $unconfirmedMember->id ?>/"><?php
echo prepareHtml($unconfirmedMember->nickname)
?></a>
<button data-action="accept" data-group-id="<?php echo $this->group->hashId ?>" data-user-id="<?php echo $unconfirmedMember->id ?>"><?php echo gettext('group.members.unconfirmed.accept'); ?></button>
<button class="secondary" data-action="deny" data-group-id="<?php echo $this->group->hashId ?>" data-user-id="<?php echo $unconfirmedMember->id ?>"><?php echo gettext('group.members.unconfirmed.deny'); ?></button>
<button class="secondary" data-action="block" data-group-id="<?php echo $this->group->hashId ?>" data-user-id="<?php echo $unconfirmedMember->id ?>"><?php echo gettext('group.members.unconfirmed.block'); ?></button>
</li><?php
}
?></ul><?php
}
if ($this->currentUser !== null) {
$admins = array();
$moderators = array();
for($i=0; $i<count($this->members); $i++) {
$member = $this->members[$i];
$class = $member->plusMarker ? 'class="plus" ' : '';
if (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_ADMIN])) {
$admins[] = '<a ' . $class . 'href="' . $this->path . 'user/view/' . $member->id . '/">' .
prepareHtml($member->nickname) . '</a>';
}
if (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_MODERATOR])) {
$moderators[] = '<a ' . $class . 'href="' . $this->path . 'user/view/' . $member->id . '/">' .
prepareHtml($member->nickname) . '</a>';
}
}
?><h3><?php echo gettext('group.members.admins'); ?></h3>
<div class="userquicklist"><?php
echo implode(', ', $admins);
?></div><?php
if (!empty($moderators)) {
?><h3><?php echo gettext('group.members.moderators'); ?></h3>
<div class="userquicklist"><?php
echo implode(', ', $moderators);
?></div><?php
}
}
if ($this->currentUser === null ||
(
$this->group->memberVisibility == \vc\object\Group::MEMBER_VISBILITY_MEMBER_ONLY &&
!$this->isConfirmedMember
)) {
?><div class="notifyInfo"><?php echo gettext('group.members.onlyVisibleToRegistered') ?></div><?php
} else {
?><h3><?php echo gettext('group.members.allmembers'); ?></h3>
<ul id="userList" class="jMembers members clearfix"><?php
for($i=0; $i<count($this->members); $i++) {
$member = $this->members[$i];
$actions = array();
if ($this->groupRole !== null) {
if ($this->currentUser->id != $member->id) {
$actions[] = sprintf('<a class="remove" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.remove'));
$actions[] = sprintf('<a class="ban" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.ban'));
}
if ($this->groupRole === \vc\object\GroupRole::ROLE_ADMIN) {
if (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_ADMIN])) {
$actions[] = sprintf('<a class="adminRemove" href="#"  data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.admin.remove'));
} elseif (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_MODERATOR])) {
$actions[] = sprintf('<a class="modRemove" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.moderator.remove'));
$actions[] = sprintf('<a class="adminAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.admin.add'));
} else {
$actions[] = sprintf('<a class="modAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.moderator.add'));
$actions[] = sprintf('<a class="adminAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.admin.add'));
}
}
}
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'id'=>'groupMemberProfilebox' . $member->id,
'profile'=>$member,
'picture'=>$this->pictures[$member->id],
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked,
'actions'=>$actions));
}
?></ul><?php
}
?></div>
</div><?php
$this->addScript(
'vc.groups.info.init();'
);