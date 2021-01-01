<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext("password.sitetitle") ?></h1>
<div class="notifyInfo"><?php 
echo gettext('password.allowedcharacters') . ' ' . htmlentities(implode(' ', $this->allowedSpecialCharacters)) 
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
$changeByToken = !empty($this->profileId) && !empty($this->token);
?><form action="<?php echo $this->path ?>account/changepassword/" method="post"><?php
if ($changeByToken):
?><input name="profile_id" value="<?php echo $this->profileId?>" type="hidden" />
<input name="token" value="<?php echo $this->token?>" type="hidden" /><?php
endif;
?><ul><?php 
if (!$changeByToken && $this->hasOldPassword):
?><li>
<label for="old"><?php echo gettext("password.old")?></label>
<input id="old" name="old" type="password" maxlength="100" /><?php
if (!empty($this->oldPasswordMessage)) {
?><label class="error" for="old"><?php echo $this->oldPasswordMessage?></label><?php
}
?></li><?php
endif;
?><li>
<label for="new1"><?php echo gettext("password.new1")?></label>
<input id="new1" name="new1" type="password" maxlength="100"/><?php
if (!empty($this->newPasswordMessage)) {
?><label for="new1" class="error"><?php echo $this->newPasswordMessage?></label><?php
}
?></li>
<li>
<label for="new2"><?php echo gettext("password.new2")?></label>
<input id="new2" name="new2" type="password" maxlength="100"/>
</li>
<li>
<button type="submit"><?php echo gettext('password.submit') ?></button>
<?php if(isset($this->referer)): 
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php 
endif; ?>
</li>
</ul>
</form><?php
$this->echoWideAd($this->locale, $this->plusLevel);
