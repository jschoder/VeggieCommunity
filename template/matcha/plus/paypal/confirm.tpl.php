<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('plus.paypal.confirm.title') ?></h1><?php
echo $this->element('tabs/plus',
array('path' => $this->path,
'site' => $this->site));
?><form action="<?php echo $this->path ?>plus/paypal/confirm/" method="post">
<input type="hidden" name="payment_id" value="<?php echo $this->payment->getId() ?>" />
<input type="hidden" name="payer_id" value="<?php echo $this->payerId ?>" />
<dl><?php
foreach ($this->payment->getTransactions() as $transaction):
foreach ($transaction->getItemList()->getItems() as $item):
?><dt><?php echo gettext('plus.package') ?></dt>
<dd><?php echo gettext('plus.package.' . $item->getSku()) ?></dd>
<dt><?php echo gettext('plus.duration') ?></dt>
<dd><?php echo gettext('plus.duration.' . $item->getQuantity()) ?></dd><?php
endforeach;
?><dt><?php echo gettext('plus.paypal.confirm.total') ?></dt>
<dd><?php echo $transaction->getAmount()->getTotal() ?> &euro;</dd><?php
endforeach;
?><dt><button type="submit"><?php echo gettext('plus.paypal.confirm.confirm') ?></dt><dd></dd>
</dl>
</form>