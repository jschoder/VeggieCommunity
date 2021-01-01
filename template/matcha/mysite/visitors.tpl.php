<ul class="followed thumblist"><?php
foreach ($this->visitors as $i => $visitor):
$visitor = $this->visitors[$i];
if (is_object($visitor)):
$url = $this->path . 'user/view/' . $visitor->id . '/';
?><li><?php
?><a href="<?php echo $url?>"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$visitor->id],
'title' => $this->prepareDate(gettext("mysite.visitors.dateformat"), $visitor->lastVisit)));
?></a><?php
?><a class="label" href="<?php echo $url?>"><?php echo prepareHTML($visitor->nickname) ?></a><?php
?></li><?php
endif;
endforeach;
?></ul>