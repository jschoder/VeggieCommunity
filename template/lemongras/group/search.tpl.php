<?php
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
echo $this->element('tabs/groups',
array('path' => $this->path,
'site' => $this->site,));
?>
<div>
<form action="<?php echo $this->path ?>groups/search/" method="get" accept-charset="UTF-8">
<input class="text" id="searchtext" name="s" type="text" value="<?php echo $this->searchPhrase ?>" maxlength="255"/>
<select name="sort">
<option value="new"<?php if ($this->searchSort === 'new') { echo ' selected="selected"'; } ?>><?php echo gettext('groups.search.sort.new') ?></option>
<option value="active"<?php if ($this->searchSort === 'active') { echo ' selected="selected"'; } ?>><?php echo gettext('groups.search.sort.active') ?></option>
<option value="members"<?php if ($this->searchSort === 'members') { echo ' selected="selected"'; } ?>><?php echo gettext('groups.search.sort.members') ?></option>
</select>
<button class="submit" type="submit"><?php echo gettext('groups.search.submit') ?></button>
</form>
</div>
<?php if (empty($this->groups)) { ?>
<div class="notifyInfo"><?php echo gettext('groups.search.empty') ?></div>
<?php } else { ?>
<ul id="groupList" class="groups clearfix">
<?php foreach ($this->groups as $group) { ?>
<li class="groupBox clearfix">
<a href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/" class="image"><?php
if (empty($group->image)) {
?><img src="/img/lemongras/default-group.png" width="70" height="70" /><?php
} else {
?><img src="/groups/picture/crop/74/74/<?php echo $group->image ?>" width="70" height="70" /><?php
}
?></a>
<div class="groupinfo">
<a class="groupname" href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/"><?php echo prepareHTML($group->name) ?></a><br />
<?php if ($group->activity == \vc\object\Group::ACTIVITY_VERY_LOW) { ?>
<span class="activity xs" title="<?php echo gettext('group.activity.xs') ?>"></span>
<?php } elseif ($group->activity == \vc\object\Group::ACTIVITY_LOW) { ?>
<span class="activity s" title="<?php echo gettext('group.activity.s') ?>"></span>
<?php } elseif ($group->activity == \vc\object\Group::ACTIVITY_AVERAGE) { ?>
<span class="activity m" title="<?php echo gettext('group.activity.m') ?>"></span>
<?php } elseif ($group->activity == \vc\object\Group::ACTIVITY_HIGH) { ?>
<span class="activity l" title="<?php echo gettext('group.activity.l') ?>"></span>
<?php } elseif ($group->activity == \vc\object\Group::ACTIVITY_VERY_HIGH) { ?>
<span class="activity xl" title="<?php echo gettext('group.activity.xl') ?>"></span>
<?php } ?>
<?php if ($group->members <= 1) {
echo gettext('group.detail.singlemember');
} else {
echo $group->members . ' ' . gettext('group.detail.members');
} ?>
</div>
</li>
<?php } ?>
</ul><?php
echo $this->element(
'pagination',
array(
'imagesPath' => $this->imagesPath,
'pagination' => $this->pagination
)
);
}
