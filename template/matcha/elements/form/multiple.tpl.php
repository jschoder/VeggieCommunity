<li class="rows<?php if (!empty($this->class)) { echo ' ' . $this->class; } ?>" data-delete="<?php echo $this->deleteCheckboxName ?>"><?php
if (!empty($this->caption)):
?><label<?php if ($this->mandatory): echo ' class="mandatory"'; endif; ?>><?php echo $this->caption ?></label><?php
endif;
?><ol<?php if ($this->sortable): echo ' class="dnd"'; endif; ?>><?php
foreach($this->filledChildrenContent as $id => $content):
?><li id="<?php echo $id ?>" class="row">
<ul><?php
if ($this->sortable):
?><li><span class="sorter"></span></li><?php
endif;
echo $content;
?></ul>
</li><?php
endforeach;
foreach($this->emptyChildrenContent as $id => $content):
?><li id="<?php echo $id ?>" class="jNewMultiple row" style="display:none">
<ul><?php
if ($this->sortable):
?><li><span class="sorter"></span></li><?php
endif;
echo $content;
?></ul>
</li><?php
endforeach;
?></ol><?php
foreach ($this->validationErrors as $validationError):
?><label class="error"><?php echo $validationError ?></label><?php
endforeach;
if (!empty($this->help)):
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
endif;
?></li>