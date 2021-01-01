<li<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label><?php
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
?> <div class="clockpicker<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>">
<input id="<?php echo $this->id ?>Time" name="<?php echo $this->name ?>[time]" value="<?php if (!empty($this->default['time'])) { echo $this->default['time']; } ?>" type="text" title="<?php echo gettext('form.date.time') ?>" /><label class="icon" for="<?php echo $this->id ?>Time"></label>
</div><?php
endif;
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>