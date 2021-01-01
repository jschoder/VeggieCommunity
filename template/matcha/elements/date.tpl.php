<div class="datepicker<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>"
data-date-start="<?php echo $this->dateStart ?>"
data-date-end="<?php echo $this->dateEnd ?>"><?php
if ($this->locale === 'de'):
$fields = array('day', 'month', 'year');
else:
$fields = array('month', 'day', 'year');
endif;
$first = true;
foreach ($fields as $field):
?><input
<?php if($first) { ?>id="<?php echo $this->id ?>"<?php } ?>
class="<?php echo $field; if($first) { echo ' first'; } ?>"
name="<?php echo $this->name ?>[<?php echo $field ?>]"
type="text"
placeholder="<?php echo gettext('form.date.' . $field) ?>"
title="<?php echo gettext('form.date.' . $field) ?>"
value="<?php if(!empty($this->default[$field])) { echo $this->escapeAttribute($this->default[$field]); } ?>" /><?php
$first = false;
endforeach;
?><label class="icon" for="<?php echo $this->id ?>"></label>
</div>