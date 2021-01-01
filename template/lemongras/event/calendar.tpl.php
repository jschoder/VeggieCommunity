<?php
echo $this->element('tabs/events',
array('path' => $this->path,
'site' => $this->site,));
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div class="fc-calendar-container clearfix">
<div class="fc-calendar fc-five-rows">
<div class="fc-head">
<div><?php echo gettext('date.weekday.Monday') ?></div>
<div><?php echo gettext('date.weekday.Tuesday') ?></div>
<div><?php echo gettext('date.weekday.Wednesday') ?></div>
<div><?php echo gettext('date.weekday.Thursday') ?></div>
<div><?php echo gettext('date.weekday.Friday') ?></div>
<div><?php echo gettext('date.weekday.Saturday') ?></div>
<div><?php echo gettext('date.weekday.Sunday') ?></div>
</div>
<div class="fc-body"><?php
$days = cal_days_in_month(CAL_GREGORIAN,$this->month,$this->year); // Days in current month
$lastmonth = date("t", mktime(0,0,0,$this->month-1,1,$this->year)); // Days in previous month
$start = date("N", mktime(0,0,0,$this->month,1,$this->year)); // Starting day of current month
$finish = date("N", mktime(0,0,0,$this->month,$days,$this->year)); // Finishing day of  current month
$laststart = $start - 1; // Days of previous month in calander
$counter = 1;
$nextMonthCounter = 1;
if($start > 5){	$rows = 6; }else {$rows = 5; }
for($i = 1; $i <= $rows; $i++){
?><div class="fc-row"><?php
for($x = 1; $x <= 7; $x++) {
if(($counter - $start) < 0){
$date = (($lastmonth - $laststart) + $counter);
$class = ' class="prevM"';
$events = array();
} else if(($counter - $start) >= $days){
$date = ($nextMonthCounter);
$nextMonthCounter++;
$class = ' class="nextM"';
$events = array();
} else {
$date = ($counter - $start + 1);
//				if($today == $counter - $start + 1){
//					$class = 'class="today"';
//				}
$class = '';
if (array_key_exists($date, $this->events)) {
$events = $this->events[$date];
} else {
$events = array();
}
}
?><div<?php echo $class ?>><?php
?><span><?php echo $date ?></span><?php
foreach ($events as $event) {
if (array_key_exists($event->categoryId, $this->eventCategories)) {
$category = $this->eventCategories[$event->categoryId];
} else {
$category = $this->eventCategories[100];
}
?><a style="border-color:#<?php echo $category['color'] ?>"
href="<?php echo $this->path ?>events/view/<?php echo $event->hashId ?>/"
title="<?php echo prepareHTML($event->name) ?> (<?php echo gettext($category['title']) ?>)"><?php
echo prepareHTML($event->name);
?></a><?php
}
?></div><?php
$counter++;
}
?></div><?php
}
?></div>
</div>
</div><?php
echo $this->element(
'pagination',
array(
'imagesPath' => $this->imagesPath,
'pagination' => $this->pagination
)
);
