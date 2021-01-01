<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('unregister.sitetitle')?></h1><?php
$this->echoWideAd($this->locale, $this->plusLevel);
echo $this->renderForm($this->form);
$this->echoWideAd($this->locale, $this->plusLevel);
