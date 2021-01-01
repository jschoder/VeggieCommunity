<li<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
<input id="<?php echo $this->id ?>" name="<?php echo $this->name ?>" type="file" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> /><?php
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>