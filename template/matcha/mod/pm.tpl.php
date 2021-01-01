<h1>PM</h1><?php
if (!empty($this->messages)) {
?><table class="mod"><?php
foreach ($this->messages as $message) {
?><tr class="<?php echo ($this->senderId == $message['senderId']) ? 'odd' : 'even'; ?>">
<td><a href="<?php echo $this->path ?>user/view/<?php echo $message['senderId'] ?>/"><?php echo $message['senderId'] ?></a></td>
<td><a href="<?php echo $this->path ?>user/view/<?php echo $message['recipientId'] ?>/"><?php echo $message['recipientId'] ?></a></td>
<td><?php echo prepareHTML($message['subject']) ?></td>
<td><?php echo prepareHTML($message['body']) ?></td>
<td><?php echo $message['created'] ?></td>
<td><?php echo $message['recipientStatus'];
if ($message['recipientStatus'] ==  \vc\object\Mail::RECIPIENT_STATUS_SPAM_CONFIRMED) {
echo ' [[SPAM CONFIRMED]]';
} else if ($message['recipientStatus'] == \vc\object\Mail::RECIPIENT_STATUS_SPAM_DENIED) {
echo ' [[SPAM DENIED]]';
}
?></td>
<td>
<aside>
<nav>
<a class="pm" href="<?php echo $this->path ?>mod/pm/<?php echo $message['senderId'] ?>/<?php echo $message['recipientId'] ?>/500/"></a>
<?php if ($message['recipientStatus'] == \vc\object\Mail::RECIPIENT_STATUS_SPAM_SUSPECT) { ?>
[[SPAM]]
<form action="<?php echo $this->path ?>mod/pm/<?php echo $message['senderId'] ?>/<?php echo $message['recipientId'] ?>/" method="post">
<a class="flag" href="#" onclick="parentNode.submit();" title="spam + deleted" ></a>
<input type="hidden" name="id" value="<?php echo $message['id'] ?>" />
<input type="hidden" name="status" value="-3" />
</form>
<form action="<?php echo $this->path ?>mod/pm/<?php echo $message['senderId'] ?>/<?php echo $message['recipientId'] ?>/" method="post">
<a class="delete" href="#" onclick="parentNode.submit();" title="spam suspicion denied" ></a>
<input type="hidden" name="id" value="<?php echo $message['id'] ?>" />
<input type="hidden" name="status" value="-4" />
</form>
<?php } ?>
</nav>
</aside>
</td>
</tr><?php
}
?></table><?php
}