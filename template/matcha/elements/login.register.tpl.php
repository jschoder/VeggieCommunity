<div class="quicklogin">
<div class="notifyInfo">
<p><?php echo gettext('login.inline'); ?></p>
<a class="button cta signup" href="<?php echo $this->path ?>account/signup/"><?php echo gettext('menu.register') ?></a>
<?php /*
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>fb/login/" method="post">
<button class="fb" href="<?php echo $this->path ?>fb/login/"><?php echo gettext('box.registration.signUpFb') ?></button>
</form>
*/ ?>
<a class="button cta secondary login" href="<?php echo $this->path ?>login/"><?php echo gettext('menu.login') ?></a>
</div>
</div>