<?php
$tabs = array(
array('path' => 'mod/tickets',
'caption' => 'Open'),
array('path' => 'mod/tickets/' . \vc\object\Ticket::STATUS_CLOSED,
'caption' => 'Done',),
array('path' => 'mod/tickets/' . \vc\object\Ticket::STATUS_SPAM,
'caption' => 'Spam'),
);
echo $this->element('tabs.links',
array('class' => 'majorTabs',
'tabs' => $tabs,
'path' => $this->path,
'site' => $this->site,
'siteParams' => $this->siteParams));
