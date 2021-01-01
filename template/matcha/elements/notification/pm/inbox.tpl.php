<li<?php if($this->isNew) { echo ' class="new"'; } ?>>
<a href="<?php echo $this->path ?>pm/#<?php echo $this->contact['id'] ?>"><?php
if (empty($this->contact['picture'])):
if ($this->contact['isActive'] && !empty($this->contact['gender'])):
?><img alt="" class="user" src="<?php echo $this->imagesPath ?>thumb/default-thumb-<?php echo $this->contact['gender'] ?>.png"  /><?php
else:
?><img alt="" class="user" src="<?php echo $this->imagesPath ?>thumb/default-thumb-deleted.png"  /><?php
endif;
else:
?><img alt="" src="/user/picture/crop/74/74/<?php echo $this->contact['picture'] ?>" alt=""><?php
endif;
?><strong><?php echo prepareHTML($this->contact['nickname']); ?></strong>
<p><?php echo $this->message ?></p>
</a>
</li>