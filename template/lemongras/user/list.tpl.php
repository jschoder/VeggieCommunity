<ul class="userlisting clearfix"><?php
for($i=0; $i<count($this->profiles); $i++)
{
$profile = $this->profiles[$i];
$picture = $this->pictures[$profile->id];
$toolTipText = $profile->getToolTipText();
?><li<?php if ((($i + 1) % 3) == 0) { echo(' class="break"');} ?>>
<a href="<?php echo $this->path ?>user/view/<?php echo $profile->id?>/" class="image"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture,
'width' => 70,
'height' => 70,
'title' => $toolTipText));
if (in_array($profile->id, $this->usersOnline))
{
?><span class="online"><?php echo gettext('profile.isonline.short')?></span><?php
}
?></a>
<a href="<?php echo $this->path ?>user/view/<?php echo $profile->id ?>/" class="link"><?php echo prepareHTML($profile->nickname, false) ?></a>
</li><?php
}
?></ul>
