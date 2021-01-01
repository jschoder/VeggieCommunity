<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
</dt>
<dd<?php if(!empty($this->class)) { echo ' class="' . $this->class . '"'; } ?>>
<table class="searching"><?php
$openGroup = null;
foreach ($this->options as $key => $value):
if ($key < 20):
?><tr>
<td>
<label for="<?php echo $this->id . '-' . $key ?>"><?php echo prepareHTML($value) ?></label>
</td>
<td>
<input id="<?php echo $this->id . '-' . $key ?>" name="<?php echo $this->name ?>[]" type="checkbox" value="<?php echo $key ?>"<?php if($this->default && in_array($key, $this->default)) { ?> checked="checked" <?php } ?>/>
</td>
</tr><?php
else:
$label = explode('(', str_replace(')', '', $value));
$group = trim($label[0]);
$groupItem = trim($label[1]);
if ($openGroup !== $group):
if ($openGroup !== null):
?></td></tr><?php
endif;
?><tr>
<td>
<label for="<?php echo $this->id . '-' . $key ?>"><?php echo prepareHTML($group) ?></label>
</td>
<td><?php
$openGroup = $group;
endif;
?><input id="<?php echo $this->id . '-' . $key ?>" name="<?php echo $this->name ?>[]" type="checkbox" value="<?php echo $key ?>"<?php if($this->default && in_array($key, $this->default)) { ?> checked="checked" <?php } ?>/>
<label for="<?php echo $this->id . '-' . $key ?>"><?php echo prepareHTML($groupItem) ?></label><?php
endif;
endforeach;
if ($openGroup !== null):
?></td></tr><?php
endif;
?></table>
</dd><?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}