<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('real.title') ?></h1><?php
echo $this->element('tabs/account',
array('path' => $this->path,
'site' => $this->site));
if ($this->openRealCheck === null && $this->submittedRealCheckObject === null):
?><h2><?php echo gettext('real.step1.title') ?></h2>
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>account/real/create/" class="login" method="post">
<ul>
<li>
<div class="notifyInfo">
<p><?php
echo gettext('real.infotext')
?></p>
<ul class="list">
<li><?php echo gettext('real.infotext.step.profilepics') ?></li>
<li><strong><?php echo gettext('real.infotext.step.code') ?></strong></li>
<li><?php echo gettext('real.infotext.step.code.paper') ?></li>
<li><?php echo gettext('real.infotext.step.code.picture') ?></li>
<li><?php echo gettext('real.infotext.step.code.upload') ?></li>
<li><?php echo gettext('real.infotext.step.confirm') ?></li>
<li><?php echo gettext('real.infotext.step.time') ?></li>
</ul>
</div>
</li>
<li>
<button class="cta add" type="submit"><?php echo gettext('real.createCta') ?></button>
</li>
</ul>
</form><?php
endif;
if ($this->openRealCheck !== null):
?><h2><?php echo gettext('real.step2.title') ?></h2><?php
echo $this->renderForm($this->confirmationForm);
endif;
if ($this->submittedRealCheckObject !== null):
?><h2><?php echo gettext('real.step3.title') ?></h2>
<div class="notifyInfo">
<p><?php
echo gettext('real.infotext')
?></p>
<ul class="list">
<li><?php echo gettext('real.infotext.step.profilepics') ?></li>
<li><?php echo gettext('real.infotext.step.code') ?></li>
<li><?php echo gettext('real.infotext.step.code.paper') ?></li>
<li><?php echo gettext('real.infotext.step.code.picture') ?></li>
<li><?php echo gettext('real.infotext.step.code.upload') ?></li>
<li><strong><?php echo gettext('real.infotext.step.confirm') ?></strong></li>
<li><strong><?php echo gettext('real.infotext.step.time') ?></strong></li>
</ul>
</div><?php
endif;
$this->echoWideAd($this->locale, $this->plusLevel);