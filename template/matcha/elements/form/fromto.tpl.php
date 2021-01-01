<li class="fromto"><?php
if (!empty($this->caption)):
?><label class="wideLabel"><?php echo $this->caption ?></label><?php
endif;
?><div><?php
$keys = array_keys($this->options);
$firstKey = null;
$lastKey = null;
foreach ($this->options as $key => $value):
if (is_numeric($value)):
$checkboxValue = $value;
else:
$checkboxValue = $key;
endif;
if ($firstKey === null):
$firstKey = $checkboxValue;
endif;
$lastKey = $checkboxValue;
endforeach;
if (!empty($this->fromText)):
?><label class="topLabel" for="<?php echo $this->name . '_' . $firstKey ?>"><?php echo $this->fromText; ?></label><?php
endif;
if (!empty($this->toText)):
?><label class="topLabel" for="<?php echo $this->name . '_' . $lastKey ?>"><?php echo $this->toText; ?></label><?php
endif;
foreach ($this->options as $key => $value):
if (is_numeric($value)):
$checkboxValue = $value;
$checkboxTitle = null;
else:
$checkboxValue = $key;
$checkboxTitle = prepareHTML($value);
endif;
if ($checkboxValue == $firstKey):
?><label class="inline first" for="<?php echo $this->name . '_' . $firstKey ?>"><span><?php
if (!empty($this->fromText)):
echo $this->fromText;
else:
echo $checkboxTitle;
endif;
?></span></label><?php
endif;
?><span class="xs"><?php
?><input id="<?php echo $this->name . '_' . $checkboxValue ?>"
type="radio"
value="<?php echo $checkboxValue ?>"
name="<?php echo $this->name ?>"<?php
if(!empty($this->class)) { echo ' class="' . $this->class . '"'; }
if ($this->default == $checkboxValue) { echo ' checked="checked" '; }
?>/><?php
?><label
style="font-weight:normal"
for="<?php echo $this->name . '_' . $checkboxValue ?>"<?php if (!empty($checkboxTitle)) { echo ' title="' . $checkboxTitle . '"'; } ?>></label><?php
?></span><?php
if ($checkboxValue == $lastKey):
?><label class="inline last" for="<?php echo $this->name . '_' . $lastKey ?>"><span><?php
if (!empty($this->toText)):
echo $this->toText;
else:
echo $checkboxTitle;
endif;
?></span></label><?php
endif;
endforeach;
?></div>
<?php
foreach ($this->validationErrors as $validationError):
?><label class="error"><?php echo $validationError ?></label><?php
endforeach;
if (!empty($this->help)):
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
endif;
?></li>