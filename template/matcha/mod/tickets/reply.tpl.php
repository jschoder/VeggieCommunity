<?php
$ticketCategories = \vc\config\Fields::getTicketCategories();
?><form action="<?php echo $this->path ?>mod/tickets/reply/<?php echo $this->ticket->id ?>/" method="get" accept-charset="UTF-8">
<ul>
<li>
<select size="1" name="default" onchange="submit()">
<option value="0">None</option><?php
foreach($this->defaults as $key => $default) {
?><option value="<?php echo prepareHTML($key) ?>"><?php echo prepareHTML($default['title']) ?></option><?php
}
?></select>
</li>
</ul>
</form>
<form action="<?php echo $this->path ?>mod/tickets/reply/" method="post" accept-charset="UTF-8">
<input type="hidden" name="ticketId" value="<?php echo $this->ticket->id ?>" />
<ul>
<?php if (!empty($ticketCategories[$this->ticket->category])): ?>
<li><?php echo $ticketCategories[$this->ticket->category] ?></li>
<?php endif; ?>
<li>
<textarea class="jAutoHeight" name="message"><?php echo $this->template ?></textarea>
</li>
<li>
<input id="closeTicket" type="checkbox" name="close" checked="checked" value="1" /><label for="closeTicket">Close ticket</label>
</li>
<li>
<button type="submit">Send</button>
</li><?php
foreach ($this->ticketMessages as $ticketMessage):
?><li>
<p><?php
if ($ticketMessage->byAdmin):
?><strong>Administrator</strong><?php
else:
echo $this->ticket->profileId;
?> <a href="<?php echo $this->path ?>user/view/<?php echo $this->ticket->profileId ?>/"><?php echo prepareHTML($this->ticket->nickname) ?></a><br />
<a class="link" href="mailto:<?php echo $this->ticket->email ?>"><?php echo $this->ticket->email ?></a><?php
if (!empty($ticketMessage->email)):
echo '<br />' . $ticketMessage->email;
endif;
endif;
?><br /><?php echo $ticketMessage->createdAt ?>
</p>
<p><?php echo prepareHTML($ticketMessage->body) ?></p>
</li><?php
endforeach;
?><li><?php echo prepareHTML($this->ticket->debuginfo) ?></li>
</ul>
</form>
