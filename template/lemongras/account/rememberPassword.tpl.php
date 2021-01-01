<h1><?php echo gettext('rememberpassword.sitetitle') ?></h1><?php
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div id="passwordlost"><?php
?><form action="<?php echo $this->path ?>account/rememberpassword/" method="post" accept-charset="UTF-8">
<p><?php echo gettext("rememberpassword.comment")?></p>
<div><?php
?><label for="email"><?php echo gettext("rememberpassword.email")?> </label><?php
?><input class="text" id="email" name="email" type="text" maxlength="70" value="<?php if(!empty($this->email)) { echo $this->email; } ?>" /> <?php
?><button type="submit"><?php echo gettext("rememberpassword.submit")?></button><?php
?></div><?php
?></form><?php
?></div>