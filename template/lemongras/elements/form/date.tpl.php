<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label></dt>
<dd class="clearfix"><?php
echo $this->element(
'date',
array(
'locale' => $this->locale,
'class' => $this->class,
'dateStart' => floor(($this->minDate - time()) / 86400),
'dateEnd' => floor(($this->maxDate - time()) / 86400),
'id' => $this->id,
'name' => $this->name,
'default' => (empty($this->default) ? array() : $this->default)
)
);
if($this->time):
?><div class="clockpicker">
<span class="icon">
<span class="fa fa-time"></span>
</span>
<input class="time<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>" class="form-control" name="<?php echo $this->name ?>[time]" value="<?php if (!empty($this->default['time'])) { echo $this->default['time']; } ?>" />
</div><?php
endif;
?></dd><?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}