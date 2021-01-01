<h1><?php echo $this->shortTitle ?></h1><?php
if (empty($this->pictures)):
?><p>No pictures</p><?php
else:
?><p><?php echo count($this->pictures) ?> Pictures</p>
<form accept-charset="UTF-8" action="<?php echo $this->path ?>mod/pictures/<?php echo $this->targetPath ?>" method="post">
<table class="mod">
<thead>
<tr>
<th></th>
<th></th>
<th>Picture</th>
<th>Creation</th>
<th>Description</th>
</tr>
</thead>
<tbody><?php
foreach ($this->pictures as $picture):
?><tr>
<td style="min-width:6em"><?php
$first = true;
foreach ($this->actions as $action => $actionTitle):
if (!$first):
?><br /><?php
endif;
?><input id="<?php echo $action . $picture->id ?>" name="action[<?php echo $picture->id ?>]" value="<?php echo $action ?>" type="radio" <?php if ($this->defaultAction == $action) { echo 'checked="checked"'; } ?> />
<label for="<?php echo $action . $picture->id ?>" title="<?php echo $actionTitle ?>"><?php echo $action ?></label><?php
$first = false;
endforeach;
?></td>
<td>
<?php echo $picture->id ?><br />
<a class="<?php if ($this->profileActiveMap[$picture->profileid] < 0) { echo 'deleted'; } ?>" href="<?php echo $this->path ?>user/view/<?php echo $picture->profileid ?>/" target="_blank"><?php echo $picture->profileid ?></a><br />
<a href="https://www.google.com/searchbyimage?image_url=<?php echo urlencode('https://www.veggiecommunity.org/user/picture/small/' . $picture->filename) ?>" target="_blank">[G]</a>
<a href="<?php echo $this->path ?>mod/pictures/user/<?php echo $picture->profileid ?>/" target="_blank">[Pics]</a>
</td>
<td><a href="/user/picture/full/<?php echo $picture->filename ?>" target="_blank">
<img height="<?php echo $picture->smallheight ?>" width="<?php echo $picture->smallwidth ?>" src="/user/picture/small/<?php echo $picture->filename ?>" />
</a></td>
<td><?php echo $picture->creation ?></td>
<td><?php echo prepareHTML($picture->description) ?></td>
</tr><?php
endforeach;
?></tbody>
</table>
<button type="submit">Save</button>
</form><?php
endif;