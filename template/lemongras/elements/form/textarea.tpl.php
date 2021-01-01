<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label></dt>
<dd><textarea id="<?php echo $this->id ?>" name="<?php echo $this->name ?>" maxlength="<?php echo $this->maxlength ?>" class="jAutoHeight <?php if(!empty($this->class)) { echo $this->class; } ?>" rows="5"><?php
echo $this->default;
?></textarea></dd>
<?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}