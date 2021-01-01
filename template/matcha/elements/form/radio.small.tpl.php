<li class="radio" title="<?php echo $this->caption ?>">
<input id="<?php echo $this->id . $this->default ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> type="radio" name="<?php echo $this->name ?>" value="<?php echo $this->default ?>" <?php if($this->selected) {?>checked="checked"<?php } ?> />
<label for="<?php echo $this->id . $this->default ?>"></label>
</li>