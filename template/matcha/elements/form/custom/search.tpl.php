<li<?php if(!empty($this->class)) { echo ' class="' . $this->class . '"'; } ?>>
<label<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?> for="<?php echo $this->id . '-5'; ?>"><?php echo $this->caption ?></label>
<ul class="searching"><?php
$openGroup = null;
foreach ($this->options as $key => $value):
if ($key < 20):
?><li>
<label for="<?php echo $this->id . '-' . $key ?>"><?php echo prepareHTML($value) ?></label>
<input id="<?php echo $this->id . '-' . $key ?>" name="<?php echo $this->name ?>[]" type="checkbox" value="<?php echo $key ?>" <?php if($this->default && in_array($key, $this->default)) { ?>checked="checked" <?php } ?>/>
<label for="<?php echo $this->id . '-' . $key ?>"></label>
</li><?php
else:
$label = explode('(', str_replace(')', '', $value));
$group = trim($label[0]);
$groupItem = trim($label[1]);
if ($openGroup !== $group):
if ($openGroup !== null):
?></div></li><?php
endif;
?><li>
<label for="<?php echo $this->id . '-' . $key ?>"><?php echo prepareHTML($group) ?></label>
<div class="group"><?php
$openGroup = $group;
endif;
?><input id="<?php echo $this->id . '-' . $key ?>" name="<?php echo $this->name ?>[]" type="checkbox" value="<?php echo $key ?>" <?php if($this->default && in_array($key, $this->default)) { ?>checked="checked" <?php } ?>/>
<label for="<?php echo $this->id . '-' . $key ?>"><?php echo prepareHTML($groupItem) ?></label><?php
endif;
endforeach;
if ($openGroup !== null):
?></div></li><?php
endif;
?></ul><?php
foreach ($this->validationErrors as $validationError):
?><label class="error"><?php echo $validationError ?></label><?php
endforeach;
if (!empty($this->help)):
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
endif;
?></li>