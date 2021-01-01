<div class="textblock">
<h1><?php echo gettext('confirmterms.title')?></h1><?php
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
echo '<p>' . $infotext . '</p>';
if (count($this->changes) > 0):
?><h2><?php echo gettext('confirmterms.changes.title')?></h2>
<ul class="list"><li><?php echo implode('</li><li>', $this->changes)?></li></ul><?php
endif;
$unregister = gettext('confirmterms.unregister');
$unregister = str_replace('%UNREGISTER_START%',
'<a class="link" href="' . $this->path . 'account/delete/" target="_blank">',
$unregister);
$unregister = str_replace('%UNREGISTER_END%',
'</a>',
$unregister);
echo '<p>' . $unregister . '</p>';
?><h2><?php echo gettext('confirmterms.termsofuse.title')?></h2>
<form action="<?php echo $this->path ?>account/confirmterms/" method="post" accept-charset="UTF-8">
<input name="terms_of_use_id" value="<?php echo $this->termsOfUse->id?>" type="hidden" />
<input name="privacy_policy_id" value="<?php echo $this->privacyPolicy->id?>" type="hidden" />
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
<p>
<button type="submit"><?php echo gettext('confirmterms.confirm')?></button>
</p>
</form>
</div>