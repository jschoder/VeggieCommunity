<?php if (!empty($this->profiles)): ?>
<aside id="blockUserOnline" class="block collapsible">
<header><h3><?php echo gettext('block.user.online.title')?></h3></header>
<div>
<ul class="followed thumblist"><?php
foreach ($this->profiles as $profile) {
$link = $this->path . 'user/view/' . $profile->id . '/';
?><li>
<a href="<?php echo $link ?>"><?php
if (in_array($profile->id, $this->usersOnline)):
?><span class="online" title="<?php echo gettext('profile.isonline') ?>"></span><?php
endif;
echo $this->element(
'picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$profile->id],
'title' => $profile->getToolTipText(true)));
?></a>
<a class="label" href="<?php echo $link ?>"><?php echo prepareHTML($profile->nickname) ?></a>
</li><?php
}
?></ul>
</div>
</aside>
<?php endif;