<?php if (empty($this->notifications)):
?><li><p><?php echo gettext('notification.pm.empty') ?></p></li><?php
endif;
?><li class="all">
<a class="pm" href="<?php echo $this->path ?>pm/"><?php echo gettext('notification.pm.all') ?></a>
</li>
