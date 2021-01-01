<?php
if (empty($this->profiles)):
?><li><?php echo gettext('like.empty') ?></li><?php
else:
foreach ($this->profiles as $profileId => $profileNickname):
?><li>
<a href="<?php echo $this->path ?>user/view/<?php echo $profileId ?>/"><?php
echo prepareHTML($profileNickname);
?></a>
</li><?php
endforeach;
endif;
