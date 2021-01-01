<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load("current", {packages:['corechart']});
</script><?php
$metricCharts = array(
vc\object\Metric::TYPE_PROFILE_CREATION => 'Created Profile',
vc\object\Metric::TYPE_PROFILE_MANUAL_DELETION => 'Deleted Profiles',
vc\object\Metric::TYPE_LOGINS_TOTAL => 'Total Logins',
vc\object\Metric::TYPE_LOGINS_DISTINCT => 'Logged in users',
vc\object\Metric::TYPE_LOGINS_DAYS_SINCE => 'Days since last login',
vc\object\Metric::TYPE_PM_SENT => 'Sent messages',
vc\object\Metric::TYPE_PM_NEW_CONTACTS => 'New message contacts',
vc\object\Metric::TYPE_THREADS => 'Posted threads',
vc\object\Metric::TYPE_THREAD_COMMENTS => 'Posted comments',
vc\object\Metric::TYPE_EVENTS_UPCOMING => 'Upcoming events',
vc\object\Metric::TYPE_EVENTS_TODAY => 'Events today',
vc\object\Metric::TYPE_EVENT_PARTICIPATION_LIKELY => 'Likely Event Participations',
vc\object\Metric::TYPE_EVENT_PARTICIPATION_UNLIKELY => 'Unlikely Event Pariticipations',
vc\object\Metric::TYPE_CHAT_MESSAGES => 'Chat messages',
vc\object\Metric::TYPE_ACTIVE_PLUS => 'Active Plus Accounts'
);
foreach ($metricCharts as $key => $metricLabel):
if (array_key_exists($key, $this->metricData) && !empty($this->metricData[$key])):
?><div id="jsMetricsChart<?php echo $key ?>" style="min-height:300px"></div>
<script type="text/javascript">
google.charts.setOnLoadCallback(drawMetricsChart<?php echo $key ?>);
function drawMetricsChart<?php echo $key ?>() {
var data = google.visualization.arrayToDataTable([
['Week', 'Amount']<?php
foreach ($this->metricData[$key] as $week => $value):
if ($key === vc\object\Metric::TYPE_LOGINS_DAYS_SINCE):
echo ',[' . $week .  ', ' . $value . ']';
else:
$weekSplit = explode('-', $week);
$weekStart = new DateTime();
$weekStart->setISODate(intval($weekSplit[0]),intval($weekSplit[1]));
$date = strtotime($weekStart->format('d-M-Y'));
echo ',[new Date(' . date('Y', $date) .  ', ' . date('n', $date) .  ', ' . date('j', $date) .  '), ' . $value . ']';
endif;
endforeach;
?>]);
var options = {
title: '<?php echo $metricLabel ?>',
legend: { position: 'bottom' }
<?php if ($key !== vc\object\Metric::TYPE_LOGINS_DAYS_SINCE): ?>
, trendlines: {
0: {
type: 'linear',
color: '#C00',
showR2: true,
visibleInLegend: true
}
}
<?php endif; ?>
};
var chart = new google.visualization.ColumnChart(document.getElementById('jsMetricsChart<?php echo $key ?>'));
chart.draw(data, options);
}
</script><?php
endif;
endforeach;
