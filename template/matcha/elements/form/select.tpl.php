<li<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
<select
id="<?php echo $this->id ?>"
name="<?php echo $this->name ?>"
size="1"<?php
if(!empty($this->class)) { ?> class="<?php echo $this->class ?>"<?php }
if(!empty($this->filterField)) { ?> data-filter-field="<?php echo $this->filterField ?>"<?php }
?>><?php
$activeGroup = null;
foreach ($this->options as $key => $value):
if (!empty($this->groups) && array_key_exists($key, $this->groups)):
$group = $this->groups[$key];
if ($group !== $activeGroup):
if ($activeGroup !== null):
?></optgroup><?php
echo "\n";
endif;
?><optgroup id="<?php echo $this->id . '-g-' . $group ?>" label=""><?php
echo "\n";
$activeGroup = $group;
endif;
endif;
?><option<?php
echo ' value="' . $key . '"';
if ($key == $this->default):
echo ' selected="selected"';
endif;
if (array_key_exists($key, $this->filterGroups)):
echo ' data-filter-group="' . $this->filterGroups[$key] . '"';
endif;
echo '>' . prepareHTML($value);
?></option><?php
endforeach;
if ($activeGroup !== null):
?></optgroup><?php
echo "\n";
endif;
?></select>
<?php
foreach ($this->validationErrors as $validationError) {
?><label class="error" for="<?php echo $this->id ?>"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help" for="<?php echo $this->id ?>"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>