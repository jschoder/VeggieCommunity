<h1><?php echo gettext('menu.help') ?></h1><?php
echo $this->element('tabs/help',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div class="clearfix">
<div class="help">
<h3><?php echo gettext('help.form.title')?></h3><?php
echo $this->renderForm($this->form);
?><p><?php
echo gettext('help.form.privacy');
?></p>
</div>
<div class="help">
<?php if(!empty($this->ticketHistory)): ?>
<h3><?php echo gettext('help.history.title') ?></h3>
<ul><?php
foreach($this->ticketHistory as $ticket):
?><li>
<a href="<?php echo $this->path ?>help/support/history/<?php echo $ticket['hashId'] ?>/"><?php
if (empty($ticket['subject'])):
echo gettext('help.history.emptySubject');
else:
echo prepareHTML($ticket['subject']);
endif;
echo ' (' . $this->ticketCategories[$ticket['category']] . ')';
?></a>
<p><?php
if ($ticket['status'] == \vc\object\Ticket::STATUS_OPEN):
echo gettext('help.history.status.open');
else:
echo gettext('help.history.status.closed');
endif;
?> | <span class="jAgo" data-ts="<?php echo strtotime($ticket['lastMessageCreatedAt']) ?>" title="<?php echo gettext('help.history.lastChange') ?>"></span>
</p>
</li><?php
endforeach;
?></ul>
<?php endif; ?>
<h3><?php echo gettext('help.mail.title') ?></h3>
<p><?php echo gettext('help.mail.text') ?></p>
<p class="supportMil"></p>
</div><?php
$this->addScript('vc.ui.printMil(\'supportMil\', \'veggiecommunity\', \'org\', \'help\');');
?></div>