<dd class="radio">
<input id="<?php echo $this->id . $this->default ?>" type="radio" name="<?php echo $this->name ?>" value="<?php echo $this->default ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> <?php if($this->selected) {?>checked="checked"<?php } ?> />
<label for="<?php echo $this->id  . $this->default ?>"><?php echo $this->caption ?></label>
</dd>