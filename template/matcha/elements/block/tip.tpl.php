<?php if (!empty($this->tips)):
$tipKey = mt_rand(0, count($this->tips) - 1);
?><aside id="blockTip" class="block collapsible">
<header><h3><?php echo gettext('block.tip.title') ?></h3></header>
<div>
<nav class="pag">
<a class="prev" href="#" title="<?php echo gettext('block.tip.prev') ?>"></a>
<a class="next" href="#" title="<?php echo gettext('block.tip.next') ?>"></a>
</nav>
<p class="jsTip" data-current="<?php echo $tipKey ?>">
<strong><?php echo gettext('block.tip.prefix') ?><span class="index"><?php echo ($tipKey + 1) ?></span></strong>
<span class="body"><?php echo $this->tips[$tipKey] ?></span></p>
</div>
<script>
var vc = vc || {};
vc.block = vc.block || {};
vc.block.tips = <?php echo json_encode($this->tips) ?>;
</script>
</aside>
<?php endif;