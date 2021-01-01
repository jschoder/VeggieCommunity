<ul class="userlisting clearfix"><?php
foreach ($this->profiles as $i => $profile) {
$url = $this->path . 'user/view/' . $profile->id . '/';
?><li<?php if ((($i + 1) % 3) == 0) { echo(' class="break"');} ?>><?php
?><a href="<?php echo $url ?>" class="image"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$profile->id],
'width' => 60,
'height' => 60,
'title' => $profile->getToolTipText()));
if (in_array($profile->id, $this->usersOnline)) {
?><span class="online"><?php echo gettext('profile.isonline.short')?></span><?php
}
?></a><?php
?><a href="<?php echo $url?>" class="link"><?php echo $profile->nickname?></a><?php
?></li><?php
}
?></ul>