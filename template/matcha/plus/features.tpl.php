<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('plus.features.title') ?></h1><?php
echo $this->element('tabs/plus',
array('path' => $this->path,
'site' => $this->site));
?><div class="notifyInfo"><?php echo gettext('plus.features.infotext') ?></div>
<table class="wide">
<thead>
<tr>
<th><?php echo gettext('plus.features.functionality') ?></th>
<th><?php echo gettext('plus.features.default') ?></th>
<th><?php echo gettext('plus.package.xs') ?></th>
<th><?php echo gettext('plus.package.m') ?></th>
</tr>
</thead>
<tbody>
<tr>
<td><?php echo gettext('plus.features.price') ?></td>
<td class="feature"><?php echo gettext('plus.features.price.free') ?></td>
<td class="feature"><?php echo str_replace('/', '<br />/', gettext('plus.package.xs.price')) ?></td>
<td class="feature"><?php echo str_replace('/', '<br />/',gettext('plus.package.m.price')) ?></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.maxPictures') ?></td>
<td class="feature"><?php echo vc\config\Globals::MAX_PICTURES_DEFAULT ?></td>
<td class="feature"><?php echo vc\config\Globals::MAX_PICTURES_DEFAULT ?></td>
<td class="feature"><?php echo vc\config\Globals::MAX_PICTURES_PLUS ?></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.pdfExport') ?></td>
<td class="feature missing" title="<?php echo gettext('plus.features.no') ?>"><span></span></td>
<td class="feature missing" title="<?php echo gettext('plus.features.no') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.pmFilter') ?></td>
<td class="feature missing" title="<?php echo gettext('plus.features.no') ?>"><span></span></td>
<td class="feature missing" title="<?php echo gettext('plus.features.no') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.sponsorMarker') ?></td>
<td class="feature missing" title="<?php echo gettext('plus.features.no') ?>"><span></span></td>
<td class="feature missing" title="<?php echo gettext('plus.features.no') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.adFree') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.no') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.pm') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.profiles') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.profileSearch') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.friends') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.favorites') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.groups') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.events') ?></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
<td class="feature present" title="<?php echo gettext('plus.features.yes') ?>"><span></span></td>
</tr>
</tbody>
</table>
