<?php if (!empty($this->groups)): ?>
<aside id="<?php echo $this->id ?>" class="block collapsible">
<header><h3><?php echo $this->title ?></h3></header>
<div>
<ul class="followed thumblist"><?php
foreach ($this->groups as $group):
$link = $this->path . 'groups/info/' . $group->hashId . '/';
?><li title="<?php echo prepareHTML($group->name) ?>">
<a href="<?php echo $link ?>"><?php
if (empty($group->image)):
?><img alt="" src="/img/matcha/thumb/default-group.png" ><?php
else:
?><img alt="" src="/groups/picture/crop/74/74/<?php echo $group->image ?>" /><?php
endif;
?></a>
<a class="label" href="<?php echo $link ?>"><?php echo prepareHTML($group->name) ?></a>
</li><?php
endforeach;
?></ul>
<footer class="actions">
<a class="details" href="<?php echo $this->path ?>groups/search/"><?php echo gettext('block.extendedResult') ?></a>
</footer>
</div>
</aside>
<?php endif;