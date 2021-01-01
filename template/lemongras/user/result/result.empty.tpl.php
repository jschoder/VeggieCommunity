<h1><?php echo gettext('menu.users'); ?></h1><?php
echo $this->element('tabs/user.search',
array('path' => $this->path,
'site' => $this->site,
'requestQuery' => $this->requestQuery));
?><div class="notifyInfo"><?php echo gettext("result.empty") ?></div>
