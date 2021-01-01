<ul class="userlisting clearfix"><?php
foreach ($this->visitors as $i => $visitor) {
$visitor = $this->visitors[$i];
if (is_object($visitor)) {
$url = $this->path . 'user/view/' . $visitor->id . '/';
?><li<?php if ((($i + 1) % 3) == 0) { echo(' class="break"');} ?>><?php
?><a href="<?php echo $url?>" class="image"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$visitor->id],
'width' => 60,
'height' => 60,
'title' => $this->prepareDate(gettext("mysite.visitors.dateformat"), $visitor->lastVisit)));
if (in_array($visitor->id, $this->usersOnline)) {
?><span class="online"><?php echo gettext('profile.isonline.short')?></span><?php
}
?></a><?php
?><a href="<?php echo $url?>" class="link"><?php echo prepareHTML($visitor->nickname) ?></a><?php
?></li><?php
}
}
?></ul>