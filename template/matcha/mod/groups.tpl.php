<h1>Unconfirmed Groups</h1><?php
if (empty($this->groups)) { ?>
<div class="notifyInfo">No unconfirmed groups at the moment</div>
<?php } else { ?>
<form action="<?php echo $this->path ?>mod/groups/" method="post" accept-charset="UTF-8">
<table class="mod">
<tr>
<th></th>
<th></th>
<th>Name</th>
<th>Description</th>
<th>Rules</th>
<th>ModMessage</th>
<th>CreatedAt</th>
<th>Creator</th>
<th>Registration</th>
</tr>
<?php foreach ($this->groups as $group) { ?>
<tr>
<td rowspan="2">
<input id="confirm<?php echo $group['id'] ?>" name="action[<?php echo $group['id'] ?>]" value="confirm" type="radio" /> <label for="confirm<?php echo $group['id'] ?>">Confirm</label><br />
<input id="delete<?php echo $group['id'] ?>" name="action[<?php echo $group['id'] ?>]" value="delete" type="radio" /> <label for="delete<?php echo $group['id'] ?>">Delete</label><br />
<input id="skip<?php echo $group['id'] ?>" name="action[<?php echo $group['id'] ?>]" value="skip" type="radio" /> <label for="skip<?php echo $group['id'] ?>">Skip</label>
</td>
<td><?php
if (!empty($group['image'])) {
?><img alt="" style="vertical-align:top;" src="/groups/picture/crop/74/74/<?php echo $group['image'] ?>" width="50" height="50" /><?php
}
?></td>
<td><?php echo prepareHTML($group['name']) ?></td>
<td><?php echo prepareHTML($group['description']) ?></td>
<td><?php echo prepareHTML($group['rules']) ?></td>
<td><?php echo prepareHTML($group['modMessage']) ?></td>
<td><?php echo prepareHTML($group['createdAt']) ?></td>
<td><a href="<?php echo $this->path ?>user/view/<?php echo intval($group['profileId']) ?>/"><?php echo prepareHTML($group['profileNickname']) ?></a></td>
<td><?php echo prepareHTML($group['profileFirstEntry']) ?></td>
</tr>
<!--
<tr>
<td colspan ="8">
<textarea name="comment[<?php echo $group['id']  ?>]"></textarea>
</td>
</tr>
-->
<?php } ?>
</table>
<button type="submit">Save</button>
</form>
<?php } ?>
