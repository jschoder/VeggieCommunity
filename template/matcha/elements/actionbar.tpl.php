<nav class="actionBar"><?php
foreach ($this->actions as $action):
echo '<a class="button ' . $action->getClass() . '"';
if ($action->getId() !== null):
echo ' id="' . $action->getId() . '"';
endif;
if ($action->getOnclick() !== null):
echo ' onclick="' . $action->getOnclick() . '"';
endif;
foreach ($action->getData() as $field => $value) {
echo ' data-' . $field . '="' . $value . '"';
}
if ($action->getHref() !== null):
echo ' href="' . $action->getHref() . '"';
else:
echo ' href="#"';
endif;
if ($action->getTitle() !== null):
echo ' title="' . $action->getTitle() . '"';
else:
echo ' title="' . $action->getCaption() . '"';
endif;
echo '>';
if (!$action->isImportant()):
echo '<span>';
endif;
echo $action->getCaption();
if (!$action->isImportant()):
echo '</span>';
endif;
echo '</a> ';
endforeach;
if (isset($this->customContent)):
echo $this->customContent;
endif;
?></nav>