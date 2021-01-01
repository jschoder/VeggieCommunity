<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext("menu.tellafriend"); ?></h1><?php
if (empty($this->errorMessage)):
?><div class="notifyInfo"><?php echo gettext('tellafriend.infotext') ?></div><?php
else:
?><div class="notifyError"><?php echo $this->errorMessage ?></div><?php
endif;
?><form action="<?php echo $this->path ?>tellafriend/" method="post" accept-charset="UTF-8">
<ul>
<li>
<label for="sendername"><?php echo gettext("tellafriend.yourname")?> </label>
<input id="sendername" type="text" name="sendername" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderName) ?>" />
</li>
<li>
<label for="senderemail"><?php echo gettext("tellafriend.youremail")?> </label>
<input id="senderemail" type="text" name="senderemail" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderEmail) ?>" />
</li>
<li>
<label for="subject"><?php echo gettext("tellafriend.subject")?> </label>
<input id="subject" type="text" name="subject" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSubject) ?>" />
</li>
<li>
<label for="reciever1"><?php echo gettext("tellafriend.otheremails")?> </label>
<input id="reciever1" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever1?>" />
<input id="reciever2" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever2?>" />
<input id="reciever3" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever3?>" />
<input id="reciever4" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever4?>" />
<input id="reciever5" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever5?>" />
<input id="reciever6" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever6?>" />
</li>
<li>
<label for="message"><?php echo gettext("tellafriend.message")?> </label>
<textarea id="message" class="jAutoHeight" name="message" rows="3"><?php echo $this->defaultMessage?></textarea>
</li>
<li>
<button type="submit"><?php echo gettext('tellafriend.confirm') ?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</li>
</ul>
</form><?php
$this->echoWideAd($this->locale, $this->plusLevel);