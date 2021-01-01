<?php
if (empty($this->profiles)) {
?><div class="notifyInfo"><?php echo gettext('event.invitation.empty') ?></div><?php
} else {
?><form action="#">
<ul class="userFormList"><?php
for($i=0; $i<count($this->profiles); $i++) {
$profile = $this->profiles[$i];
?><li><label for="invite<?php echo $profile->id ?>"><?php
echo $this->element(
'picture.crop',
array('path' => $this->path,
'title' => $profile->getToolTipText(false),
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$profile->id],
'width' => 70,
'height' => 70)
);
?></label>
<input id="invite<?php echo $profile->id ?>" type="checkbox" name="profileId[]" value="<?php echo $profile->id ?>" />
<label for="invite<?php echo $profile->id ?>" class="text"><?php echo prepareHTML($profile->nickname) ?></label><?php
?></li><?php
}
?></ul>
<button type="submit"><?php echo gettext('event.invitation.submit') ?></button>
</form><?php
}