<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('event.calendar.title') ?></h1><?php
echo $this->element('tabs/events',
array('path' => $this->path,
'site' => $this->site));
$this->echoWideAd($this->locale, $this->plusLevel);
?><section class="calendar">
<header>
<div><?php echo gettext('date.weekday.Monday') ?></div>
<div><?php echo gettext('date.weekday.Tuesday') ?></div>
<div><?php echo gettext('date.weekday.Wednesday') ?></div>
<div><?php echo gettext('date.weekday.Thursday') ?></div>
<div><?php echo gettext('date.weekday.Friday') ?></div>
<div><?php echo gettext('date.weekday.Saturday') ?></div>
<div><?php echo gettext('date.weekday.Sunday') ?></div>
</header>
<div class="body"><?php
$days = cal_days_in_month(CAL_GREGORIAN,$this->month,$this->year); // Days in current month
$lastmonth = date("t", mktime(0,0,0,$this->month-1,1,$this->year)); // Days in previous month
$start = date("N", mktime(0,0,0,$this->month,1,$this->year)); // Starting day of current month
$finish = date("N", mktime(0,0,0,$this->month,$days,$this->year)); // Finishing day of  current month
$laststart = $start - 1; // Days of previous month in calander
$counter = 1;
$nextMonthCounter = 1;
$rows = $start > 5 ? 6 : 5;
for($i = 1; $i <= $rows; $i++):
?><div class="week"><?php
for($x = 1; $x <= 7; $x++):
if (($counter - $start) < 0):
$date = (($lastmonth - $laststart) + $counter);
$class = ' class="prevM"';
$events = array();
elseif (($counter - $start) >= $days):
$date = ($nextMonthCounter);
$nextMonthCounter++;
$class = ' class="nextM"';
$events = array();
else:
$date = ($counter - $start + 1);
//				if ($today == $counter - $start + 1):
//					$class = 'class="today"';
//				endif;
$class = '';
if (array_key_exists($date, $this->events)):
$events = $this->events[$date];
else:
$events = array();
endif;
endif;
?><div<?php echo $class ?>>
<span class="day"><?php echo $date ?></span><span class="weekday"><?php
switch ($x):
case 1:
echo gettext('date.weekday.short.Monday');
break;
case 2:
echo gettext('date.weekday.short.Tuesday');
break;
case 3:
echo gettext('date.weekday.short.Wednesday');
break;
case 4:
echo gettext('date.weekday.short.Thursday');
break;
case 5:
echo gettext('date.weekday.short.Friday');
break;
case 6:
echo gettext('date.weekday.short.Saturday');
break;
case 7:
echo gettext('date.weekday.short.Sunday');
break;
endswitch;
?></span><?php
foreach ($events as $event):
if (array_key_exists($event->categoryId, $this->eventCategories)):
$category = $this->eventCategories[$event->categoryId];
else:
$category = $this->eventCategories[100];
endif;
?><div class="event <?php echo $category['class'] ?>"><?php
?><a href="<?php echo $this->path ?>events/view/<?php echo $event->hashId ?>/"
title="<?php echo prepareHTML($event->name) ?> (<?php echo gettext($category['title']) ?>)"><?php
echo prepareHTML($event->name);
?></a><?php
?></div><?php
endforeach;
?></div><?php
$counter++;
endfor;
?></div><?php
endfor;
?></div>
</section><?php
echo $this->element(
'pagination',
array(
'pagination' => $this->pagination
)
);
$this->echoWideAd($this->locale, $this->plusLevel);