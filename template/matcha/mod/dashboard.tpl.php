<h1>Mod Dashboard</h1>
<div class="wideCol">
<div>
<?php if (!empty($this->modMessages)): ?>
<h2>Recent mod messages</h2>
<ul class="list"><?php
foreach ($this->modMessages as $modMessage):
?><li><?php
if (empty($modMessage['userId'])):
?><strong>Logged Out</strong><?php
else:
?><a href="<?php echo $this->path ?>user/view/<?php echo $modMessage['userId'] ?>/"><?php
echo $modMessage['userNickname'];
?></a><?php
endif;
?> <span class="jAgo inline" data-ts="<?php echo strtotime($modMessage['createdAt']) ?>"></span>
<p><?php
echo prepareHTML($modMessage['message']);
?></p><?php
if (!empty($modMessage['logins'])) {
?>Recent Logins: <?php
foreach ($modMessage['logins'] as $login) {
?><a href="<?php echo $this->path ?>user/view/<?php echo $login ?>/"><?php
echo $login
?></a> <?php
}
}
?></li><?php
endforeach;
?></ul>
<?php endif; ?>
&nbsp;
</div>
<div>
<p><a href="<?php echo $this->path ?>mod/errors/">Full Error Log</a></p>
<?php if (!empty($this->lastErrors)): ?>
<h2>Most recent errors</h2>
<ul class="list"><?php
foreach ($this->lastErrors as $lastError):
?><li title="<?php echo $lastError[2]; ?>"><?php
echo date('H:i:s', $lastError[0]) . ' :: ' . $lastError[1];
?></li><?php
endforeach;
?></ul>
<?php endif; ?>
<?php if (!empty($this->errorArchives)): ?>
<h2>Error Archive</h2>
<ul class="list"><?php
foreach ($this->errorArchives as $filename => $errorCount):
?><li><?php
echo $filename . ' (' . $errorCount . ')';
?></li><?php
endforeach;
?></ul>
<?php endif; ?>
<?php if (!empty($this->watchlist)): ?>
<h2>Watchlist</h2>
<ul class="list"><?php
foreach ($this->watchlist as $profileId => $values):
?><li>
<a href="<?php echo $this->path ?>user/view/<?php echo $profileId ?>/mod/"><?php
echo $profileId;
?></a><?php
if ($values[0] == 1) {
echo ' [U]';
}
?> <span class="jAgo inline" data-ts="<?php echo strtotime($values[1]) ?>"></span>
</li><?php
endforeach;
?></ul>
<?php endif; ?>
</div>
</div>
