<li<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
<textarea id="<?php echo $this->id ?>" name="<?php echo $this->name ?>" maxlength="<?php echo $this->maxlength ?>" class="jAutoHeight <?php if(!empty($this->class)) { echo $this->class; } ?>" rows="3"><?php
echo $this->default;
?></textarea><?php
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>