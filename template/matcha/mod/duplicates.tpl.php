<h1>Duplicate Ips</h1><?php
if (!empty($this->duplicates)) {
?><table class="mod"><?php
foreach ($this->duplicates as $profileId1 => $profileIds) {
foreach ($profileIds as $profileId2 => $days) {
?><tr>
<td><a href="https://www.veggiecommunity.org/de/user/view/<?php echo $profileId1 ?>/mod/"><?php echo $profileId1 ?></a></td>
<td><a href="https://www.veggiecommunity.org/de/user/view/<?php echo $profileId2 ?>/mod/"><?php echo $profileId2 ?></a></td>
<td><?php echo implode(', ', $days) ?></td>
</tr><?php
}
}
?></table><?php
}