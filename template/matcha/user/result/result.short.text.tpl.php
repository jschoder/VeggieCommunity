<ul class="followed list"><?php
foreach ($this->profiles as $profile):
?><li><?php
$class = array();
if ($profile->realMarker):
$class[] = 'real';
endif;
if ($profile->plusMarker):
$class[] = 'plus';
endif;
?><a <?php if(!empty($class)) { echo 'class="' . implode(' ', $class) . '" '; }  ?>href="<?php echo $this->path ?>user/view/<?php echo $profile->id?>/" title="<?php echo prepareHTML($profile->getToolTipText()) ?>"><?php echo $profile->nickname?></a><?php
if ($profile->hideAge !== true):
?>&nbsp;<span>(<?php echo $profile->age?>)</span><?php
endif;
?>, <?php echo prepareHTML(\vc\config\Fields::getNutritionCaption($profile->nutrition, $profile->nutritionFreetext, $profile->gender))?><?php
?></li><?php
endforeach;
?></ul>