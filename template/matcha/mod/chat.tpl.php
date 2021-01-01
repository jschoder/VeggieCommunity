<h1>Chat</h1><?php
if (!empty($this->messages)):
$currentDate = null;
foreach ($this->messages as $message):
$date = date('Y-m-d', $message['dateTime']);
if ($currentDate !== $date):
if ($currentDate !== null):
?></table><?php
endif;
?><h2><?php print $date ?></h2><?php
$currentDate = $date;
?><table class="mod">
<tr>
<th>Time</th>
<th>Channel</th>
<th>Author</th>
<th>Text</th>
</tr><?php
endif;
?><tr class="<?php if ($this->userFilter === $message['userId']) { echo 'highlight'; } ?>">
<td><?php echo date('H:i:s', $message['dateTime']) ?></td>
<td><a href="<?php echo $this->path . 'mod/chat/channel/' . $message['channel'] . '/' ?>"><?php echo $message['channel'] ?></a></td>
<td><?php
if ($message['userId'] === 2147483647):
?><strong><?php echo $message['userName'] ?></strong><?php
else:
?><a href="<?php echo $this->path . 'user/view/' . $message['userId'] . '/' ?>">[D]</a> <?php
?><a href="<?php echo $this->path . 'mod/chat/user/' . $message['userId'] . '/' ?>"><?php echo $message['userName'] ?></a><?php
endif;
?></td>
<td><?php echo prepareHTML($message['text']) ?></td>
</tr><?php
endforeach;
?></table><?php
endif;