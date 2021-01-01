<?php
$tabs = array(
array('path' => 'pm/inbox',
'caption' => gettext('mailbox.inbox.short')),
array('path' => 'pm/outbox',
'caption' => gettext('mailbox.outbox.short')),
array('path' => 'pm/trash',
'caption' => gettext('mailbox.trash.short')),
);
echo $this->element('tabs.links',
array('tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));