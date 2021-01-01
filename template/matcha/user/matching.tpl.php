<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('matching.title') ?></h1><?php
echo $this->element('tabs/account',
array('path' => $this->path,
'site' => $this->site));
?><div class="notifyInfo">
<p><?php echo gettext('matching.infotext.1') ?></p>
<p><?php echo gettext('matching.infotext.2') ?></p>
<p><?php echo gettext('matching.infotext.3') ?></p>
</div><?php
echo $this->renderForm($this->form);
$this->echoWideAd($this->locale, $this->plusLevel);