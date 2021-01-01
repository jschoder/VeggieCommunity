<?php if (empty($this->notifications)):
?><li><p><?php echo gettext('notification.groups.empty') ?></p></li><?php
endif;
?><li class="all">
<a class="groups" href="<?php echo $this->path ?>groups/"><?php echo gettext('notification.groups.all') ?></a>
</li>
