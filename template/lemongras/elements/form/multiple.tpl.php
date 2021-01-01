<?php if (!empty($this->caption)) {
?><dt<?php if ($this->mandatory) { echo ' class="mandatory"'; } ?>><?php echo $this->caption ?></dt><?php
}
?><dd class="rows<?php if ($this->sortable) { echo ' dnd'; } ?>" data-delete="<?php echo $this->deleteCheckboxName ?>"><?php
foreach($this->filledChildrenContent as $id => $content) {
?><dl id="<?php echo $id ?>" class="row"><?php
if ($this->sortable) {
?><span class="sorter"></span><?php
}
echo $content;
?></dl><?php
}
foreach($this->emptyChildrenContent as $id => $content) {
?><dl id="<?php echo $id ?>" class="row jNewMultiple" style="display:none"><?php
if ($this->sortable) {
?><span class="sorter"></span><?php
}
echo $content;
?></dl><?php
}
?></dd><?php
foreach ($this->validationErrors as $validationError) {
?><dd class="error"><?php echo $validationError ?></dd><?php
}
if (!empty($this->help)) {
?><dd class="help"><?php echo prepareHTML($this->help) ?></dd><?php
}