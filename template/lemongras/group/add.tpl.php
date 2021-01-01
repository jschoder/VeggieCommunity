<?php
echo $this->element('tabs/groups',
array('path' => $this->path,
'site' => $this->site,));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
echo $this->renderForm($this->form); ?>