<h1>Spam</h1><?php
if (empty($this->messages)) {
?><div class="notifyInfo">No spam messages</div><?php
} else {
?><table class="mod">
<tr>
<th>Sender</th>
<th>E-Mail</th>
<th>First Entry</th>
<th>Last Update</th>
<th>Last Login</th>
<th></th>
</tr>
<?php
foreach ($this->messages as $message) {
?><tr>
<td><a href="<?php echo $this->path ?>user/view/<?php echo $message['profileId'] ?>/"><?php echo prepareHTML($message['profileNickname']) ?></a></td>
<td><?php echo prepareHTML($message['profileEmail']) ?></td>
<td><?php echo $message['profileFirstEntry'] ?></td>
<td><?php echo $message['profileLastUpdate'] ?></td>
<td><?php echo $message['profileLastLogin'] ?></td>
<td>
<aside>
<nav>
<a class="pm" href="<?php echo $this->path ?>mod/pm/<?php echo $message['profileId']?>/<?php echo $message['recipientId'] ?>/500/"></a>
</nav>
</aside>
</td>
</tr><?php
}
?></table><?php
}