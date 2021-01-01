<dd class="text" title="<?php echo empty($this->help) ? $this->caption : $this->help ?>">
<input
class="text<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>"
id="<?php echo $this->id ?>" name="<?php echo $this->name ?>"
type="<?php echo $this->type ?>"
placeholder="<?php echo $this->caption ?>"
value="<?php echo $this->escapeAttribute($this->default) ?>"
maxlength="<?php echo $this->maxlength ?>"/>
</dd>