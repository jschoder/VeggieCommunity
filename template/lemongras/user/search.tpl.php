<h1><?php echo gettext('menu.users'); ?></h1>
<?php
echo $this->element('tabs/user.search',
array('path' => $this->path,
'site' => $this->site,
'requestQuery' => $this->requestQuery));
?>
<div id="search">
<form action="<?php echo $this->path ?>user/result/" method="get" accept-charset="UTF-8">
<div class="searchbox">
<h2 id="slide-header-basic" class="openslide" onclick="setSlideVisible('basic', true)"><?php echo gettext("search.group.basic")?></h2>
<div id="slide-content-basic">
<div class="content clearfix">
<div class="three">
<?php
echoField(
"search.field.gender",
"gender",
\vc\config\Fields::getGenderFields(),
$this->defaultGender,
false,
false);
?><div>&nbsp;</div><?php
?><h4><?php echo gettext("search.field.age")?></h4>
<div><?php
?><label for="age-from"><?php echo gettext("search.field.age.from")?>&nbsp;</label><?php
echoAgeCombo("age-from", $this->defaultAgeFrom);
?>&nbsp;<label for="age-to"><?php echo gettext("search.field.age.to")?>&nbsp;</label><?php
echoAgeCombo("age-to", $this->defaultAgeTo);
?></div>
<div>&nbsp;</div>
<h4><?php echo gettext("search.field.searchtext")?></h4>
<input class="text130" type="text" name="searchstring" maxlength="250" value="<?php echo $this->defaultSearchString?>" />
</div>
<div class="three">
<?php
echoField(
"search.field.nutrition",
"nutrition",
\vc\config\Fields::getNutritionFields(),
$this->defaultNutrition,
false);
?>
</div>
<div class="three">
<?php echoSearchFields($this->defaultSearch);	?>
</div>
</div>
</div>
</div>
<div class="searchbox">
<h2 id="slide-header-location" class="openslide" onclick="setSlideVisible('location', true)"><?php echo gettext("search.group.location")?></h2>
<div id="slide-content-location">
<div class="content clearfix">
<div class="three">
<?php
echoField(
"search.field.countries",
"country",
$this->countries,
$this->defaultCountries,
true);
?>
</div>
<div class="three">
<?php echoRegionField($this->regions, $this->defaultRegions); ?>
</div>
<div class="three">
<?php
echoDistanceSearch($this->currentUser,
$this->defaultDistance,
$this->defaultDistanceUnit);
?>
</div>
</div>
</div>
</div>
<div class="buttonblock">
<button type="reset"><?php echo gettext("search.reset")?></button>
&nbsp;
<button type="submit"><?php echo gettext("search.submit")?></button>
</div>
<div class="searchbox">
<h2 id="slide-header-consumption" class="openslide" onclick="setSlideVisible('consumption', true)"><?php echo gettext("search.group.consumption")?></h2>
<div id="slide-content-consumption">
<div class="content clearfix">
<div class="two">
<?php
echoField(
"search.field.smoking",
"smoking",
\vc\config\Fields::getSmokingFields(),
$this->defaultSmoking,
false);
?>
</div>
<div class="two">
<?php
echoField(
"search.field.alcohol",
"alcohol",
\vc\config\Fields::getAlcoholFields(),
$this->defaultAlcohol,
false);
?>
</div>
</div>
</div>
</div>
<div class="searchbox">
<h2 id="slide-header-belief" class="openslide" onclick="setSlideVisible('belief', true)"><?php echo gettext("search.group.belief")?></h2>
<div id="slide-content-belief">
<div class="content clearfix">
<div class="three">
<?php
echoField(
"search.field.religion",
"religion",
\vc\config\Fields::getReligionFields(),
$this->defaultReligion,
false);
?>
</div>
<div class="three">
<?php
echoField(
"search.field.zodiac",
"zodiac",
\vc\config\Fields::getZodiacFields(),
$this->defaultZodiac,
false);
?>
</div>
<div class="three">
<?php
echoField(
"search.field.political",
"political",
\vc\config\Fields::getPoliticalFields(),
$this->defaultPolitical,
false);
?>
</div>
</div>
</div>
</div>
<div class="searchbox">
<h2 id="slide-header-familystatus" class="openslide" onclick="setSlideVisible('familystatus', true)"><?php echo gettext("search.group.familystatus")?></h2>
<div id="slide-content-familystatus">
<div class="content clearfix">
<div class="three">
<?php
echoField(
"search.field.marital",
"marital",
\vc\config\Fields::getMaritalFields(),
$this->defaultMarital,
false);
?>
</div>
<div class="three">
<?php
echoField(
"search.field.children",
"children",
\vc\config\Fields::getChildrenFields(),
$this->defaultChildren,
false);
?>
</div>
<div class="three">
<?php
echoField(
"search.field.relocate",
"relocate",
\vc\config\Fields::getRelocateFields(),
$this->defaultRelocate,
false);
?>
</div>
</div>
</div>
</div>
<div class="searchbox">
<h2 id="slide-header-look" class="openslide" onclick="setSlideVisible('look', true)"><?php echo gettext("search.group.look")?></h2>
<div id="slide-content-look">
<div class="content clearfix">
<div class="five">
<?php
echoField(
"search.field.bodytype",
"bodytype",
\vc\config\Fields::getBodyTypeFields(),
$this->defaultBodyType,
false);
?>
</div>
<div class="five">
<?php
echoField(
"search.field.bodyheight",
"bodyheight",
\vc\config\Fields::getBodyHeightFields(),
$this->defaultBodyHeight,
true);
?>
</div>
<div class="five">
<?php
echoField(
"search.field.clothing",
"clothing",
\vc\config\Fields::getClothingFields(),
$this->defaultClothing,
true);
?>
</div>
<div class="five">
<?php
echoField(
"search.field.haircolor",
"haircolor",
\vc\config\Fields::getHairColorFields(),
$this->defaultHairColor,
false);
?>
</div>
<div class="five">
<?php
echoField(
"search.field.eyecolor",
"eyecolor",
\vc\config\Fields::getEyeColorFields(),
$this->defaultEyeColor,
false);
?>
</div>
</div>
</div>
</div>
<?php
if ($this->currentUser !== null)
{
?><div class="searchbox">
<h2 id="slide-header-hobbies" class="openslide" onclick="setSlideVisible('hobbies', true)"><?php echo gettext("search.group.hobbies")?></h2>
<div id="slide-content-hobbies">
<div><?php
foreach ($this->hobbies as $groupId=>$groupValues) {
?><h4><?php echo $groupValues['title'] ?></h4><?php
?><ul class="clearfix"><?php
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
?></ul><?php
}
?></div>
</div>
</div><?php
}
?>
<div class="searchbox">
<h2 id="slide-header-sort" class="openslide" onclick="setSlideVisible('sort', true)"><?php echo gettext("search.group.sort")?></h2>
<div id="slide-content-sort">
<div class="content clearfix">
<div class="two">
<h4><?php echo gettext("search.field.filter")?></h4>
<input class="check" id="photofilter" name="photofilter" value="true" type="checkbox" <?php
if ($this->filterPhoto)
{
?> checked="checked"<?php
}
?>/>
<label for="photofilter"><?php echo gettext("search.filter.photo")?></label><br />
<input class="check" id="textfilter" name="textfilter" value="true" type="checkbox" <?php
if ($this->filterText)
{
?> checked="checked"<?php
}
?>/>
<label for="textfilter"><?php echo gettext("search.filter.text")?></label><?php
if ($this->currentUser !== null) {
?><br /><input class="check" id="preferencefilter" name="preferencefilter" value="true" type="checkbox" <?php
if ($this->filterPreference)
{
?> checked="checked"<?php
}
?>/>
<label for="preferencefilter" title="<?php echo gettext('search.filter.preference.title') ?>"><?php echo gettext("search.filter.preference")?></label><?php
?><br /><input class="check" id="ignoreEmptyAgeRange" name="ignoreEmptyAgeRange" value="true" type="checkbox" <?php
if ($this->filterIgnoreEmptyAgeRange)
{
?> checked="checked"<?php
}
?>/>
<label for="ignoreEmptyAgeRange"><?php echo gettext("search.filter.ignoreEmptyAgeRange")?></label><?php
if ($this->hasMatching):
?><br /><select name="matchingfilter" size="1"><?php
foreach (array(0, 50, 60, 70) as $value):
?><option value="<?php echo $value ?>"<?php if ($value == $this->matchingPreference) { echo ' selected="selected"'; } ?>><?php
if ($value === 0):
echo gettext('search.filter.matching.all');
else:
echo sprintf(gettext('search.filter.matching'), $value);
endif;
?></option><?php
endforeach;
?></select><?php
endif;
}
?></div>
<div class="two">
<h4><?php echo gettext("search.field.sort")?></h4>
<input class="radio" id="lastloginsort" name="sort" type="radio" value="last_login"<?php
if ($this->sortByLastLogin)
{
?> checked="checked"<?php
}
?>/>
<label for="lastloginsort"><?php echo gettext("search.sort.lastlogin")?></label><br />
<input class="radio" id="lastupdatesort" name="sort" type="radio" value="last_update"<?php
if ($this->sortByLastUpdate)
{
?> checked="checked"<?php
}
?>/>
<label for="lastupdatesort"><?php echo gettext("search.sort.lastupdate")?></label><br />
<input class="radio" id="firstentrysort" name="sort" type="radio" value="first_entry"<?php
if ($this->sortByFirstEntry)
{
?> checked="checked"<?php
}
?>/>
<label for="firstentrysort"><?php echo gettext("search.sort.firstentry")?></label><?php
?>
</div>
</div>
</div>
</div>
<div class="buttonblock">
<button type="reset"><?php echo gettext("search.reset")?></button>
&nbsp;
<button type="submit"><?php echo gettext("search.submit")?></button>
</div>
</form>
</div><?php
$this->addScript(
'printSearchRegionCombo();'
);
if (   count($this->defaultSmoking) == 0
&& count($this->defaultAlcohol) == 0) {
$this->addScript(
'setSlideVisible(\'consumption\');'
);
}
if (   count($this->defaultReligion) == 0
&& count($this->defaultZodiac) == 0
&& count($this->defaultPolitical) == 0)  {
$this->addScript(
'setSlideVisible(\'belief\');'
);
}
if (   count($this->defaultMarital) == 0
&& count($this->defaultChildren) == 0
&& count($this->defaultRelocate) == 0) {
$this->addScript(
'setSlideVisible(\'familystatus\');'
);
}
if (   count($this->defaultBodyType) == 0
&& count($this->defaultBodyHeight) == 0
&& count($this->defaultClothing) == 0
&& count($this->defaultHairColor) == 0
&& count($this->defaultEyeColor) == 0) {
$this->addScript(
'setSlideVisible(\'look\');'
);
}
if ($this->currentUser !== null &&
count($this->selectedHobbies) === 0) {
$this->addScript(
'setSlideVisible(\'hobbies\');'
);
}
//-----------------------------------------------------------------------------
function echoField($caption, $name, $values, $defaultValues, $scroll, $showall=true)
{
$usedDefaultFields = array();
?><h4><?php echo gettext($caption)?></h4><?php
?><div<?php if ($scroll) { ?> class="scroll"<?php } ?>><?php
if ($showall)
{
?><input class="check" id="<?php echo $name?>.all" type="checkbox" onfocus="blur()"<?php
if (count($defaultValues) === 0)
{
?> checked="checked"<?php
}
if ($name == "country")
{
?> onchange="selectAll('<?php echo $name?>');printSearchRegionCombo()"<?php
}
else
{
?> onchange="selectAll('<?php echo $name?>')"<?php
}
?>/> <label for="<?php echo $name?>.all"><?php echo gettext('search.showall')?></label><br /><?php
}
foreach ($values as $value=>$caption)
{
// Value multiple in same array []{$key, $value} instead of [$key][$value]
if (is_array($caption))
{
$value = $caption[0];
$caption = $caption[1];
}
$id = $name . "." . $value;
if ($value === 0 && empty($caption))
{
?><hr /><?php
}
else
{
?><input class="check" id="<?php echo $id?>" name="<?php echo $name?>[]" value="<?php echo $value?>"
type="checkbox"<?php
if (in_array($value, $defaultValues) && !in_array($value, $usedDefaultFields))
{
?> checked="checked"<?php
$usedDefaultFields[] = $value;
}
if ($name == "country")
{
?> onchange="deselectAll('<?php echo $name?>');printSearchRegionCombo()" onfocus="blur()"<?php
}
elseif ($showall)
{
?> onchange="deselectAll('<?php echo $name?>')" onfocus="blur()"<?php
}
?>/> <label for="<?php echo $id?>"><?php echo $caption?></label><br /><?php
}
}
?></div><?php
}
//-----------------------------------------------------------------------------
function echoAgeCombo($id, $selection)
{
?><select class="text" id="<?php echo $id?>" name="<?php echo $id?>" size="1"><?php
for($value=8;$value<=120;$value++)
{
if ($value == $selection)
{
?><option selected="selected"><?php echo $value?></option><?php
}
else
{
?><option><?php echo $value?></option><?php
}
}
?></select><?php
}
//-----------------------------------------------------------------------------
function echoSearchFields($search)
{
?><h4><?php echo gettext("search.field.search")?></h4><?php
?><div class="searchscroll"><?php
?><table><?php
?><tr><?php
?><td><label for="search.all"><?php echo gettext('search.showall')?></label></td><?php
?><td><?php
?><input class="check" id="search.all" type="checkbox"
onchange="selectAll('search')" onfocus="blur()"<?php
if (count($search) === 0)
{
?> checked="checked"<?php
}
?>/><?php
?></td><?php
?></tr><?php
echoSearchField("profile.search.penpal", $search, 1, 0, 0, 0);
echoSearchField("profile.search.pal", $search, 5, 0, 0, 0);
echoSearchField("profile.search.activist", $search, 10, 0, 0, 0);
echoSearchField("profile.search.letsee", $search, 0, 21, 25, 29);
echoSearchField("profile.search.date", $search, 0, 41, 45, 49);
echoSearchField("profile.search.relationship", $search, 0, 61, 65, 69);
echoSearchField("profile.search.polyamorous", $search, 0, 81, 85, 89);
echoSearchField("profile.search.marriage", $search, 0, 101, 105, 109);
?></table><?php
?></div><?php
}
//-----------------------------------------------------------------------------
function echoSearchField($label, $search, $globalID, $maleId, $femaleId, $queerId)
{
if ($globalID > 0) {
$defaultID = "search." . $globalID;
} else {
$defaultID = "search." . $maleId;
}
?><tr>
<td><label for="<?php echo $defaultID?>"><?php echo gettext($label)?></label></td>
<td><?php
if ($globalID > 0)
{
?><input class="checkbox" id="search.<?php echo $globalID?>" name="search[]" type="checkbox" <?php
if (in_array($globalID, $search)) {
?>checked="checked"<?php
}
?> value="<?php echo $globalID?>" onchange="deselectAll('search')" onfocus="blur()"/><?php
}
else
{
?><input class="checkbox" id="search.<?php echo $maleId ?>" name="search[]" type="checkbox" <?php
if (in_array($maleId, $search)) {
?>checked="checked"<?php
}
?> value="<?php echo $maleId?>" onchange="deselectAll('search')" onfocus="blur()"/>
<label for="search.<?php echo $maleId?>"
title="<?php echo gettext($label)?> (<?php echo gettext("profile.search.male")?>)"><?php
echo gettext("profile.search.male")
?></label><br />
<input class="checkbox" id="search.<?php echo $femaleId ?>" name="search[]" type="checkbox" <?php
if (in_array($femaleId, $search))  {
?>checked="checked"<?php
}
?> value="<?php echo $femaleId?>" onchange="deselectAll('search')" onfocus="blur()"/>
<label for="search.<?php echo $femaleId?>"
title="<?php echo gettext($label)?> (<?php echo gettext("profile.search.female")?>)"><?php
echo gettext("profile.search.female")
?></label><br />
<input class="checkbox" id="search.<?php echo $queerId ?>" name="search[]" type="checkbox" <?php
if (in_array($queerId, $search))  {
?>checked="checked"<?php
}
?> value="<?php echo $queerId?>" onchange="deselectAll('search')" onfocus="blur()"/>
<label for="search.<?php echo $queerId?>"
title="<?php echo gettext($label)?> (<?php echo gettext("profile.search.queer")?>)"><?php
echo gettext("profile.search.queer")
?></label><?php
}
?></td>
</tr><?php
}
//-----------------------------------------------------------------------------
function echoRegionField($regionParams, $defaultRegions)
{
?><h4><?php echo gettext("search.field.regions")?></h4><?php
?><div class="scroll"><?php
foreach ($regionParams as $country=>$regions)
{
?><div id="regiongroup<?php echo $country?>"><?php
foreach ($regions as $regionKey => $region)
{
?><input class="check" id="region.<?php echo $country . '.' . $regionKey ?>" name="region[]" value="<?php echo $region?>" type="checkbox"<?php
if (in_array($region, $defaultRegions))
{
?> checked="checked"<?php
}
?>/> <label for="region.<?php echo  $country . '.' . $regionKey ?>"><?php echo $region?></label><br /><?php
}
?></div><?php
}
?></div><?php
}
//-----------------------------------------------------------------------------
function echoDistanceSearch($currentUser, $defaultDistance, $defaultDistanceUnit)
{
?><h4><?php echo gettext("search.field.distance")?></h4><?php
?><div class="distance"><?php
if ($currentUser === null) {
?><div><?php echo gettext("search.field.distance.noactivesession")?></div><?php
}
elseif ($currentUser->latitude  == 0 && $currentUser->longitude == 0)
{
?><div><?php echo gettext("search.field.distance.nocoordinates")?></div><?php
}
else
{
?><div><?php
?><select class="text" id="distance" name="distance" size="1" style="width:5em"><?php
foreach (array(0, 1, 2, 5, 10, 25, 50, 75, 100, 250, 500, 1000) as $value)
{
if ($value == 0)
{
$caption = gettext("search.field.distance.all");
}
else
{
$caption = $value;
}
if ($value == $defaultDistance)
{
?><option selected="selected" value="<?php echo $value?>"><?php echo $caption?></option><?php
}
else
{
?><option value="<?php echo $value?>"><?php echo $caption?></option><?php
}
}
?></select>
<select class="text" id="distanceunit" name="distanceunit" size="1" style="width:4em"><?php
foreach (array("km", "mi") as $value)
{
if ($value == $defaultDistanceUnit)
{
?><option selected="selected"><?php echo $value?></option><?php
}
else
{
?><option><?php echo $value?></option><?php
}
}
?></select><?php
?></div><?php
}
?></div><?php
}