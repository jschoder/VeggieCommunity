<h1><?php echo gettext('menu.users'); ?></h1><?php
echo $this->element('tabs/user.search',
array('path' => $this->path,
'site' => $this->site,
'requestQuery' => $this->requestQuery));
?><form action="<?php echo $this->path ?>user/result/" method="get" accept-charset="UTF-8"><?php
if (   count($this->defaultSmoking) == 0
&& count($this->defaultAlcohol) == 0) {
$consumptionClass = 'collapsed';
$consumptionVisibility = ' style="display:none"';
} else {
$consumptionClass = 'collapsible';
$consumptionVisibility = '';
}
if (   count($this->defaultReligion) == 0
&& count($this->defaultZodiac) == 0
&& count($this->defaultPolitical) == 0)  {
$beliefClass = 'collapsed';
$beliefVisibility = ' style="display:none"';
} else {
$beliefClass = 'collapsible';
$beliefVisibility = '';
}
if (   count($this->defaultMarital) == 0
&& count($this->defaultChildren) == 0
&& count($this->defaultRelocate) == 0) {
$statusClass = 'collapsed';
$statusVisibility = ' style="display:none"';
} else {
$statusClass = 'collapsible';
$statusVisibility = '';
}
if (   count($this->defaultBodyType) == 0
&& count($this->defaultBodyHeight) == 0
&& count($this->defaultClothing) == 0
&& count($this->defaultHairColor) == 0
&& count($this->defaultEyeColor) == 0) {
$lookClass = 'collapsed';
$lookVisibility = ' style="display:none"';
} else {
$lookClass = 'collapsible';
$lookVisibility = '';
}
if (   $this->currentUser !== null
&& count($this->selectedHobbies) === 0) {
$hobbiesClass = 'collapsed';
$hobbiesVisibility = ' style="display:none"';
} else {
$hobbiesClass = 'collapsible';
$hobbiesVisibility = '';
}
?><section class="collapsible">
<h2><?php echo gettext("search.group.basic")?></h2>
<div>
<div class="thinCol">
<div><?php
echoField(
"search.field.gender",
"gender",
\vc\config\Fields::getGenderFields(),
$this->defaultGender,
false,
false);
?><div>&nbsp;</div><?php
?><h4><?php echo gettext("search.field.age")?></h4>
<div class="range"><?php
echoAgeCombo("age-from", $this->defaultAgeFrom);
echo ' - ';
echoAgeCombo("age-to", $this->defaultAgeTo);
?></div>
<div>&nbsp;</div>
<h4><?php echo gettext("search.field.searchtext")?></h4>
<input type="text" name="searchstring" maxlength="250" value="<?php echo $this->defaultSearchString?>" />
</div>
<div><?php
echoField(
"search.field.nutrition",
"nutrition",
\vc\config\Fields::getNutritionFields(),
$this->defaultNutrition,
false);
?></div>
<div><?php echoSearchFields($this->defaultSearch) ?></div>
</div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
?><section class="collapsible">
<h2><?php echo gettext("search.group.location")?></h2>
<div>
<div class="thinCol">
<div>
<?php
echoField(
"search.field.countries",
"country",
$this->countries,
$this->defaultCountries,
true);
?>
</div>
<div><?php
echoRegionField($this->regions, $this->defaultRegions);
?></div>
<div>
<?php
echoDistanceSearch($this->currentUser,
$this->defaultDistance,
$this->defaultDistanceUnit);
?>
</div>
</div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
?><section class="<?php echo $consumptionClass ?>">
<h2><?php echo gettext("search.group.consumption")?></h2>
<div<?php echo $consumptionVisibility ?>>
<div class="wideCol">
<div><?php
echoField(
"search.field.smoking",
"smoking",
\vc\config\Fields::getSmokingFields(),
$this->defaultSmoking,
false);
?></div>
<div>
<?php
echoField(
"search.field.alcohol",
"alcohol",
\vc\config\Fields::getAlcoholFields(),
$this->defaultAlcohol,
false);
?></div>
</div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
?><section class="<?php echo $beliefClass ?>">
<h2><?php echo gettext("search.group.belief")?></h2>
<div<?php echo $beliefVisibility ?>>
<div class="thinCol">
<div><?php
echoField(
"search.field.religion",
"religion",
\vc\config\Fields::getReligionFields(),
$this->defaultReligion,
false);
?></div>
<div><?php
echoField(
"search.field.zodiac",
"zodiac",
\vc\config\Fields::getZodiacFields(),
$this->defaultZodiac,
false);
?></div>
<div><?php
echoField(
"search.field.political",
"political",
\vc\config\Fields::getPoliticalFields(),
$this->defaultPolitical,
false);
?></div>
</div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
?><section class="<?php echo $statusClass ?>">
<h2><?php echo gettext("search.group.familystatus")?></h2>
<div<?php echo $statusVisibility ?>>
<div class="thinCol">
<div><?php
echoField(
"search.field.marital",
"marital",
\vc\config\Fields::getMaritalFields(),
$this->defaultMarital,
false);
?></div>
<div><?php
echoField(
"search.field.children",
"children",
\vc\config\Fields::getChildrenFields(),
$this->defaultChildren,
false);
?></div>
<div><?php
echoField(
"search.field.relocate",
"relocate",
\vc\config\Fields::getRelocateFields(),
$this->defaultRelocate,
false);
?></div>
</div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
?><section class="<?php echo $lookClass ?>">
<h2><?php echo gettext("search.group.look")?></h2>
<div<?php echo $lookVisibility ?>>
<div class="thinCol big">
<div><?php
echoField(
"search.field.bodytype",
"bodytype",
\vc\config\Fields::getBodyTypeFields(),
$this->defaultBodyType,
false);
?></div>
<div><?php
echoField(
"search.field.bodyheight",
"bodyheight",
\vc\config\Fields::getBodyHeightFields(),
$this->defaultBodyHeight,
true);
?></div>
<div><?php
echoField(
"search.field.clothing",
"clothing",
\vc\config\Fields::getClothingFields(),
$this->defaultClothing,
true);
?></div>
<div><?php
echoField(
"search.field.haircolor",
"haircolor",
\vc\config\Fields::getHairColorFields(),
$this->defaultHairColor,
false);
?></div>
<div><?php
echoField(
"search.field.eyecolor",
"eyecolor",
\vc\config\Fields::getEyeColorFields(),
$this->defaultEyeColor,
false);
?></div>
</div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
if ($this->currentUser !== null):
?><section class="<?php echo $hobbiesClass ?>">
<h2><?php echo gettext("search.group.hobbies")?></h2>
<div<?php echo $hobbiesVisibility ?>>
<div class="thinCol big"><?php
foreach ($this->hobbies as $groupId => $groupValues):
?><div>
<h3><?php echo $groupValues['title'] ?></h3>
<ul class="scroll"><?php
foreach ($groupValues['hobbies'] as $hobbyId => $hobbyName):
?><li class="hobby"<?php
?>><input class="checkbox" id="hobby<?php echo $hobbyId?>" name="hobbies[]" value="<?php echo $hobbyId ?>" type="checkbox"<?php
if (in_array($hobbyId, $this->selectedHobbies)) {
?> checked="checked" <?php
}
?>/>
<label for="hobby<?php echo $hobbyId?>"><?php echo prepareHTML($hobbyName, false)?></label><?php
?></li><?php
endforeach;
?></ul>
</div><?php
endforeach;
?></div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
endif;
?><section class="collapsible">
<h2><?php echo gettext('search.group.sort')?></h2>
<div>
<div class="wideCol">
<div>
<h3><?php echo gettext("search.field.filter")?></h3>
<ul>
<li><input class="check" id="photofilter" name="photofilter" value="true" type="checkbox" <?php
if ($this->filterPhoto) {
?> checked="checked"<?php
}
?>/>
<label for="photofilter"><?php echo gettext("search.filter.photo")?></label></li>
<li><input class="check" id="textfilter" name="textfilter" value="true" type="checkbox" <?php
if ($this->filterText) {
?> checked="checked"<?php
}
?>/>
<label for="textfilter"><?php echo gettext("search.filter.text")?></label></li><?php
if ($this->currentUser !== null) {
?><li><input class="check" id="preferencefilter" name="preferencefilter" value="true" type="checkbox" <?php
if ($this->filterPreference) {
?> checked="checked"<?php
}
?>/>
<label for="preferencefilter" title="<?php echo gettext('search.filter.preference.title') ?>"><?php echo gettext("search.filter.preference")?></label></li><?php
?><li><input class="check" id="ignoreEmptyAgeRange" name="ignoreEmptyAgeRange" value="true" type="checkbox" <?php
if ($this->filterIgnoreEmptyAgeRange) {
?> checked="checked"<?php
}
?>/>
<label for="ignoreEmptyAgeRange"><?php echo gettext("search.filter.ignoreEmptyAgeRange")?></label></li><?php
if ($this->hasMatching):
?><li>
<select name="matchingfilter" size="1"><?php
foreach (array(0, 50, 60, 70) as $value):
?><option value="<?php echo $value ?>"<?php if ($value == $this->matchingPreference) { echo ' selected="selected"'; } ?>><?php
if ($value === 0):
echo gettext('search.filter.matching.all');
else:
echo sprintf(gettext('search.filter.matching'), $value);
endif;
?></option><?php
endforeach;
?></select>
</li><?php
endif;
}
?></ul>
</div>
<div>
<h3><?php echo gettext("search.field.sort")?></h3>
<ul>
<li><input class="radio" id="lastloginsort" name="sort" type="radio" value="last_login"<?php
if ($this->sortByLastLogin) {
?> checked="checked"<?php
}
?>/>
<label for="lastloginsort"><?php echo gettext("search.sort.lastlogin")?></label></li>
<li><input class="radio" id="lastupdatesort" name="sort" type="radio" value="last_update"<?php
if ($this->sortByLastUpdate)  {
?> checked="checked"<?php
}
?>/>
<label for="lastupdatesort"><?php echo gettext("search.sort.lastupdate")?></label></li>
<li><input class="radio" id="firstentrysort" name="sort" type="radio" value="first_entry"<?php
if ($this->sortByFirstEntry) {
?> checked="checked"<?php
}
?>/>
<label for="firstentrysort"><?php echo gettext("search.sort.firstentry")?></label></li><?php
?></ul>
</div>
</div>
<div>
<button type="submit"><?php echo gettext("search.submit")?></button>
<?php if(isset($this->referer)):
?><a href="<?php echo $this->referer ?>"><?php echo gettext('form.cancel') ?></a><?php
endif; ?>
</div>
</div>
</section><?php
?></form><?php
$this->echoWideAd($this->locale, $this->plusLevel);
$this->addScript(
'printSearchRegionCombo();'
);
function echoField($caption, $name, $values, $defaultValues, $scroll, $showall=true)
{
$usedDefaultFields = array();
?><h3><?php echo gettext($caption)?></h3><?php
?><ul<?php if ($scroll) { ?> class="scroll"<?php } ?>><?php
if ($showall) {
?><li><input class="check" id="<?php echo $name?>.all" type="checkbox" onfocus="blur()"<?php
if (count($defaultValues) === 0) {
?> checked="checked"<?php
}
if ($name == "country") {
?> onchange="selectAll('<?php echo $name?>');printSearchRegionCombo()"<?php
} else {
?> onchange="selectAll('<?php echo $name?>')"<?php
}
?>/> <label for="<?php echo $name?>.all"><?php echo gettext('search.showall')?></label></li><?php
}
foreach ($values as $value=>$caption) {
?><li><?php
// Value multiple in same array []{$key, $value} instead of [$key][$value]
if (is_array($caption)) {
$value = $caption[0];
$caption = $caption[1];
}
$id = $name . "." . $value;
if ($value === 0 && empty($caption)) {
?><hr /><?php
} else {
?><input class="check" id="<?php echo $id?>" name="<?php echo $name?>[]" value="<?php echo $value?>"
type="checkbox"<?php
if (in_array($value, $defaultValues) && !in_array($value, $usedDefaultFields)) {
?> checked="checked"<?php
$usedDefaultFields[] = $value;
}
if ($name == "country") {
?> onchange="deselectAll('<?php echo $name?>');printSearchRegionCombo()" onfocus="blur()"<?php
} elseif ($showall) {
?> onchange="deselectAll('<?php echo $name?>')" onfocus="blur()"<?php
}
?>/> <label for="<?php echo $id?>"><?php echo $caption?></label><?php
}
?></li><?php
}
?></ul><?php
}
function echoAgeCombo($id, $selection)
{
?><select id="<?php echo $id?>" name="<?php echo $id?>" size="1"><?php
for($value=8;$value<=120;$value++) {
if ($value == $selection) {
?><option selected="selected"><?php echo $value?></option><?php
} else {
?><option><?php echo $value?></option><?php
}
}
?></select><?php
}
function echoSearchFields($search)
{
?><h3><?php echo gettext("search.field.search")?></h3><?php
?><div class="searchscroll">
<ul class="searching"><?php
$openGroup = null;
foreach (\vc\config\Fields::getSearchFields() as $key => $value):
if ($key < 20):
?><li>
<label for="<?php echo 'search-' . $key ?>"><?php echo prepareHTML($value) ?></label>
<input id="<?php echo 'search-' . $key ?>" name="search[]" type="checkbox" value="<?php echo $key ?>" <?php if($search && in_array($key, $search)) { ?>checked="checked" <?php } ?>/>
<label for="<?php echo 'search-' . $key ?>"></label>
</li><?php
else:
$label = explode('(', str_replace(')', '', $value));
$group = trim($label[0]);
$groupItem = trim($label[1]);
if ($openGroup !== $group):
if ($openGroup !== null):
?></div></li><?php
endif;
?><li>
<label for="<?php echo 'search-' . $key ?>"><?php echo prepareHTML($group) ?></label>
<div class="group"><?php
$openGroup = $group;
endif;
?><input id="<?php echo 'search-' . $key ?>" name="search[]" type="checkbox" value="<?php echo $key ?>" <?php if($search && in_array($key, $search)) { ?>checked="checked" <?php } ?>/>
<label for="<?php echo 'search-' . $key ?>"><?php echo prepareHTML($groupItem) ?></label><?php
endif;
endforeach;
if ($openGroup !== null):
?></div></li><?php
endif;
?></ul>
</div><?php
}
function echoRegionField($regionParams, $defaultRegions)
{
?><h4><?php echo gettext("search.field.regions")?></h4><?php
?><div class="scroll"><?php
foreach ($regionParams as $country => $regions) {
?><div id="regiongroup<?php echo $country?>"><?php
foreach ($regions as $regionKey => $region) {
?><input class="check" id="region.<?php echo $country . '.' . $regionKey ?>" name="region[]" value="<?php echo $region?>" type="checkbox"<?php
if (in_array($region, $defaultRegions)) {
?> checked="checked"<?php
}
?>/> <label for="region.<?php echo $country . '.' . $regionKey  ?>"><?php echo $region?></label><br /><?php
}
?></div><?php
}
?></div><?php
}
function echoDistanceSearch($currentUser, $defaultDistance, $defaultDistanceUnit)
{
?><h4><?php echo gettext("search.field.distance")?></h4><?php
?><div class="distance"><?php
if ($currentUser === null):
?><div><?php echo gettext("search.field.distance.noactivesession")?></div><?php
elseif ($currentUser->latitude  == 0 && $currentUser->longitude == 0):
?><div><?php echo gettext("search.field.distance.nocoordinates")?></div><?php
else:
?><div><?php
?><select id="distance" name="distance" size="1" style="width:5em"><?php
foreach (array(0, 1, 2, 5, 10, 25, 50, 75, 100, 250, 500, 1000) as $value):
if ($value == 0):
$caption = gettext("search.field.distance.all");
else:
$caption = $value;
endif;
if ($value == $defaultDistance):
?><option selected="selected" value="<?php echo $value?>"><?php echo $caption?></option><?php
else:
?><option value="<?php echo $value?>"><?php echo $caption?></option><?php
endif;
endforeach;
?></select>
<select id="distanceunit" name="distanceunit" size="1" style="width:4em"><?php
foreach (array("km", "mi") as $value):
if ($value == $defaultDistanceUnit):
?><option selected="selected"><?php echo $value?></option><?php
else:
?><option><?php echo $value?></option><?php
endif;
endforeach;
?></select><?php
?></div><?php
endif;
?></div><?php
}