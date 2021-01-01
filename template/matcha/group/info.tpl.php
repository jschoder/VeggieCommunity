<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
echo $this->element('groups/header',
array('path' => $this->path,
'currentUser' => $this->currentUser,
'group' => $this->group,
'currentForum' => null,
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
?><div><?php
if ($this->currentUser !== null &&
count($this->invitations) > 0):
?><h2><?php echo gettext('group.info.invitations') ?></h2>
<div class="invitations"><?php
foreach($this->invitations as $invitation):
?><div><?php
if ($invitation['profileRealMarker'] && $invitation['profilePlusMarker']):
$class = 'class="real plus" ';
elseif ($invitation['profileRealMarker']):
$class = 'class="real" ';
elseif ($invitation['profilePlusMarker']):
$class = 'class="plus" ';
else:
$class = '';
endif;
echo sprintf(
gettext('group.info.invitation'),
'<a ' . $class . 'href="' . $this->path . 'user/view/' . $invitation['profileId'] . '/">' .
$invitation['profileNickname'] .
'</a>',
'<span class="jAgo" data-ts="' . strtotime($invitation['createdAt']) . '"></span>'
);
if (!empty($invitation['comment'])):
?><p><?php echo prepareHTML($invitation['comment']) ?></p><?php
endif;
?></div><?php
endforeach;
?><form class="join" action="<?php echo $this->path ?>groups/join/" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button type="submit"><?php echo gettext('group.detail.join') ?></button>
</form>
<form class="join" action="<?php echo $this->path ?>groups/invitation/ignore/" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button type="submit" class="secondary"><?php echo gettext('group.info.invitation.ignore') ?></button>
</form>
</div><?php
endif;
?><h2><?php echo gettext('group.info.description') ?></h2>
<p><?php echo prepareHTML($this->group->description) ?></p>
<?php if (!empty($this->group->rules)): ?>
<h2><?php echo gettext('group.info.rules') ?></h2>
<p><?php echo prepareHTML($this->group->rules) ?></p>
<?php endif; ?>
<h2><?php echo gettext('group.info.language') ?></h2>
<p><?php echo gettext('language.' . $this->group->language) ?></p><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?><h2><?php echo gettext('group.info.members') ?></h2>
<div id="groupMembers">
<a id="members"></a><?php
if (isset($this->unconfirmedMembers)):
?><h3><?php echo gettext('group.members.unconfirmed'); ?></h3><?php
?><ul id="unconfirmed-members" class="jUnconfirmedMembers list buttons"><?php
foreach ($this->unconfirmedMembers as $unconfirmedMember):
$class = array();
if ($unconfirmedMember->realMarker):
$class[] = 'real';
endif;
if ($unconfirmedMember->plusMarker):
$class[] = 'plus';
endif;
?><li id="unconfirmed-member-<?php echo $unconfirmedMember->id ?>">
<a <?php  if(!empty($class)) { echo 'class="' . implode(' ', $class) . '" '; } ?>href="<?php echo $this->path ?>user/view/<?php echo $unconfirmedMember->id ?>/"><?php
echo prepareHtml($unconfirmedMember->nickname)
?></a>
<button data-action="accept" data-group-id="<?php echo $this->group->hashId ?>" data-user-id="<?php echo $unconfirmedMember->id ?>"><?php echo gettext('group.members.unconfirmed.accept'); ?></button>
<button class="secondary" data-action="deny" data-group-id="<?php echo $this->group->hashId ?>" data-user-id="<?php echo $unconfirmedMember->id ?>"><?php echo gettext('group.members.unconfirmed.deny'); ?></button>
<button class="secondary" data-action="block" data-group-id="<?php echo $this->group->hashId ?>" data-user-id="<?php echo $unconfirmedMember->id ?>"><?php echo gettext('group.members.unconfirmed.block'); ?></button>
</li><?php
endforeach;
?></ul><?php
endif;
if ($this->currentUser !== null):
$admins = array();
$moderators = array();
for($i=0; $i<count($this->members); $i++):
$member = $this->members[$i];
$class = array();
if ($member->realMarker):
$class[] = 'real';
endif;
if ($member->plusMarker):
$class[] = 'plus';
endif;
$classAttribute = empty($class) ? '' : 'class="' . implode(' ', $class) . '" ';
if (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_ADMIN])):
$admins[] = '<a ' . $classAttribute . 'href="' . $this->path . 'user/view/' . $member->id . '/">' .
prepareHtml($member->nickname) . '</a>';
endif;
if (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_MODERATOR])):
$moderators[] = '<a ' . $classAttribute . 'href="' . $this->path . 'user/view/' . $member->id . '/">' .
prepareHtml($member->nickname) . '</a>';
endif;
endfor;
?><h3><?php echo gettext('group.members.admins'); ?></h3>
<p><?php
echo implode(', ', $admins);
?></p><?php
if (!empty($moderators)):
?><h3><?php echo gettext('group.members.moderators'); ?></h3>
<p><?php
echo implode(', ', $moderators);
?></p><?php
endif;
endif;
if ($this->currentUser === null ||
(
$this->group->memberVisibility == \vc\object\Group::MEMBER_VISBILITY_MEMBER_ONLY &&
!$this->isConfirmedMember
)):
?><div class="notifyInfo"><?php echo gettext('group.members.onlyVisibleToRegistered') ?></div><?php
else:
?><h3><?php
echo gettext('group.members.allmembers');
if ($this->currentUser !== null):
if ($this->group->memberVisibility == vc\object\Group::MEMBER_VISBILITY_MEMBER_ONLY):
$iconTitle = gettext('group.visibility.member.member');
$iconClass = 'member';
elseif ($this->group->memberVisibility == vc\object\Group::MEMBER_VISBILITY_SITE_MEMBERS):
$iconTitle = gettext('group.visibility.member.registered');
$iconClass = 'registered';
else:
$iconTitle = '';
$iconClass = null;
endif;
?><sup class="<?php echo $iconClass ?>" title="<?php echo $iconTitle ?>"><span><?php echo $iconTitle ?></span></sup><?php
endif;
?></h3>
<ul class="itembox jMembers"><?php
for($i=0; $i<count($this->members); $i++):
$member = $this->members[$i];
$actions = array();
if ($this->groupRole !== null):
if ($this->currentUser->id != $member->id):
$actions[] = sprintf('<a class="remove" href="#"  data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.remove'));
$actions[] = sprintf('<a class="ban" href="#"  data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.ban'));
endif;
if ($this->groupRole === \vc\object\GroupRole::ROLE_ADMIN):
if (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_ADMIN])):
$actions[] = sprintf('<a class="adminRemove" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.admin.remove'));
elseif (in_array($member->id, $this->groupRoles[\vc\object\GroupRole::ROLE_MODERATOR])):
$actions[] = sprintf('<a class="modRemove" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.moderator.remove'));
$actions[] = sprintf('<a class="adminAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.admin.add'));
else:
$actions[] = sprintf('<a class="modAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.moderator.add'));
$actions[] = sprintf('<a class="adminAdd" href="#" data-group-id="%s" data-user-id="%d">%s</a>',
$this->group->hashId,
$member->id,
gettext('group.members.action.admin.add'));
endif;
endif;
endif;
echo $this->element('profilebox',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
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
'usersOnline' => $this->usersOnline,
'actions'=>$actions));
endfor;
?></ul><?php
endif;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?></div><?php
$this->addScript(
'vc.groups.info.init();'
);