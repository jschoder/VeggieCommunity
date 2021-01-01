<li class="range<?php if ($this->mandatory) { echo ' mandatory'; } ?>">
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
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
?></select><?php
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>