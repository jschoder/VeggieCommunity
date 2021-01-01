<h1><?php echo gettext('plus.history.title') ?></h1><?php
echo $this->element('tabs/plus',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
if (empty($this->plusObjects)) {
?><div class="notifyInfo"><?php echo gettext('plus.history.empty') ?></div><?php
} else {
?><table class="infos">
<tr>
<th><?php echo gettext('plus.history.plusType') ?></th>
<th><?php echo gettext('plus.history.from') ?></th>
<th><?php echo gettext('plus.history.to') ?></th>
<th><?php echo gettext('plus.history.paymentType') ?></th>
</tr><?php
foreach ($this->plusObjects as $plusObject) {
$package = \vc\object\Plus::$packages[$plusObject->plusType];
$startDate = strtotime($plusObject->startDate);
$endDate = strtotime($plusObject->endDate);
?><tr<?php if ($startDate < time() && $endDate > time()) { echo ' class="active"'; } ?>>
<td><?php echo gettext('plus.package.' . $package[0]) ?></td>
<td><?php echo date(gettext('plus.history.dateformat'), $startDate) ?></td>
<td><?php echo date(gettext('plus.history.dateformat'), $endDate) ?></td>
<td><?php
switch($plusObject->paymentType) {
case \vc\object\Plus::PAYMENT_TYPE_TRIAL:
echo gettext('plus.history.paymentType.trial');
break;
case \vc\object\Plus::PAYMENT_TYPE_GIFT:
echo gettext('plus.history.paymentType.gift');
break;
case \vc\object\Plus::PAYMENT_TYPE_RESTVALUE:
echo gettext('plus.history.paymentType.restvalue');
break;
case \vc\object\Plus::PAYMENT_TYPE_PAYPAL:
echo gettext('plus.history.paymentType.paypal');
break;
case \vc\object\Plus::PAYMENT_TYPE_PAYSAFECARD:
echo gettext('plus.history.paymentType.paysafecard');
break;
case \vc\object\Plus::PAYMENT_TYPE_SOFORT:
echo gettext('plus.history.paymentType.sofort');
break;
}
?></td>
</tr><?php
}
?></table><?php
}