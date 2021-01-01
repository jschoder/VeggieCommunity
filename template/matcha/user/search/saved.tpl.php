<h1><?php echo gettext('menu.search.saved'); ?></h1><?php
echo $this->element('tabs/user.search',
array('path' => $this->path,
'site' => $this->site));
if ($this->isEmpty):
?><div class="notifyInfo"><?php
echo gettext('result.savesearch.empty');
?></div><?php
else:
echo $this->renderForm($this->form);
endif;
