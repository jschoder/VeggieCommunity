<h1><?php echo gettext("password.sitetitle") ?></h1>
<div class="notifyInfo"><?php echo gettext('password.allowedcharacters') . ' ' . htmlentities(implode(' ', $this->allowedSpecialCharacters)) ?></div><?php
if ($this->notification):
echo $this->element('notification',
array('notification' => $this->notification));
endif;
$changeByToken = !empty($this->profileId) && !empty($this->token);
?><div id="password"><?php
?><form action="<?php echo $this->path ?>account/changepassword/" method="post"><?php
if ($changeByToken):
?><input name="profile_id" value="<?php echo $this->profileId ?>" type="hidden" />
<input name="token" value="<?php echo $this->token ?>" type="hidden" /><?php
endif;
?><table><?php
if (!$changeByToken && $this->hasOldPassword):
?><tr>
<td class="caption"><label for="old"><?php echo gettext("password.old") ?></label></td>
<td class="field"><input class="text" id="old" name="old" type="password" maxlength="100"/></td>
</tr><?php
if (!empty($this->oldPasswordMessage)):
?><tr>
<td></td>
<td class="field"><?php
?><label for="old" class="invalid"><?php echo $this->oldPasswordMessage ?></label><?php
?></td>
</tr><?php
endif;
endif;
?><tr>
<td class="caption"><label for="new1"><?php echo gettext("password.new1") ?></label></td>
<td class="field"><input class="text" id="new1" name="new1" type="password" maxlength="100"/></td>
</tr><?php
if (!empty($this->newPasswordMessage)):
?><tr>
<td></td>
<td class="field"><?php
?><label for="new1" class="invalid"><?php echo $this->newPasswordMessage ?></label><?php
?></td>
</tr><?php
endif;
?><tr>
<td class="caption"><label for="new2"><?php echo gettext("password.new2")?></label></td>
<td class="field"><input class="text" id="new2" name="new2" type="password" maxlength="100"/></td>
</tr>
</table>
<div class="buttons">
<button type="reset"><?php echo gettext('password.reset') ?></button>
<button type="submit"><?php echo gettext('password.submit') ?></button>
</div>
</form><?php
?></div>