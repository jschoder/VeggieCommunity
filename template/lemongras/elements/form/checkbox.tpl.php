<dd>
<input id="<?php echo $this->id ?>" type="checkbox" name="<?php echo $this->name ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> value="1"<?php if(!empty($this->default)) { ?> checked="checked" <?php } ?>/>
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
</dd>
<?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}