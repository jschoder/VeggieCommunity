<li<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
<input class="pw<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>" id="<?php echo $this->id ?>" name="<?php echo $this->name ?>[0]" type="password" maxlength="<?php echo $this->maxlength ?>"/>
<label for="<?php echo $this->id ?>Repeat"><?php echo $this->repeatCaption ?></label>
<input class="pw<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>" id="<?php echo $this->id ?>Repeat" name="<?php echo $this->name ?>[1]" type="password" maxlength="<?php echo $this->maxlength ?>"/>
<div class="passwordStrength" style="display:none" data-specialchars="<?php echo prepareHTML(implode('', $this->validChars)) ?>">
<div class="label"><?php echo gettext('form.passwordStrength') ?>: <span class="verdict"></span></div>
<div class="bar"><div class=""></div></div>
<div class="comment">
<ul class="list"></ul>
</div>
</div><?php
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
?></li>