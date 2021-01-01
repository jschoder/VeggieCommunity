<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label></dt>
<dd><select
id="<?php echo $this->id ?>"
name="<?php echo $this->name ?>"
size="1" <?php
if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php }
if(!empty($this->filterField)) { ?> data-filter-field="<?php echo $this->filterField ?>"<?php }
?>><?php
$activeGroup = null;
foreach ($this->options as $key => $value):
if (!empty($this->groups) && array_key_exists($key, $this->groups)):
$group = $this->groups[$key];
if ($group !== $activeGroup):
if ($activeGroup !== null):
?></optgroup><?php
endif;
?><optgroup id="<?php echo $this->id . '-g-' . $group ?>"><?php
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
endif;
?></select></dd>
<?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}