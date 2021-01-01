<?php
if (!empty($this->caption)) {
?><dt class="fromto"><?php echo $this->caption ?></dt><?php
}
?><dd class="fromto"><?php
if (!empty($this->fromText)) {
?><span class="caption"><?php echo $this->fromText; ?></span><?php
}
foreach ($this->options as $key => $value) {
if (is_numeric($value)) {
?><span class="xs"><input type="radio" value="<?php echo $value ?>" name="<?php echo $this->name ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> <?php if ($this->default == $value) { echo 'checked="checked" '; } ?>/></span><?php
} else {
?><span class="labeled">
<label for="<?php echo $this->name . '_' . $key ?>"><?php echo prepareHTML($value) ?></label>
<input type="radio" id="<?php echo $this->name . '_' . $key ?>" value="<?php echo $key ?>" name="<?php echo $this->name ?>" <?php if(!empty($this->class)) { ?>class="<?php echo $this->class ?>"<?php } ?> <?php if ($this->default == $key) { echo 'checked="checked" '; } ?>/>
</span><?php
}
}
if (!empty($this->toText)) {
?><span class="caption"><?php echo $this->toText; ?></span><?php
}
?></dd>
<?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}