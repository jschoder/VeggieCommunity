<div id="login" class="clearfix">
<h2><?php echo gettext("box.login.title")?></h2>
<form action="<?php echo $this->path ?>login/" method="post" accept-charset="UTF-8">
<input type="hidden" name="loginTargetUrl" value="<?php echo $this->escapeAttribute($this->loginTargetUrl) ?>" />
<ul class="clearfix">
<li>
<label for="login-email"><?php echo gettext("box.login.username")?> </label>
<input id="login-email" class="text130" type="text" name="login-email" maxlength="255" tabindex="1" />
<p class="signUp"><?php echo gettext('face.nomember.yet')?> <a href="<?php echo $this->path ?>account/signup/"><?php echo gettext('face.signuphere')?></a></p>
</li>
<li>
<label for="login-password"><?php echo gettext("box.login.password")?> </label>
<input id="login-password" class="text130" type="password" name="login-password" maxlength="70" tabindex="2" />
<a class="lostPW" href="<?php echo $this->path ?>account/rememberpassword/"><?php echo gettext("box.login.lostpassword")?></a>
</li>
<li>
<button type="submit" tabindex="3"><?php echo gettext("box.login.confirm")?></button>
<p class="autoLogin">
<input class="check" id="login-auto" name="login-autologin" value="true" type="checkbox"/>
<label for="login-auto"><?php echo gettext("box.login.autologin")?> </label>
</p>
</li>
</ul>
</form>
</div>
