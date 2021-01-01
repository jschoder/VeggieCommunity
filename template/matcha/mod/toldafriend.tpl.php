<h1>Told a friend</h1>
<form accept-charset="UTF-8" action="<?php echo $this->path ?>mod/toldafriend/" method="post">
<ul><?php
foreach ($this->toldafriendObjects as $toldafriend):
?><li>
<div>
<ul class="h">
<li><a href="<?php echo $this->path ?>user/view/<?php echo $toldafriend->id ?>"><?php echo $toldafriend->id ?></a></li>
<li><a href="mailto:<?php echo prepareHTML($toldafriend->sender) ?>"><?php echo prepareHTML($toldafriend->sender) ?></a></li>
<?php if (!empty($toldafriend->subject)): ?>
<li><strong><?php echo prepareHTML($toldafriend->subject); ?></strong></li>
<?php endif; ?>
</ul>
</div>
<p><?php
echo prepareHTML($toldafriend->body);
?></p>
<aside class="actions">
<nav>
<input id="toldafriend<?php echo $toldafriend->id ?>send" name="id[<?php echo $toldafriend->id ?>]" type="radio" value="<?php echo \vc\object\ToldAFriend::STATUS_SENT ?>" />
<label for="toldafriend<?php echo $toldafriend->id ?>send">Send</label>
<input id="toldafriend<?php echo $toldafriend->id ?>deny" name="id[<?php echo $toldafriend->id ?>]" type="radio" value="<?php echo \vc\object\ToldAFriend::STATUS_DENIED ?>" />
<label for="toldafriend<?php echo $toldafriend->id ?>deny">Deny</label>
<input id="toldafriend<?php echo $toldafriend->id ?>skip" name="id[<?php echo $toldafriend->id ?>]" type="radio" value="<?php echo \vc\object\ToldAFriend::STATUS_UNSENT ?>" checked="checked" />
<label for="toldafriend<?php echo $toldafriend->id ?>skip">Skip</label>
</nav>
</aside>
</li><?php
endforeach;
?><li>
<button type="submit" class="submit">Save</button>
</li>
</ul>
</form>
