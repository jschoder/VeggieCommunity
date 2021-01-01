<h1><?php echo gettext("menu.tellafriend"); ?></h1>
<?php
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
} elseif (!empty($this->errorMessage)) {
?><div class="notifyError"><?php echo $this->errorMessage ?></div><?php
}
?><form action="<?php echo $this->path ?>tellafriend/" method="post" accept-charset="UTF-8">
<div id="tellafriend">
<p class="infotext"><?php echo gettext("tellafriend.infotext")?></p>
<div class="form">
<dl id="tellafriend-sender" class="clearfix">
<dt class="caption"><label for="sendername"><?php echo gettext("tellafriend.yourname")?> </label></dt>
<dd class="field"><input id="sendername" class="text180" type="text" name="sendername" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderName) ?>" /></dd>
<dt class="caption"><label for="senderemail"><?php echo gettext("tellafriend.youremail")?> </label></dt>
<dd class="field"><input id="senderemail" class="text180" type="text" name="senderemail" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderEmail) ?>" /></dd>
<dt class="caption"><label for="subject"><?php echo gettext("tellafriend.subject")?> </label></dt>
<dd class="field"><input id="subject" class="text180" type="text" name="subject" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSubject) ?>" /></dd>
</dl>
<p><label for="reciever1"><?php echo gettext("tellafriend.otheremails")?> </label></p>
<ul id="tellafriend-reciever" class="clearfix">
<li><input id="reciever1" class="text130" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever1?>" /></li>
<li><input id="reciever2" class="text130" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever2?>" /></li>
<li><input id="reciever3" class="text130" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever3?>" /></li>
<li><input id="reciever4" class="text130" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever4?>" /></li>
<li><input id="reciever5" class="text130" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever5?>" /></li>
<li><input id="reciever6" class="text130" type="text" name="reciever[]" maxlength="70" value="<?php echo $this->defaultReciever6?>"/></li>
</ul>
<p><label for="message"><?php echo gettext("tellafriend.message")?> </label></p>
<textarea id="message" class="text jAutoHeight" name="message" rows="3"><?php echo $this->defaultMessage?></textarea>
<div class="buttons">
<button type="reset"><?php echo gettext('tellafriend.reset') ?></button>
<button type="submit"><?php echo gettext('tellafriend.confirm') ?></button>
</div>
</div>
</div>
</form>