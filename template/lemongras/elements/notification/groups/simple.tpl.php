<li<?php if($this->seenAt === null) { echo ' class="new"'; } ?>>
<a href="<?php echo $this->link ?>"><?php
if (empty($this->groupImage)):
?><img width="50" height="50" alt="" src="<?php echo $this->imagesPath ?>thumb/default-group.png" /><?php
else:
?><img width="50" height="50" alt="" src="/groups/picture/crop/74/74/<?php echo $this->groupImage ?>" /><?php
endif;
?><p><?php
echo $this->message;
?></p>
<span class="jAgo" data-ts="<?php echo strtotime($this->lastUpdate) ?>"></span>
</a>
</li>