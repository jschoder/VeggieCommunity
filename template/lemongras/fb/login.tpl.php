<h1><?php echo gettext('fb.login.title') ?></h1>
<div class="jLoginLoading jLoading"></div>
<div class="jLoadFail notifyError" style="display:none"><?php echo gettext('fb.login.loadFailed'); ?></div>
<div class="jLoginFailed notifyWarn" style="display:none"><?php echo gettext('fb.login.failed') ?></div>
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>account/signup/" id="jsFbSignup" method="post">
<input name="fb_user_id" type="hidden" value="" />
<input name="fb_access_token" type="hidden" value="" />
</form>
<script src="https://connect.facebook.net/en_US/all.js"></script><?php
$this->addScript('vc.fb.init();');
$this->addScript('vc.fb.login()');