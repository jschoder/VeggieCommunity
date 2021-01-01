<ul class="textlisting"><?php
foreach ($this->profiles as $profile) {
?><li class="clearfix"><?php
?><a <?php if($profile->plusMarker) { echo 'class="plus" '; } ?>href="<?php echo $this->path ?>user/view/<?php echo $profile->id ?>/" title="<?php echo prepareHTML($profile->getToolTipText()) ?>"><?php echo $profile->nickname?></a><?php
if ($profile->hideAge !== true) {
?>&nbsp;<span>(<?php echo $profile->age?>)</span><?php
}
?>, <span><?php echo prepareHTML(\vc\config\Fields::getNutritionCaption($profile->nutrition, $profile->nutritionFreetext, $profile->gender))?></span><?php
?></li><?php
}
?></ul>