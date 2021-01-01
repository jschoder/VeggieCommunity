<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('menu.users'); ?></h1><?php
echo $this->element('actionbar',
array('actions' => $this->actions));
echo $this->element('tabs/user.search',
array('path' => $this->path,
'site' => $this->site,
'requestQuery' => $this->requestQuery));
?><div class="notifyInfo"><?php echo gettext("result.empty") ?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
