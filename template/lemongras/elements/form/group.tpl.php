<dd>
<h2 id="slide-header-<?php echo $this->id ?>" class="<?php echo $this->collapsed ? 'hiddenslide' : 'openslide' ?>" onclick="setSlideVisible('<?php echo $this->id ?>', false)"><?php
echo $this->caption
?></h2>
<div id="slide-content-<?php echo $this->id ?>" <?php if($this->collapsed) { ?> style="display:none" <?php } ?>>
<dl><?php
foreach ($this->childrenContent as $childrenContent) {
echo $childrenContent;
}
?></dl>
</div>
</dd>