<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
</dt>
<dd class="range">
<select<?php if(!empty($this->class)) { echo ' class="' . $this->class . '"'; } ?> id="<?php echo $this->id ?>" name="<?php echo $this->name ?>[from]"><?php
for ($i = $this->minimum; $i <= $this->maximum; $i++):
if (!empty($this->default['from']) && $this->default['from'] == $i):
?><option selected="selected"><?php echo $i ?></option><?php
else:
?><option><?php echo $i ?></option><?php
endif;
endfor;
?></select>
-
<select<?php if(!empty($this->class)) { echo ' class="' . $this->class . '"'; } ?> id="<?php echo $this->id ?>To" name="<?php echo $this->name ?>[to]"><?php
for ($i = $this->minimum; $i <= $this->maximum; $i++):
if (!empty($this->default['to']) && $this->default['to'] == $i):
?><option selected="selected"><?php echo $i ?></option><?php
else:
?><option><?php echo $i ?></option><?php
endif;
endfor;
?></select>
</dd><?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}