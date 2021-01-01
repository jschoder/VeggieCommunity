<?php
?><table class="mod">
<tr>
<th>user</th>
<th>email</th>
<th>deleteReason</th>
<th>deleteDate</th>
<th>active days</th>
</tr><?php
foreach ($this->deleteReasons as $deleteReason):
?><tr>
<td><a href="<?php echo $this->path ?>user/view/<?php echo $deleteReason['profileId'] ?>/mod/"><?php echo prepareHTML($deleteReason['nickname']) ?></a></td>
<td><a href="mailto:<?php echo $deleteReason['email'] ?>"><?php echo prepareHTML($deleteReason['email']) ?></a></td>
<td><?php echo prepareHTML($deleteReason['deleteReason']) ?></td>
<td><?php echo prepareHTML($deleteReason['deleteDate']) ?></td>
<td><?php echo intval($deleteReason['activeDay']) ?></td>
</tr><?php
endforeach;
?></table><?php
var_dump($this->deleteReasons);