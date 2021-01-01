<?php
if (empty($this->profiles)):
?><div class="notifyInfo"><?php echo gettext('group.invitation.empty') ?></div><?php
else:
?><form action="#">
<ul class="thumblist"><?php
for($i=0; $i<count($this->profiles); $i++):
$profile = $this->profiles[$i];
?><li><label for="invite<?php echo $profile->id ?>"><?php
echo $this->element(
'picture.crop',
array(
'path' => $this->path,
'title' => $profile->getToolTipText(false),
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$profile->id],
)
);
?></label>
<label class="label" for="invite<?php echo $profile->id ?>"><?php echo prepareHTML($profile->nickname) ?></label>
<input id="invite<?php echo $profile->id ?>" type="checkbox" name="profileId[]" value="<?php echo $profile->id ?>" />
<aside class="actions">
<nav>
<label class="circle" for="invite<?php echo $profile->id ?>">
<span></span>
</label>
</nav>
</aside>
</li><?php
endfor;
?></ul>
<ul>
<li>
<label for="inviteMessage"><?php echo gettext('group.invitation.comment') ?></label>
<textarea id="inviteMessage" name="comment"></textarea>
</li>
<li>
<button type="submit"><?php echo gettext('group.invitation.submit') ?></button>
</li>
</ul>
</form><?php
endif;