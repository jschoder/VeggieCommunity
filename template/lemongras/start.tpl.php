<div id="start"><?php
echo $this->render('elements/box.login');
?>
<div class="content">
<div id="welcometext"><?php
echo gettext('start.infotext');
?><p>
<?php echo gettext('start.nomember'); ?>
<a class="button cta signup" href="<?php echo $this->path ?>account/signup/"><?php echo gettext('start.cta') ?></a>
</p>
</div>
</div>
</div>
