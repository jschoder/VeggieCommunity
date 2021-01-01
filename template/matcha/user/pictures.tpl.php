<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('pictures.title') ?></h1><?php
echo $this->element('tabs/account',
array('path' => $this->path,
'site' => $this->site));
?><div class="notifyInfo">
<p><?php echo gettext('pictures.limitations') ?></p>
<p><?php
if ($this->simpleUpload):
echo str_replace(
array(
'%LINK_START%',
'%LINK_END%'
),
array(
'<a href="' . $this->path . 'user/pictures/simple/">',
'</a>'
),
gettext('pictures.openNormal')
);
else:
echo str_replace(
array(
'%LINK_START%',
'%LINK_END%'
),
array(
'<a href="' . $this->path . 'user/pictures/simple/">',
'</a>'
),
gettext('pictures.openSimple')
);
endif;
?></p>
<p><?php echo gettext('pictures.rules') ?></p>
</div>
<div class="wideCol">
<div>
<p><strong><?php echo gettext('pictures.yes') ?></strong></p>
<ul class="list yes">
<li><?php echo gettext('pictures.yes.own') ?></li>
<li><?php echo gettext('pictures.yes.author') ?></li>
<li><?php echo gettext('pictures.yes.campaign') ?></li>
<li><?php echo gettext('pictures.yes.publicdomain') ?></li>
<li><?php echo gettext('pictures.yes.creativecommons') ?></li>
</ul>
</div>
<div>
<p><strong><?php echo gettext('pictures.no') ?></strong></p>
<ul class="list no">
<li><?php echo gettext('pictures.no.photocopies') ?></li>
<li><?php echo gettext('pictures.no.socialmedia') ?></li>
<li><?php echo gettext('pictures.no.google') ?></li>
<li><?php echo gettext('pictures.no.unknown') ?></li>
<li><?php echo gettext('pictures.no.commercial') ?></li>
<li><?php echo gettext('pictures.no.obscene') ?></li>
</ul>
</div>
</div><?php
echo $this->renderForm($this->form);
$this->echoWideAd($this->locale, $this->plusLevel);