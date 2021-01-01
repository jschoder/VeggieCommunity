<li class="mchoice<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>">
<label<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><?php echo $this->caption ?></label><?php
if ($this->columns):
?><div class="wideCol"><?php
$chunks = array_chunk($this->options, ceil(count($this->options) / 2), true);
foreach ($chunks as $chunk):
?><div><ul><?php
foreach ($chunk as $id => $caption):
?><li>
<input id="<?php echo $this->id . '-' . $id ?>" type="checkbox" name="<?php echo $this->name ?>[]" value="<?php echo $id ?>" <?php if($this->default && in_array($id, $this->default)) { ?>checked="checked" <?php } ?>/>
<label for="<?php echo $this->id . '-' . $id ?>"><?php echo prepareHTML($caption) ?></label>
</li><?php
endforeach;
?></ul></div><?php
endforeach;
?></div><?php
else:
?><ul><?php
foreach ($this->options as $id => $caption):
?><li>
<input id="<?php echo $this->id . '-' . $id ?>" type="checkbox" name="<?php echo $this->name ?>[]" value="<?php echo $id ?>"<?php if($this->default && in_array($id, $this->default)) { ?> checked="checked" <?php } ?>/>
<label for="<?php echo $this->id . '-' . $id ?>"><?php echo prepareHTML($caption) ?></label>
</li><?php
endforeach;
?></ul><?php
endif;
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>