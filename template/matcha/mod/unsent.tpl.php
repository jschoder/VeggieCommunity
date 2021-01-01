<h1>Send messages</h1>
<form action="<?php echo $this->path ?>mod/unsent/" method="post">
<button type="submit">Send messages</button>
</form>
<?php if (isset($this->sent) && isset($this->skipped)) { ?>
<strong>Sent: </strong> <?php echo $this->sent ?><br />
<strong>Skipped: </strong> <?php echo $this->skipped ?><br />
<?php } ?>
<h2>Unsent</h2>
<?php if (count($this->groupedMessages) > 0) { ?>
<table class="mod">
<tr>
<td>User</td>
<td></td>
<td></td>
<td>E-Mail</td>
<td>Signup</td>
<td>Length</td>
<td>Count</td>
</tr>
<?php
foreach ($this->groupedMessages as $group) {
$sender = $group['sender'];
// foreach ($group['msg'] as $length => $count) {
?><tr>
<td><a class="link" href="<?php echo $this->path ?>user/view/<?php echo $sender['id'] ?>/mod/"><?php echo $sender['nickname'] ?></a></td>
<td><a class="link" href="<?php echo $this->path ?>mod/pm/<?php echo $sender['id'] ?>/">[M]</a></td>
<td><a href="http://whatismyipaddress.com/ip/<?php echo $sender['ip'] ?>">{<?php echo $sender['ip_iso'] ?>}</a></td>
<td><?php echo $sender['email'] ?></td>
<td><?php echo $sender['signup'] ?></td>
<td><?php echo $group['length'] ?></td>
<td><?php echo $group['count'] ?></td>
</tr><?php
if (!empty($group['filter'])):
?><tr>
<td colspan="7">
<ul class="h">
<li><strong>FILTER</strong>&nbsp;&nbsp;</li><?php
foreach($group['filter'] as $filter):
?><li>&nbsp;&nbsp;<?php
echo $filter;
?>&nbsp;&nbsp;</li><?php
endforeach;
?></ul>
</td>
</tr><?php
endif;
}
?></table>
<?php } ?>