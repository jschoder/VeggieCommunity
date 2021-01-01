<div class="infotext"><?php
?><h2><?php echo gettext('confirmterms.title')?></h2><?php
?><div><?php
$infotext = gettext('confirmterms.info');
$infotext = str_replace('%TERMS_START%',
'<a class="link" href="' . $this->path . 'account/termsofservice/" target="_blank">',
$infotext);
$infotext = str_replace('%TERMS_END%',
'</a>',
$infotext);
$infotext = str_replace('%PRIVACY_START%',
'<a class="link" href="' . $this->path . 'account/privacypolicy/" target="_blank">',
$infotext);
$infotext = str_replace('%PRIVACY_END%',
'</a>',
$infotext);
echo $infotext;
?></div><?php
?><div>&nbsp;</div><?php
if (count($this->changes) > 0)
{
?><h2><?php echo gettext('confirmterms.changes.title')?></h2><?php
?><ul><li><?php echo implode('</li><li>', $this->changes)?></li></ul><?php
?><div>&nbsp;</div><?php
}
?><div><?php
$unregister = gettext('confirmterms.unregister');
$unregister = str_replace('%UNREGISTER_START%',
'<a class="link" href="' . $this->path . 'account/delete/" target="_blank">',
$unregister);
$unregister = str_replace('%UNREGISTER_END%',
'</a>',
$unregister);
echo $unregister;
?></div><?php
?><div>&nbsp;</div><?php
?><h2><?php echo gettext('confirmterms.termsofuse.title')?></h2><?php
?><form action="<?php echo $this->path ?>account/confirmterms/" method="post" accept-charset="UTF-8"><?php
?><input name="terms_of_use_id" value="<?php echo $this->termsOfUse->id?>" type="hidden" /><?php
?><input name="privacy_policy_id" value="<?php echo $this->privacyPolicy->id?>" type="hidden" />
<p><?php
echo str_replace(
array(
'%BUTTON%',
'%TERMS_START%',
'%TERMS_END%',
'%PRIVACY_START%',
'%PRIVACY_END%'
),
array(
gettext('confirmterms.confirm'),
'<a class="link" rel="nofollow" href="' . $this->path . 'account/termsofservice/" target="_blank">',
'</a>',
'<a class="link" rel="nofollow" href="' . $this->path . 'account/privacypolicy/" target="_blank">',
'</a>'
),
gettext('confirmterms.termsofuse')
);
?></p>
<div class="confirm-button"><button type="submit"><?php echo gettext("confirmterms.confirm")?></button></div><?php
?></form><?php
?></div>