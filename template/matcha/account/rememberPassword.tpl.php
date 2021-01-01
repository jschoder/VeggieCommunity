<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('rememberpassword.sitetitle') ?></h1>
<div class="notifyInfo"><?php
echo gettext('rememberpassword.comment')
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
?><form action="<?php echo $this->path ?>account/rememberpassword/" method="post" accept-charset="UTF-8">
<ul>
<li>
<label for="email"><?php echo gettext("rememberpassword.email")?> </label>
<input id="email" name="email" type="text" maxlength="70" value="<?php if(!empty($this->email)) { echo $this->email; } ?>" />
</li>
<li>
<button type="submit"><?php echo gettext("rememberpassword.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</li>
</ul>
</form><?php
$this->echoWideAd($this->locale, $this->plusLevel);
