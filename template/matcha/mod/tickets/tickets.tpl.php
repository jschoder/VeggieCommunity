<h1>Tickets</h1><?php
echo $this->element('tabs/admin.tickets',
array('path' => $this->path,
'site' => $this->site,
'siteParams' => $this->siteParams));
if ($this->statusFilter == 1) {
$actionUrl = $this->path . 'mod/tickets/';
} else {
$actionUrl = $this->path . 'mod/tickets/' . $this->statusFilter . '/';
}
?><form action="<?php echo $actionUrl ?>" method="post">
<button type="submit">Save</button>
<table class="mod">
<tr>
<th>Profile</th>
<th>Subject</th>
<th>Debuginfo</th>
</tr>
<?php
foreach ($this->tickets as $ticket) {
?><tr>
<td>
<?php echo $ticket->profileId ?>
&nbsp;
<a href="<?php echo $this->path ?>user/view/<?php echo $ticket->profileId ?>/"><?php echo prepareHTML($ticket->nickname) ?></a><br />
<a class="link" href="mailto:<?php echo $ticket->email ?>"><?php echo $ticket->email ?></a><br />
<a href="<?php echo $this->path ?>mod/tickets/reply/<?php echo $ticket->id ?>/">[reply]</a>
<ul>
<li><input id="ticketstatus.<?php echo $ticket->id ?>.none" type="radio" name="tickets[<?php echo $ticket->id ?>]" value="none" checked="checked" />
<label for="ticketstatus.<?php echo $ticket->id ?>.none">No Change</label></li>
<li><input id="ticketstatus.<?php echo $ticket->id ?>.<?php echo \vc\object\Ticket::STATUS_OPEN ?>" type="radio" name="tickets[<?php echo $ticket->id ?>]" value="<?php echo \vc\object\Ticket::STATUS_OPEN ?>"/>
<label for="ticketstatus.<?php echo $ticket->id ?>.<?php echo \vc\object\Ticket::STATUS_OPEN ?>">Open</label></li>
<li><input id="ticketstatus.<?php echo $ticket->id ?>.<?php echo \vc\object\Ticket::STATUS_CLOSED ?>" type="radio" name="tickets[<?php echo $ticket->id ?>]" value="<?php echo \vc\object\Ticket::STATUS_CLOSED ?>"/>
<label for="ticketstatus.<?php echo $ticket->id ?>.<?php echo \vc\object\Ticket::STATUS_CLOSED ?>">Closed</label></li>
<li><input id="ticketstatus.<?php echo $ticket->id ?>.<?php echo \vc\object\Ticket::STATUS_SPAM ?>" type="radio" name="tickets[<?php echo $ticket->id ?>]" value="<?php echo \vc\object\Ticket::STATUS_SPAM ?>"/>
<label for="ticketstatus.<?php echo $ticket->id ?>.<?php echo \vc\object\Ticket::STATUS_SPAM ?>">Spam</label></li>
</ul>
</td>
<td><?php echo prepareHTML($ticket->subject) ?></td>
<td><?php echo prepareHTML($ticket->debuginfo) ?></td>
</tr><?php
}
?></table>
<button type="submit">Save</button>
</form>