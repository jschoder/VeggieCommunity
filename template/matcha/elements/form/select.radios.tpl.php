<li<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label><?php echo $this->caption; ?></label><?php
foreach ($this->options as $key => $value):
$id = $this->id  . '.' . $key;
?><input id="<?php echo $id ?>" type="radio" name="<?php echo $this->name ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> value="<?php echo $key ?>"  />
<label for="<?php echo $id ?>" class="secondary"><?php echo $value ?></label><?php
endforeach;
foreach ($this->validationErrors as $validationError):
?><label class="error"><?php echo $validationError ?></label><?php
endforeach;
if (!empty($this->help)):
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
endif;
?></li>