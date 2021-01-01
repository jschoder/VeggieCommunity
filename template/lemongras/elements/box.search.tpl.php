<div id="searching">
<h2><?php echo gettext("box.search.title")?></h2>
<div class="fields">
<form action="<?php echo $this->path ?>user/result/" method="get" accept-charset="UTF-8">
<table>
<colgroup>
<col width="100" />
<col width="130" />
</colgroup><tr>
<td class="caption"><label for="search-nutrition"><?php echo gettext("box.search.nutrition")?> </label></td>
<td class="field"><select class="text130" id="search-nutrition" name="nutrition[]" size="1"><?php
foreach (\vc\config\Fields::getNutritionFields() as $value=>$caption)
{
// vegetarian
if ($value == 3)
{
?><option value="<?php echo $value?>" selected="selected"><?php echo $caption?></option><?php
}
else
{
?><option value="<?php echo $value?>"><?php echo $caption?></option><?php
}
}
?></select></td>
</tr><tr>
<td class="caption"><label for="search-searchstring"><?php echo gettext("box.search.searchstring")?> </label></td>
<td class="field"><input id="search-searchstring" class="text130" type="text" name="searchstring" maxlength="250" /></td>
</tr><tr>
<td class="caption"><label for="search-country"><?php echo gettext("box.search.country")?> </label></td>
<td class="field"><select class="text130" id="search-country" name="country[]" size="1"><?php
foreach ($this->countries as $country)
{
$value = $country[0];
$caption = $country[1];
// :TODO:  defaultCountry (currently selected always false)
if ($value != $value)
{
?><option value="<?php echo $value?>" selected="selected"><?php echo $caption?></option><?php
}
else
{
?><option value="<?php echo $value?>"><?php echo $caption?></option><?php
}
}
?><option value="0"><?php echo gettext("box.search.country.all")?></option><?php
?></select></td>
</tr></table>
<div class="buttons">
<button type="submit"><?php echo gettext("box.search.confirm")?></button>
</div>
</form>
</div>
<div class="footline">
<a class="link" href="<?php echo $this->path ?>search/"><?php echo gettext("box.search.extended")?></a>
</div>
</div>