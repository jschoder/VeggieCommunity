<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><div class="wideCol">
<div>
<section class="formHighlight">
<h2><?php echo gettext('box.login.title') ?></h2>
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>login/" method="post">
<input type="hidden" name="loginTargetUrl" value="<?php echo $this->loginTargetUrl; ?>" />
<ul>
<li class="mandatory">
<label for="login-email"><?php echo gettext('box.login.username') ?></label>
<input id="login-email" maxlength="255" name="login-email" placeholder="<?php  ?>" type="text" />
</li>
<li class="mandatory">
<label for="login-password"><?php echo gettext('box.login.password') ?></label>
<input id="login-password" maxlength="100" name="login-password" placeholder="<?php  ?>" type="password" />
</li>
<li>
<input class="check" id="login-auto" name="login-autologin" value="true" type="checkbox"/>
<label for="login-auto"><?php echo gettext("box.login.autologin")?></label>
</li>
<li>
<button class="login" type="submit"><?php echo gettext('box.login.confirm') ?></button>
</li>
</ul>
</form>
<?php /*
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>fb/login/" method="post">
<ul>
<li>
<button class="fb" type="submit"><?php echo gettext('box.login.loginFb') ?></button>
</li>
</ul>
</form>
*/ ?>
<aside>
<a href="<?php echo $this->path; ?>account/rememberpassword/"><?php echo gettext('box.login.lostpassword') ?></a>
</aside>
</section>
<?php if (!empty($this->news)): ?>
<h2><?php echo gettext('news') ?></h2>
<ul class="list news"><?php
if ($this->locale == 'de'):
$forumHashId = 'nnph7j4';
else:
$forumHashId = '8mtx1if';
endif;
foreach ($this->news as $newsHashId => $newsItem):
echo '<li><a href="' . $this->path . 'groups/forum/86om9vc/' . $forumHashId . '/#' . $newsHashId . '">' .
prepareHTML($newsItem[0]) .
'</a><p>' . prepareHTML($newsItem[1]) . '</p></li>';
endforeach;
?></ul>
<?php endif; ?>
</div>
<div>
<section><?php
echo gettext('start.infotext');
?><a class="button cta signup" href="<?php echo $this->path ?>account/signup/"><?php echo gettext('start.cta') ?></a><?php
/*
?><form accept-charset="UTF-8" action="<?php echo $this->path; ?>fb/login/" method="post">
<ul>
<li>
<a class="button cta signup" href="<?php echo $this->path ?>account/signup/"><?php echo gettext('start.cta') ?></a>
</li>
<li>
<button class="fb" type="submit"><?php echo gettext('box.registration.signUpFb') ?></button>
</li>
</ul>
</form><?php
*/
?></section>
</div>
</div><?php
$this->echoWideAd($this->locale, $this->plusLevel);