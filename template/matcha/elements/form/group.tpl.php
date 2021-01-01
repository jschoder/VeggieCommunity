<li class="group">
<section class="<?php echo $this->collapsed ? 'collapsed' : 'collapsible' ?>">
<h2><?php echo $this->caption ?></h2>
<div><?php
if ($this->columns === 1) {
?><ul><?php echo $this->childrenContent[0] ?></ul><?php
} else {
$class = 'wideCol';
if ($this->columns > 2) {
$class .= ' big';
}
?><div class="<?php echo $class ?>"><?php
foreach ($this->childrenContent as $childrenContent) {
?><div>
<ul><?php
echo $childrenContent;
?></ul>
</div><?php
}
?></div><?php
}
?></div>
</section>
</li>