<h1><?php echo gettext('unregister.sitetitle')?></h1>
<?php
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
}
echo $this->renderForm($this->form);
