<ul class="followed thumblist"><?php
foreach ($this->profiles as $i => $profile):
$url = $this->path . 'user/view/' . $profile->id . '/';
?><li><?php
?><a href="<?php echo $url?>"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$profile->id],
'title' => $profile->getToolTipText()));
?></a><a href="<?php echo $url?>" class="label"><?php echo prepareHTML($profile->nickname) ?></a><?php
?></li><?php
endforeach;
?></ul>