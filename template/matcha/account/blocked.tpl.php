<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('blocked.title')?></h1><?php
$this->echoWideAd($this->locale, $this->plusLevel);
if (empty($this->blockedUsers)):
?><div class="notifyInfo"><?php
echo gettext('blocked.empty')
?></div><?php
else:
?><form accept-charset="UTF-8" action="<?php echo $this->path ?>account/blocked/" method="post">
<ul>
<li>
<div class="notifyInfo"><?php
echo gettext('blocked.infotext')
?></div>
</li><?php
foreach ($this->blockedUsers as $userId => $blockDate):
?><li>
<input id="<?php echo $userId ?>" type="checkbox" name="unblock[]" value="<?php echo $userId ?>" />
<label for="<?php echo $userId ?>"><?php
echo $this->blockedProfiles[$userId] ?>
<span class="secondary"><?php echo $blockDate ?></span>
</label>
</li><?php
endforeach;
?><li>
<button type="submit"><?php echo gettext('blocked.submit')?></button>
</li>
</ul>
</form><?php
endif;
$this->echoWideAd($this->locale, $this->plusLevel);