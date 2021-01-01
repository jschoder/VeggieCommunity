<dt>
<label<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><?php echo $this->caption ?></label>
</dt>
<dd><?php
?><ul><?php
foreach ($this->options as $id => $caption):
?><li>
<input id="<?php echo $this->id . '-' . $id ?>" type="checkbox" name="<?php echo $this->name ?>[]" value="<?php echo $id ?>"<?php if($this->default && in_array($id, $this->default)) { ?> checked="checked" <?php } ?>/>
<label for="<?php echo $this->id . '-' . $id ?>"><?php echo prepareHTML($caption) ?></label>
</li><?php
endforeach;
?></ul><?php
?></dd><?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}