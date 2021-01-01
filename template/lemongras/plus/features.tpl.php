<h1><?php echo gettext('plus.features.title') ?></h1><?php
echo $this->element('tabs/plus',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><p><?php echo gettext('plus.features.infotext') ?></p>
<table class="infos">
<tr>
<th><?php echo gettext('plus.features.functionality') ?></th>
<th><?php echo gettext('plus.features.default') ?></th>
<th><?php echo gettext('plus.package.xs') ?></th>
<th><?php echo gettext('plus.package.m') ?></th>
</tr>
<tr>
<td><?php echo gettext('plus.features.price') ?></td>
<td class="feature"><?php echo gettext('plus.features.price.free') ?></td>
<td class="feature"><?php echo gettext('plus.package.xs.price') ?></td>
<td class="feature"><?php echo gettext('plus.package.m.price') ?></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.maxPictures') ?></td>
<td class="feature"><?php echo vc\config\Globals::MAX_PICTURES_DEFAULT ?></td>
<td class="feature"><?php echo vc\config\Globals::MAX_PICTURES_DEFAULT ?></td>
<td class="feature"><?php echo vc\config\Globals::MAX_PICTURES_PLUS ?></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.pdfExport') ?></td>
<td class="feature"><span class="fa fa-missing"></span></td>
<td class="feature"><span class="fa fa-missing"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.pmFilter') ?></td>
<td class="feature"><span class="fa fa-missing"></span></td>
<td class="feature"><span class="fa fa-missing"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.sponsorMarker') ?></td>
<td class="feature"><span class="fa fa-missing"></span></td>
<td class="feature"><span class="fa fa-missing"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.adFree') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.pm') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.profiles') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.profileSearch') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.friends') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.favorites') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.groups') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
<tr>
<td><?php echo gettext('plus.features.events') ?></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
<td class="feature"><span class="fa fa-present"></span></td>
</tr>
</table>
