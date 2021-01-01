<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('groups.search.title') ?></h1><?php
echo $this->element('tabs/groups',
array('path' => $this->path,
'site' => $this->site));
?><div>
<form accept-charset="UTF-8" action="<?php echo $this->path ?>groups/search/" class="formHighlight horizontal" method="get">
<ul>
<li><input id="searchtext" name="s" placeholder="<?php echo gettext('groups.search.placeholder') ?>" title="<?php echo gettext('groups.search.placeholder') ?>" type="text" value="<?php echo $this->searchPhrase ?>" maxlength="255"/></li>
<li><select name="sort">
<option value="new"<?php if ($this->searchSort === 'new') { echo ' selected="selected"'; } ?>><?php echo gettext('groups.search.sort.new') ?></option>
<option value="active"<?php if ($this->searchSort === 'active') { echo ' selected="selected"'; } ?>><?php echo gettext('groups.search.sort.active') ?></option>
<option value="members"<?php if ($this->searchSort === 'members') { echo ' selected="selected"'; } ?>><?php echo gettext('groups.search.sort.members') ?></option>
</select></li>
<li><button class="submit" type="submit"><?php echo gettext('groups.search.submit') ?></button></li>
</ul>
</form>
</div><?php
if (empty($this->groups)) :
?><div class="notifyInfo"><?php echo gettext('groups.search.empty') ?></div><?php
else:
?><ul class="itembox"><?php
foreach ($this->groups as $group):
?><li>
<a href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/"><?php
if (empty($group->image)):
?><img alt="" src="/img/matcha/thumb/default-group.png" width="70" height="70" /><?php
else:
?><img alt="" src="/groups/picture/crop/74/74/<?php echo $group->image ?>" width="70" height="70" /><?php
endif;
?></a>
<div>
<a href="<?php echo $this->path ?>groups/info/<?php echo $group->hashId ?>/"><?php echo prepareHTML($group->name) ?></a>
<ul class="h">
<li><?php
if ($group->activity == \vc\object\Group::ACTIVITY_VERY_LOW):
?><span class="activity xs" title="<?php echo gettext('group.activity.xs') ?>"><span></span></span><?php
elseif ($group->activity == \vc\object\Group::ACTIVITY_LOW):
?><span class="activity s" title="<?php echo gettext('group.activity.s') ?>"><span></span></span><?php
elseif ($group->activity == \vc\object\Group::ACTIVITY_AVERAGE):
?><span class="activity m" title="<?php echo gettext('group.activity.m') ?>"><span></span></span><?php
elseif ($group->activity == \vc\object\Group::ACTIVITY_HIGH):
?><span class="activity l" title="<?php echo gettext('group.activity.l') ?>"><span></span></span><?php
elseif ($group->activity == \vc\object\Group::ACTIVITY_VERY_HIGH):
?><span class="activity xl" title="<?php echo gettext('group.activity.xl') ?>"><span></span></span><?php
endif;
?></li>
<li><?php
if ($group->members <= 1):
echo gettext('group.detail.singlemember');
else:
echo $group->members . ' ' . gettext('group.detail.members');
endif;
?></li>
</ul>
</div>
</li>
<?php endforeach; ?>
</ul><?php
echo $this->element(
'pagination',
array(
'pagination' => $this->pagination
)
);
endif;
$this->echoWideAd($this->locale, $this->plusLevel);