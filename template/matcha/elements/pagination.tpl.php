<nav class="pag"><?php
$firstUrl = $this->pagination->getFirst();
if ($firstUrl !== false):
if ($firstUrl === null):
?><span class="first" title="<?php echo gettext('pagination.first') ?>"></span><?php
else:
?><a class="first" href="<?php echo $firstUrl ?>" title="<?php echo gettext('pagination.first') ?>"></a> <?php
endif;
endif;
$prevUrl = $this->pagination->getPrev();
if ($prevUrl !== false):
if ($prevUrl === null):
?><span class="prev" title="<?php echo gettext('pagination.prev') ?>"></span> <?php
else:
?><a class="prev" href="<?php echo $prevUrl ?>" title="<?php echo gettext('pagination.prev') ?>"></a> <?php
endif;
endif;
$currentUrl = $this->pagination->getCurrent();
foreach ($this->pagination->getItems() as $url => $caption):
if ($url === $currentUrl):
?><span><?php echo prepareHTML($caption) ?></span> <?php
else:
?><a href="<?php echo $url ?>"><?php echo prepareHTML($caption) ?></a> <?php
endif;
endforeach;
$nextUrl = $this->pagination->getNext();
if ($nextUrl !== false):
if ($nextUrl === null):
?> <span class="next" title="<?php echo gettext('pagination.next') ?>"></span><?php
else:
?> <a class="next" href="<?php echo $nextUrl ?>" title="<?php echo gettext('pagination.next') ?>"></a><?php
endif;
endif;
$lastUrl = $this->pagination->getLast();
if ($lastUrl !== false):
if ($lastUrl === null):
?> <span class="last" title="<?php echo gettext('pagination.last') ?>"></span><?php
else:
?> <a class="last" href="<?php echo $lastUrl ?>" title="<?php echo gettext('pagination.last') ?>"></a><?php
endif;
endif;
?></nav>