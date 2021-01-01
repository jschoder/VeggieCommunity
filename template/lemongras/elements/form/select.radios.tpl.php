<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><?php echo $this->caption ?></dt>
<dd><?php
$first = true;
foreach ($this->options as $key => $value) {
if (!$first) {
?><br /><?php
}
$id = $this->id  . '.' . $key;
?><input id="<?php echo $id ?>" type="radio" name="<?php echo $this->name ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> value="<?php echo $key ?>"  />
<label for="<?php echo $id ?>"><?php echo $value ?></label><?php
$first = false;
}
?></dd>
<?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}