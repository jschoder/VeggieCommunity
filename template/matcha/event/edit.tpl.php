<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo $this->shortTitle ?></h1><?php
echo $this->element('tabs/events',
array('path' => $this->path,
'site' => $this->site,));
echo $this->renderForm($this->form);
$this->echoWideAd($this->locale, $this->plusLevel);