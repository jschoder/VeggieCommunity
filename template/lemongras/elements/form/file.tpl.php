<dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label></dt>
<dd><input id="<?php echo $this->id ?>" name="<?php echo $this->name ?>" type="file" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> /></dd>
<?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}
