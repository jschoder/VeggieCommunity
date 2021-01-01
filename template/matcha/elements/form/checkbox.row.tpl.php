<li class="checkbox<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>">
<input id="<?php echo $this->id ?>" type="checkbox" name="<?php echo $this->name ?>" value="<?php echo ($this->default === null ? '1' : $this->default) ?>" />
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
</li>