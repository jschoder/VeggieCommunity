<div class="basics"><?php
if (empty($this->group->image)) {
?><img alt="" src="/img/matcha/thumb/default-group.png" ><?php
} else {
if (!empty($_SERVER["HTTP_USER_AGENT"]) &&
(strpos("Bot", $_SERVER["HTTP_USER_AGENT"]) || strpos("Spider", $_SERVER["HTTP_USER_AGENT"]))) {
?><img alt="" src="/groups/picture/crop/74/74/<?php echo $this->group->image ?>" /><?php
} else {
?><a class="jZoom" href="/groups/picture/full/<?php echo $this->group->image ?>">
<img alt="" src="/groups/picture/crop/74/74/<?php echo $this->group->image ?>" />
</a><?php
}
}
?><h1><?php echo prepareHTML($this->group->name) ?></h1>
<ul><?php
?><li><?php
if ($this->memberCount <= 1) {
echo gettext('group.detail.singlemember');
} else {
echo $this->memberCount . ' ' . gettext('group.detail.members');
}
?></li><?php
?></ul>
</div><?php
if ($this->currentUser !== null):
?><nav class="actionBar"><?php
if ($this->currentForum !== null):
if (in_array($this->currentForum->id, $this->forumSubscriptions)):
?><a class="button subscribe subscribeForum" data-forum-id="<?php echo $this->currentForum->hashId ?>" href="#" title="<?php echo gettext('group.forum.subscribe') ?>" style="display:none">
<span><?php
echo gettext('group.forum.subscribe')
?></span>
</a>
<a class="button unsubscribe unsubscribeForum" data-forum-id="<?php echo $this->currentForum->hashId ?>" href="#" title="<?php echo gettext('group.forum.unsubscribe') ?>">
<span><?php
echo gettext('group.forum.unsubscribe')
?></span>
</a> <?php
else:
?><a class="button subscribe subscribeForum" data-forum-id="<?php echo $this->currentForum->hashId ?>" href="#" title="<?php echo gettext('group.forum.subscribe') ?>">
<span><?php
echo gettext('group.forum.subscribe')
?></span>
</a>
<a class="button unsubscribe unsubscribeForum" data-forum-id="<?php echo $this->currentForum->hashId ?>" href="#" title="<?php echo gettext('group.forum.unsubscribe') ?>" style="display:none">
<span><?php
echo gettext('group.forum.unsubscribe')
?></span>
</a> <?php
endif;
/*
{
?><a class="" title="<?php echo gettext('group.forum.subscribe') ?>"  style="display:none"></a><?php
?><a class="unsubscribeForum" title="<?php echo gettext('group.forum.unsubscribe') ?>" data-forum-id="<?php echo $this->currentForum->hashId ?>"></a><?php
} else {
?><a class="subscribeForum" title="<?php echo gettext('group.forum.subscribe') ?>" data-forum-id="<?php echo $this->currentForum->hashId ?>"></a><?php
?><a class="unsubscribeForum" title="<?php echo gettext('group.forum.unsubscribe') ?>" data-forum-id="<?php echo $this->currentForum->hashId ?>" style="display:none"></a><?php
}
*/
endif;
if (in_array($this->currentUser->id, $this->groupRoles[\vc\object\GroupRole::ROLE_MODERATOR]) ||
in_array($this->currentUser->id, $this->groupRoles[\vc\object\GroupRole::ROLE_ADMIN])):
?><a class="button event" href="<?php echo $this->path ?>events/add/group/<?php echo $this->group->hashId ?>/" title="<?php echo gettext('group.detail.addevent') ?>">
<span><?php
echo gettext('group.detail.addevent');
?></span>
</a> <?php
endif;
// You can only invite somebody if you have friends to invite
if (!empty($this->ownFriendsConfirmed)):
?><a class="button share jInvite" href="#" data-group-id="<?php echo $this->group->hashId ?>" title="<?php echo gettext('group.detail.invite') ?>">
<span><?php
echo gettext('group.detail.invite');
?></span>
</a> <?php
endif;
?><a class="button secondary flag" href="<?php echo $this->path ?>help/support/reportgroup/<?php echo $this->group->hashId ?>/" title="<?php echo gettext('group.detail.reportgroup') ?>">
<span><?php
echo gettext('group.detail.reportgroup');
?></span>
</a> <?php
if (in_array($this->currentUser->id, $this->groupRoles[\vc\object\GroupRole::ROLE_ADMIN])):
?><a class="button secondary delete jDeleteGroup" href="#" title="<?php echo gettext('group.detail.deletegroup') ?>">
<span><?php
echo gettext('group.detail.deletegroup')
?></span>
</a>
<div id="deleteGroupDialog" style="display:none">
<form action="<?php echo $this->path ?>groups/delete/<?php echo $this->group->hashId ?>/" method="post">
<p><?php echo gettext('group.detail.deletegroup.dialog.text') ?></p>
<button class="cancel secondary"><?php echo gettext('group.detail.deletegroup.dialog.cancel') ?></button>
<button class="delete" type="submit"><?php echo gettext('group.detail.deletegroup.dialog.confirm') ?></button>
</form>
</div><?php
endif;
if ($this->isConfirmedMember):
?><form action="<?php echo $this->path ?>groups/leave/" class="jLeave" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button class="leave" type="submit"> <?php echo gettext('group.detail.leave') ?></button>
</form><?php
elseif ($this->isMemberWaitingForConfirmation):
?><form action="<?php echo $this->path ?>groups/leave/" class="jCancel" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button class="cancel" type="submit"> <?php echo gettext('group.detail.cancelRequest') ?></button>
</form><?php
else:
?><form action="<?php echo $this->path ?>groups/join/" class="jJoin" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button class="join" type="submit"> <?php echo gettext('group.detail.join') ?></button>
</form><?php
endif;
?></nav><?php
endif;