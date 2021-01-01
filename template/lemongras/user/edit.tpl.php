<h1><?php echo gettext('edit.title') ?></h1><?php
echo $this->element('tabs/account',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div id="content" class="slideBox profileEdit"><?php
echo $this->renderForm($this->form);
?></div>