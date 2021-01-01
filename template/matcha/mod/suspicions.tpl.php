<?php
$suspicionTypeConstants = \vc\helper\ConstantHelper::getConstants(
'vc\\model\\db\\SuspicionDbModel',
array(),
'TYPE_'
);
?><div>
<form accept-charset="UTF-8" action="<?php echo $this->path ?>mod/suspicions/" class="formHighlight horizontal" method="get">
<ul>
<li>
<select name="type">
<option value="0">All</option><?php
foreach ($suspicionTypeConstants as $key => $value):
if ($key === $this->filterType):
?><option value="<?php echo $key ?>" selected="selected"><?php echo $value ?></option><?php
else:
?><option value="<?php echo $key ?>"><?php echo $value ?></option><?php
endif;
endforeach;
?></select>
</li>
<li><input id="user" name="user" placeholder="user" type="text" value="<?php echo $this->filterUser ?>" maxlength="10"/></li>
<li><input id="ip" name="ip" placeholder="ip" type="text" value="<?php echo $this->filterIp ?>" maxlength="16"/></li>
<li>
<select name="timeframe"><?php
$timeframeOptions = array(
'all' => 'all',
'24h' => '24 hours',
'7d' => '7 days',
'1m' => '1 month',
'3m' => '3 months',
);
foreach ($timeframeOptions as $key => $value):
if ($key === $this->filterTimeframe):
?><option value="<?php echo $key ?>" selected="selected"><?php echo $value ?></option><?php
else:
?><option value="<?php echo $key ?>"><?php echo $value ?></option><?php
endif;
endforeach;
?></select>
</li>
<li>
<select name="limit"><?php
$limitOptions = array(
'1000' => '1000',
'5000' => '5000',
'all' => 'all',
);
foreach ($limitOptions as $key => $value):
if ($key == $this->filterLimit):
?><option value="<?php echo $key ?>" selected="selected"><?php echo $value ?></option><?php
else:
?><option value="<?php echo $key ?>"><?php echo $value ?></option><?php
endif;
endforeach;
?></select>
</li>
<li><button class="submit" type="submit">Filter</button></li>
</ul>
</form>
</div><?php
function drawChart($suspicionTypeConstants, $chartId, $chartTitle, $suspicionValues, $suspicionKeys)
{
?>var data = google.visualization.arrayToDataTable([
[<?php
echo '\'Date\'';
foreach ($suspicionKeys as $suspicionKey):
echo ',\'' . $suspicionTypeConstants[$suspicionKey] . '\'';
endforeach;
?>]<?php
foreach ($suspicionValues as $day => $suspicion):
echo "\n" . ',[\'' . $day . '\'';
foreach ($suspicionKeys as $suspicionKey):
if (array_key_exists($suspicionKey, $suspicion)):
echo ',' . $suspicion[$suspicionKey];
else:
echo ',0';
endif;
endforeach;
echo ']';
endforeach;
?>
]);
var options = {
title: '<?php echo $chartTitle ?>',
curveType: 'function',
legend: { position: 'right' },
vAxis: {
viewWindow: {
min: 0
}
},
};
var chart = new google.visualization.LineChart(document.getElementById('<?php echo $chartId ?>'));
chart.draw(data, options); <?php
}
if (!empty($this->hoursSuspicions) || !empty($this->daysSuspicions)):
if (!empty($this->hoursSuspicions)):
?><div id="jHoursCurveChart" style="min-height:400px"></div><?php
endif;
if (!empty($this->daysSuspicions)):
?><div id="jDaysCurveChart" style="min-height:400px"></div><?php
endif;
?><script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
<?php
if (!empty($this->hoursSuspicions)):
drawChart($suspicionTypeConstants, 'jHoursCurveChart', 'Suspicions last 24 hours', $this->hoursSuspicions, $this->suspicionHoursKeys);
endif;
if (!empty($this->daysSuspicions)):
drawChart($suspicionTypeConstants, 'jDaysCurveChart', 'Suspicions last 30 days', $this->daysSuspicions, $this->suspicionDaysKeys);
endif;
?>
}
</script><?php
endif;
if (!empty($this->suspicions)):
?><table class="mod">
<tr>
<th>type</th>
<th>user</th>
<th>ip</th>
<th>occurrence</th>
<th>debugData</th>
</tr><?php
foreach ($this->suspicions as $suspicion):
?><tr>
<td><a href="<?php echo $this->path ?>mod/suspicions/?type=<?php echo $suspicion['type'] ?>"><?php echo $suspicionTypeConstants[$suspicion['type']] ?></a></td>
<td><?php
if ($suspicion['profileId'] === 0):
echo $suspicion['profileId'];
else:
?><a href="<?php echo $this->path ?>mod/suspicions/?user=<?php echo $suspicion['profileId'] ?>"><?php echo $suspicion['profileId'] ?></a>
<a href="<?php echo $this->path ?>user/view/<?php echo $suspicion['profileId'] ?>/mod/">[D]</a><?php
endif;
?></td>
<td>
<a href="<?php echo $this->path ?>mod/suspicions/?ip=<?php echo $suspicion['ip'] ?>"><?php echo $suspicion['ip'] ?></a>
<a href="http://whatismyipaddress.com/ip/<?php echo $suspicion['ip'] ?>">[D]</a>
</td>
<td><?php echo $suspicion['occurrence'] ?></td>
<td><?php echo prepareHTML($suspicion['debugData']) ?></td>
</tr><?php
endforeach;
?></table><?php
endif;