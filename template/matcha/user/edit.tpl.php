<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('edit.title') ?></h1><?php
echo $this->element('tabs/account',
array('path' => $this->path,
'site' => $this->site));
echo $this->renderForm($this->form);
$this->echoWideAd($this->locale, $this->plusLevel);
