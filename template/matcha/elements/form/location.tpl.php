<li class="location<?php if ($this->mandatory) { echo ' mandatory'; } ?>">
<label for="<?php echo $this->id ?>"><?php echo $this->caption ?></label>
<input type="text" class="addresspicker<?php if(!empty($this->class)) { echo ' ' . $this->class; } ?>" id="<?php echo $this->id ?>" name="<?php echo $this->name ?>[caption]" value="<?php echo $this->escapeAttribute($this->default['caption']) ?>" />
<div class="addresspopup">
<ul>
<li class="<?php if ($this->mandatory) { echo ' mandatory'; } ?>">
<label for="<?php echo $this->id ?>-caption"><?php echo gettext('form.location.caption') ?></label>
<input type="text" id="<?php echo $this->id ?>-caption" name="<?php echo $this->name ?>[popcaption]" value="" />
</li>
<li>
<label for="<?php echo $this->id ?>-street"><?php echo gettext('form.location.street') ?></label>
<input type="text" id="<?php echo $this->id ?>-street" name="<?php echo $this->name ?>[street]" value="<?php echo $this->escapeAttribute($this->default['street']) ?>" />
</li>
<li>
<label for="<?php echo $this->id ?>-postal"><?php echo gettext('form.location.postal') ?></label>
<input type="text" id="<?php echo $this->id ?>-postal" name="<?php echo $this->name ?>[postal]" value="<?php echo $this->escapeAttribute($this->default['postal']) ?>" />
</li>
<li class="<?php if ($this->mandatory) { echo ' mandatory'; } ?>">
<label for="<?php echo $this->id ?>-city"><?php echo gettext('form.location.city') ?></label>
<input type="text" id="<?php echo $this->id ?>-city" name="<?php echo $this->name ?>[city]" value="<?php echo $this->escapeAttribute($this->default['city']) ?>" />
</li>
<li class="<?php if ($this->mandatory) { echo ' mandatory'; } ?>">
<label for="<?php echo $this->id ?>-country"><?php echo gettext('form.location.country') ?></label>
<select id="<?php echo $this->id ?>-country" name="<?php echo $this->name ?>[country]" data-filter-field="<?php echo $this->name ?>[region]"><?php
foreach ($this->countries as $key => $countryName):
?><option<?php
if ($key == $this->default['country']):
?> selected="selected"<?php
endif;
if (array_key_exists($key, $this->regions)):
?> data-filter-group="<?php echo $key ?>"<?php
endif;
?> value="<?php echo $key ?>"><?php
echo prepareHTML($countryName)
?></option><?php
endforeach;
?></select>
</li>
<li>
<label for="<?php echo $this->id ?>-region"><?php echo gettext('form.location.region') ?></label>
<select
id="<?php echo $this->id ?>-region"
name="<?php echo $this->name ?>[region]"
size="1"<?php
if(!empty($this->class)) { ?> class="<?php echo $this->class ?>"<?php }
if(!empty($this->filterField)) { ?> data-filter-field="<?php echo $this->filterField ?>"<?php }
?>><?php
foreach ($this->regions as $countryId => $regions):
?><optgroup id="<?php echo $this->id . '-region-g-' . $countryId ?>" label=""><?php
foreach ($regions as $regionId => $regionLabel):
?><option<?php
if ($this->default['region'] == $regionId):
?> selected="selected"<?php
endif;
?> value="<?php echo $regionId ?>"><?php
echo prepareHTML($regionLabel);
?></option><?php
endforeach;
?></optgroup><?php
endforeach;
?></select>
</li>
</ul>
</div>
<input type="hidden" name="<?php echo $this->name ?>[lat]" value="<?php echo $this->escapeAttribute($this->default['lat']) ?>" />
<input type="hidden" name="<?php echo $this->name ?>[lng]" value="<?php echo $this->escapeAttribute($this->default['lng']) ?>" /><?php
foreach ($this->validationErrors as $validationError) {
?><label class="error"><?php echo $validationError ?></label><?php
}
if (!empty($this->help)) {
?><label class="help"><?php echo prepareHTML($this->help) ?></label><?php
}
?></li>