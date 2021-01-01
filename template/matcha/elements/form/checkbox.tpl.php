<li>
<input id="<?php echo $this->id ?>" type="checkbox" name="<?php echo $this->name ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> value="1" <?php if(!empty($this->default)) { ?>checked="checked" <?php } ?>/>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label><?php
foreach ($this->validationErrors as $validationError):
?><label class="error"><?php echo $validationError ?></label><?php
endforeach;
if (!empty($this->help)):
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
endif;1
?></li>