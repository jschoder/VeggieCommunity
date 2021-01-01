<h1>Submitted Real Checks</h1><?php
if (empty($this->realCheckObjects)):
?><p>No submitted real checks</p><?php
else:
?><form action="<?php echo $this->path ?>mod/real/" method="post">
<ul>
<li>
<button type="submit" class="submit">Save</button>
</li><?php
foreach ($this->realCheckObjects as $realCheckObject):
?><li>
<h2><?php
echo prepareHTML($this->profiles[$realCheckObject->profileId]->nickname)
?> (<?php
echo $this->userLanguages[$realCheckObject->profileId]
?>)</h2>
<a class="jZoom" href="/mod/real/picture/full/<?php echo $realCheckObject->picture ?>">
<img alt="" src="/mod/real/picture/small/<?php echo $realCheckObject->picture ?>" />
</a>
<div>
<strong><?php echo $realCheckObject->code ?></strong>
</div>
<ul class="h">
<?php if ($realCheckObject->status == \vc\object\RealCheck::STATUS_SUBMITTED): ?>
<li>
<input id="confirm<?php echo $realCheckObject->id ?>" name="action[<?php echo $realCheckObject->id ?>]" type="radio" value="confirm" />
<label for="confirm<?php echo $realCheckObject->id ?>">Confirm</label>
</li>
<li>
<input id="noarm<?php echo $realCheckObject->id ?>" name="action[<?php echo $realCheckObject->id ?>]" type="radio" value="noarm.<?php echo $this->userLanguages[$realCheckObject->profileId] ?>" />
<label for="noarm<?php echo $realCheckObject->id ?>">No arm</label>
</li>
<li>
<input id="deny<?php echo $realCheckObject->id ?>" name="action[<?php echo $realCheckObject->id ?>]" type="radio" value="deny" />
<label for="deny<?php echo $realCheckObject->id ?>">Deny</label>
</li>
<?php endif; ?>
<?php if ($realCheckObject->status == \vc\object\RealCheck::STATUS_REOPENED): ?>
<li>
<input id="reconfirm<?php echo $realCheckObject->id ?>" name="action[<?php echo $realCheckObject->id ?>]" type="radio" value="reconfirm" />
<label for="reconfirm<?php echo $realCheckObject->id ?>">Reconfirm</label>
</li>
<li>
<input id="remove<?php echo $realCheckObject->id ?>" name="action[<?php echo $realCheckObject->id ?>]" type="radio" value="remove" />
<label for="remove<?php echo $realCheckObject->id ?>">Remove</label>
</li>
<?php endif; ?>
<li>
<input id="skip<?php echo $realCheckObject->id ?>" name="action[<?php echo $realCheckObject->id ?>]" type="radio" value="skip" checked="checked" />
<label for="skip<?php echo $realCheckObject->id ?>">Skip</label>
</li>
</ul>
<div>
<input name="comment[<?php echo $realCheckObject->id ?>]" maxlength="255" type="text" placeholder="Admin Comment" />
</div>
<div>
<textarea name="usermessage[<?php echo $realCheckObject->id ?>]" placeholder="Custom Denial Message"></textarea>
</div><?php
if (empty($this->pictures[$realCheckObject->profileId])):
?><p><strong>Missing pictures</strong></p><?php
else:
?><ul class="thumblist"><?php
foreach($this->pictures[$realCheckObject->profileId] as $picture):
?><li><a class="jZoom" href="/user/picture/full/<?php echo $picture->filename ?>"><?php
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $picture,
)
);
?></a>
<input id="picture<?php echo $picture->id ?>" type="checkbox" name="picture[<?php echo $realCheckObject->id ?>][]" value="<?php echo $picture->id ?>" />
<aside class="actions">
<nav>
<label class="circle" for="picture<?php echo $picture->id ?>">
<span></span>
</label>
</nav>
</aside>
</li><?php
endforeach;
?></ul><?php
endif;
?></li><?php
endforeach;
?><li>
<button type="submit" class="submit">Save</button>
</li>
</ul>
</form><?php
endif;
