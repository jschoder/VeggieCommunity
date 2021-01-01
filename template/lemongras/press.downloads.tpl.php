<h2><?php echo $this->tabTitle?></h2>
<div id="press">
<?php if (count($this->logos) > 0) { ?>
<h3><?php echo gettext('press.downloads.logo')?></h3>
<p><?php foreach ($this->logos as $thumb=>$full) { ?>
<a href="/img/press/logos/full/<?php echo $full?>" target="_blank"><img src="/img/press/logos/thumbs/<?php echo $thumb?>" /></a>
<?php } ?></p>
<?php } ?>
<?php if (count($this->screenshots) > 0) { ?>
<h3><?php echo gettext('press.downloads.screenshots')?></h3>
<p><?php foreach ($this->screenshots as $thumb=>$full) { ?>
<a href="/img/press/screenshots/full/<?php echo $full?>" target="_blank"><img src="/img/press/screenshots/thumbs/<?php echo $thumb?>" /></a>
<?php } ?></p>
<?php } ?>
<?php if (count($this->photos) > 0) { ?>
<h3><?php echo gettext('press.downloads.photos')?></h3>
<p><?php foreach ($this->photos as $thumb=>$full) { ?>
<a href="/img/press/photos/full/<?php echo $full?>" target="_blank"><img src="/img/press/photos/thumbs/<?php echo $thumb?>" /></a>
<?php } ?></p>
<?php } ?>
</div>