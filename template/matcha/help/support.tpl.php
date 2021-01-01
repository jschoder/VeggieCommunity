<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('menu.help') ?></h1><?php
echo $this->element('tabs/help',
array('path' => $this->path,
'site' => $this->site));
?><div class="wideCol">
<div>
<h2><?php echo gettext('help.form.title')?></h2><?php
echo $this->renderForm($this->form);
?><p><?php
echo gettext('help.form.privacy');
?></p>
</div>
<div>
<?php if(!empty($this->ticketHistory)): ?>
<h2><?php echo gettext('help.history.title') ?></h2>
<ul class="list"><?php
foreach($this->ticketHistory as $ticket):
?><li<?php if (in_array($ticket['id'], $this->activeTicketNotifications)) { echo ' class="active"'; } ?>>
<a href="<?php echo $this->path ?>help/support/history/<?php echo $ticket['hashId'] ?>/"><?php
if (empty($ticket['subject'])):
echo gettext('help.history.emptySubject');
else:
echo prepareHTML($ticket['subject']);
endif;
echo ' (' . $this->ticketCategories[$ticket['category']] . ')';
?></a>
<div class="p"><ul class="h">
<li><?php
if ($ticket['status'] == \vc\object\Ticket::STATUS_OPEN):
echo gettext('help.history.status.open');
else:
echo gettext('help.history.status.closed');
endif;
?></li>
<li>
<span class="jAgo" data-ts="<?php echo strtotime($ticket['lastMessageCreatedAt']) ?>" title="<?php echo gettext('help.history.lastChange') ?>"></span>
</li>
</ul></div>
</li><?php
endforeach;
?></ul>
<?php endif; ?>
<h2><?php echo gettext('help.mail.title') ?></h2>
<p><?php echo gettext('help.mail.text')?></p>
<p class="supportMil"></p>
</div><?php
$this->addScript('vc.ui.printMil(\'supportMil\', \'veggiecommunity\', \'org\', \'help\');');
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);