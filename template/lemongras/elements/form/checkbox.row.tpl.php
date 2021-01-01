<dd class="checkbox">
<input id="<?php echo $this->id ?>" type="checkbox" name="<?php echo $this->name ?>" value="<?php echo ($this->default === null ? '1' : $this->default) ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> />
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
</dd>