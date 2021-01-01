<h1><?php echo gettext('plus.paypal.confirm.title') ?></h1><?php
echo $this->element('tabs/plus',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><form action="<?php echo $this->path ?>plus/paypal/confirm/" method="post">
<input type="hidden" name="payment_id" value="<?php echo $this->payment->getId() ?>" />
<input type="hidden" name="payer_id" value="<?php echo $this->payerId ?>" />
<table class="infos"><?php
foreach ($this->payment->getTransactions() as $transaction) {
foreach ($transaction->getItemList()->getItems() as $item) {;
?><tr>
<td><?php echo gettext('plus.package') ?></td>
<td><?php echo gettext('plus.package.' . $item->getSku()) ?></td>
</tr>
<tr>
<td><?php echo gettext('plus.duration') ?></td>
<td><?php echo gettext('plus.duration.' . $item->getQuantity()) ?></td>
</tr><?php
}
?><tr>
<td><?php echo gettext('plus.paypal.confirm.total') ?></td>
<td><?php echo $transaction->getAmount()->getTotal() ?> &euro;</td>
</tr><?php
}
?><tr>
<td colspan="2"><button type="submit"><?php echo gettext('plus.paypal.confirm.confirm') ?></button></td>
</tr>
</table>
</form>