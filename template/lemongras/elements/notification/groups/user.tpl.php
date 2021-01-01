<li<?php if($this->seenAt === null) { echo ' class="new"'; } ?>>
<a href="<?php echo $this->link ?>"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->picture,
'title' => null,
'width' => 50,
'height' => 50));
?><p><?php
echo $this->message;
?></p>
<span class="jAgo" data-ts="<?php echo strtotime($this->lastUpdate) ?>"></span>
</a>
</li>
