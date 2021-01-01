<div id="groupBasics" class="clearfix">
<div id="basics" class="clearfix"><?php
if (empty($this->group->image)) {
?><img src="/img/lemongras/default-group.png" width="50" height="50" /><?php
} else {
if (!empty($_SERVER["HTTP_USER_AGENT"]) &&
(strpos("Bot", $_SERVER["HTTP_USER_AGENT"]) || strpos("Spider", $_SERVER["HTTP_USER_AGENT"]))) {
?><img src="/groups/picture/crop/74/74/<?php echo $this->group->image ?>" width="50" height="50" /><?php
} else {
?><a class="jZoom" href="/groups/picture/full/<?php echo $this->group->image ?>">
<img src="/groups/picture/crop/74/74/<?php echo $this->group->image ?>" width="50" height="50" />
</a><?php
}
}
?><div>
<div>
<strong><?php echo prepareHTML($this->group->name) ?></strong>
</div>
<ul>
<li><?php
if ($this->memberCount <= 1) {
echo gettext('group.detail.singlemember');
} else {
echo $this->memberCount . ' ' . gettext('group.detail.members');
}
?></li>
</ul>
</div>
</div><!-- #basics -->
<div id="options" class="clearfix">
<?php if ($this->currentUser !== null) {
if ($this->isConfirmedMember) { ?>
<form class="jLeave" action="<?php echo $this->path ?>groups/leave/" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button type="submit"><?php echo gettext('group.detail.leave') ?></button>
</form>
<?php } elseif ($this->isMemberWaitingForConfirmation) { ?>
<form class="jCancel" action="<?php echo $this->path ?>groups/leave/" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button type="submit"><?php echo gettext('group.detail.cancelRequest') ?></button>
</form>
<?php } else { ?>
<form class="jJoin" action="<?php echo $this->path ?>groups/join/" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->hashId ?>" />
<button type="submit"><?php echo gettext('group.detail.join') ?></button>
</form>
<?php }
}
if ($this->currentUser !== null) {
?><div class="context">
<a class="show-context">...</a>
<div class="context-menu jThreadContextMenu">
<ul><?php
if (!empty($this->ownFriendsConfirmed)) {
?><li><a href="#" data-group-id="<?php echo $this->group->hashId ?>" class="invite jInvite"><?php
echo gettext('group.detail.invite');
?></a></li><?php
}
?><li><a href="<?php echo $this->path ?>help/support/reportgroup/<?php echo $this->group->hashId ?>/" class="flag"><?php
echo gettext('group.detail.reportgroup');
?></a></li><?php
if (in_array($this->currentUser->id, $this->groupRoles[\vc\object\GroupRole::ROLE_ADMIN])) {
?><li><a href="#" class="delete jDeleteGroup"><?php
echo gettext('group.detail.deletegroup');
?></a></li>
<div id="deleteGroupDialog" style="display:none">
<form action="<?php echo $this->path ?>groups/delete/<?php echo $this->group->hashId ?>/" method="post">
<p><?php echo gettext('group.detail.deletegroup.dialog.text') ?></p>
<button class="cancel secondary"><?php echo gettext('group.detail.deletegroup.dialog.cancel') ?></button>
<button type="submit"><?php echo gettext('group.detail.deletegroup.dialog.confirm') ?></button>
</form>
</div><?php
}
?></ul>
</div>
</div><?php
}
?></div>
</div><!-- #groupBasics -->