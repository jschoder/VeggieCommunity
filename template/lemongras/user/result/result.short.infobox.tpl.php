<ul class="infoboxlisting"><?php
foreach ($this->profiles as $profile) {
?><li class="clearfix"><?php
?><a href="<?php echo $this->path ?>user/view/<?php echo $profile->id?>/" class="image"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$profile->id],
'width' => 35,
'height' => 35));
?></a><div><?php
?><a <?php if($profile->plusMarker) { echo 'class="plus" '; } ?>href="<?php echo $this->path ?>user/view/<?php echo $profile->id?>/"><?php echo $profile->nickname?></a><?php
?><p><?php
if ($profile->hideAge !== true) {
?><span><?php echo $profile->age?></span><?php
}
?><span><?php echo prepareHTML(\vc\config\Fields::getNutritionCaption($profile->nutrition, $profile->nutritionFreetext, $profile->gender))?></span><?php
?><span><?php echo prepareHTML($profile->getHtmlLocation()) ?></span><?php
?></p><?php
?></div><?php
?></li><?php
}
?></ul>