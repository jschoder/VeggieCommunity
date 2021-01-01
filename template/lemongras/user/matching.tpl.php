<h1><?php echo gettext('matching.title') ?></h1><?php
echo $this->element('tabs/account',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><p><?php echo gettext('matching.infotext.1') ?></p><?php
?><p><?php echo gettext('matching.infotext.2') ?></p><?php
?><p><?php echo gettext('matching.infotext.3') ?></p><?php
echo $this->renderForm($this->form); ?>