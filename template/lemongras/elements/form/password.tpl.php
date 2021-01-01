<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
</dt>
<dd>
<input class="pw<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>" id="<?php echo $this->id ?>" name="<?php echo $this->name ?>[0]" type="password" maxlength="<?php echo $this->maxlength ?>"/>
</dd>
<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>Repeat"><?php echo $this->repeatCaption ?></label>
</dt>
<dd>
<input class="pw<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>" id="<?php echo $this->id ?>Repeat" name="<?php echo $this->name ?>[1]" type="password" maxlength="<?php echo $this->maxlength ?>"/>
</dd><?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}