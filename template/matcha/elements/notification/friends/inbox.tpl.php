<li class="new">
<a href="<?php echo $this->path ?>friend/list/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->picture));
?><strong><?php echo prepareHTML($this->profile->nickname); ?></strong>
<ul class="h"><?php
if ($this->profile->hideAge !== true) {
?><li><?php echo $this->profile->age?></li><?php
}
if ($this->profile->gender == 4) {
?><li title="<?php echo gettext('profile.gender.female')?>"><span class="female"></span></li><?php
} elseif ($this->profile->gender == 2) {
?><li title="<?php echo gettext('profile.gender.male')?>"><span class="male"></span></li><?php
} elseif ($this->profile->gender == 6) {
?><li title="<?php echo gettext('profile.gender.other')?>"><span class="queer"></span></li><?php
}
?><li><?php
echo prepareHTML(\vc\config\Fields::getNutritionCaption($this->profile->nutrition, null, $this->profile->gender));
?></li>
<li title="<?php echo prepareHTML($this->profile->getHtmlLocation()) ?>"><?php
echo prepareHTML($this->profile->getHtmlLocation(true, $this->currentUser))
?></li>
</ul>
</a>
</li>