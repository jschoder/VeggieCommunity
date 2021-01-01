<dd title="<?php echo $this->caption ?>"><select id="<?php echo $this->id ?>" name="<?php echo $this->name ?>" size="1" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?>><?php
foreach ($this->options as $key => $value) {
if ($key == $this->default) {
?><option value="<?php echo $key ?>" selected="selected"><?php echo prepareHTML($value) ?></option><?php
} else {
?><option value="<?php echo $key ?>"><?php echo prepareHTML($value) ?></option><?php
}
}
?></select></dd>