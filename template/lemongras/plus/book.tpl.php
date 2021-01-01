<h1><?php echo gettext('plus.book.title') ?></h1><?php
echo $this->element('tabs/plus',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}

if (!empty($this->locale) && $this->locale === 'de'): ?>
<p>
    Die Seite wird zum 1. Januar abgeschalten. Alle gebuchten Plus-Accounts verfallen zu diesem Zeitpunkt. Eine Rückzahlung ist nicht vorgesehen. Wenn du dich bei uns für die Seite bedanken willst kannst du uns einen Gutschein für Steam an die Support-Adresse support@veggiecommunity.org schicken. 
</p>
<?php
endif;
if (!empty($this->locale) && $this->locale === 'en'): ?>
<p>
    The site will be deactivated on January 1st. All booked Plus accounts will expire at this point. A repayment is not planned. If you want to thank us for the page you can send us a voucher for Steam to the support address support@veggiecommunity.org.
</p>
<?php
endif;


?><p><?php echo gettext('plus.features.infotext') ?></p>
<div class="plusForm">
<div class="paypal">
<h2 title="<?php echo gettext('plus.paypal') ?>"><img src="<?php echo $this->imagesPath ?>paypal-200px.png"  alt="<?php echo gettext('plus.paypal') ?>" /></h2><?php
/*
?><h3><?php echo gettext('plus.abo') ?></h3><?php
if ($this->paypalActive) {
?><div class="notifyError"><?php echo gettext('plus.notavailable') ?></div><?php
} else {
?><div class="notifyError"><?php echo gettext('plus.notavailable') ?></div><?php
}
*/
?><h3><?php echo gettext('plus.single') ?></h3><?php
if ($this->paypalActive) {
?><form action="<?php echo $this->path ?>plus/paypal/checkout/" method="post">
<dl>
<dt><?php echo gettext('plus.package') ?></dt>
<dd>
<select name="package"><?php
foreach ($this->plusPackages as $package) {
$label = gettext('plus.package.' . $package[0]) . ' (' . gettext('plus.package.' . $package[0] . '.price') . ')';
if ($package[0] == 'm') {
?><option selected="selected" value="<?php echo $package[0] ?>"><?php echo $label ?></option><?php
} else {
?><option value="<?php echo $package[0] ?>"><?php echo $label ?></option><?php
}
}
?></select>
</dd>
<dt><?php echo gettext('plus.duration') ?></dt>
<dd>
<select name="duration">
<option value="1"><?php echo gettext('plus.duration.1') ?></option>
<option value="2"><?php echo gettext('plus.duration.2') ?></option>
<option value="3"><?php echo gettext('plus.duration.3') ?></option>
<option value="6" selected="selected"><?php echo gettext('plus.duration.6') ?></option>
<option value="12"><?php echo gettext('plus.duration.12') ?></option>
<option value="18"><?php echo gettext('plus.duration.18') ?></option>
<option value="24"><?php echo gettext('plus.duration.24') ?></option>
</select>
</dd>
<dt>
<button type="submit"><?php echo gettext('plus.paypal.submit') ?></button>
</dt>
</dl>
</form><?php
} else {
?><div class="notifyError"><?php echo gettext('plus.notavailable') ?></div><?php
}
?></div>
</div>
