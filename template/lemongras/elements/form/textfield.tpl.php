<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><?php if (!empty($this->caption)) { ?><label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label><?php } ?></dt>
<dd><input class="text<?php
if(!empty($this->class)) { echo ' ' . $this->class; }
?>" <?php
if ($this->readonly) { echo ' readonly="readonly"'; }
?> id="<?php echo $this->id ?>" name="<?php echo $this->name ?>" type="<?php echo $this->type ?>" value="<?php echo $this->escapeAttribute($this->default) ?>" maxlength="<?php echo $this->maxlength ?>"/></dd>
<?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}