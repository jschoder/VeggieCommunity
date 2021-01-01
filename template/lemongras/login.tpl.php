<div id="register">
<?php
if (!empty($this->notification)) {
echo $this->element('notification',
array('notification' => $this->notification));
} else {
?><div class="infotext"><?php echo gettext("login.infotext")?></div><?php
}
?><p style="margin:1em 0">
<a class="button" href="<?php echo $this->path ?>account/signup/"><?php echo gettext('menu.register') ?></a>
<a class="button secondary" href="<?php echo $this->path ?>login/"><?php echo gettext('menu.login') ?></a>
</p>
</div>