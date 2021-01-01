<?php if (!empty($this->events)): ?>
<aside id="blockEventUpcoming" class="block collapsible">
<header><h3><?php echo gettext('block.event.upcoming.title')?></h3></header>
<div>
<ul class="followed thumblist"><?php
foreach ($this->events as $event) {
if (array_key_exists($event->categoryId, $this->eventCategories)):
$category = $this->eventCategories[$event->categoryId];
else:
$category = $this->eventCategories[100];
endif;
$link = $this->path . 'events/view/' . $event->hashId . '/';
$title = $this->getShortDate(strtotime($event->startDate)) . ' ' . prepareHTML($event->name) . ' (' . gettext($category['title']) . ')';
?><li title="<?php echo $title ?>">
<a href="<?php echo $link ?>"><?php
if (empty($event->image)):
?><img alt="" src="/img/matcha/thumb/default-event.png"  /><?php
else:
?><img alt="" src="/events/picture/crop/74/74/<?php echo $event->image ?>" /><?php
endif;
?></a>
<a class="label" href="<?php echo $link ?>"><?php echo prepareHTML($event->name) ?></a>
</li><?php
}
?></ul>
<footer class="actions">
<a class="details" href="<?php echo $this->path ?>events/calendar/"><?php echo gettext('block.extendedResult') ?></a>
</footer>
</div>
</aside>
<?php endif;