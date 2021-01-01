<li<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<?php if (!empty($this->caption)) { ?><label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label><?php } ?>
<input<?php
if(!empty($this->class)) { echo ' class="' . $this->class . '"'; }
if ($this->readonly) { echo ' readonly="readonly"'; }
?> id="<?php echo $this->id ?>" name="<?php echo $this->name ?>" type="<?php echo $this->type ?>" value="<?php echo $this->escapeAttribute($this->default) ?>" maxlength="<?php echo $this->maxlength ?>" /><?php
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>