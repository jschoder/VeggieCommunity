<?php if (empty($this->notifications)):
?><li><p><?php echo gettext('notification.friends.empty') ?></p></li><?php
endif;
?><li class="all">
<a class="friends" href="<?php echo $this->path ?>friend/list/"><?php echo gettext('notification.friends.all') ?></a>
</li>
