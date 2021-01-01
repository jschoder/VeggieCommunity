<div class="pagination"><?php
$firstUrl = $this->pagination->getFirst();
if ($firstUrl !== false):
if ($firstUrl === null):
?><img class="link" src="<?php echo $this->imagesPath ?>first-disabled.png" alt="" title="<?php echo gettext('pagination.first') ?>" width="10" height="10" /><?php
else:
?><a href="<?php echo $firstUrl ?>"><?php
?><img class="link" src="<?php echo $this->imagesPath ?>first.png" alt="" title="<?php echo gettext('pagination.first') ?>" width="10" height="10" /><?php
?></a><?php
endif;
endif;
$prevUrl = $this->pagination->getPrev();
if ($prevUrl !== false):
if ($prevUrl === null):
?><img class="link" src="<?php echo $this->imagesPath ?>prev-disabled.png" alt="" title="<?php echo gettext('pagination.prev') ?>" width="7" height="10" /><?php
else:
?><a href="<?php echo $prevUrl ?>"><?php
?><img class="link" src="<?php echo $this->imagesPath ?>prev.png" alt="" title="<?php echo gettext('pagination.prev') ?>" width="7" height="10" /><?php
?></a><?php
endif;
endif;
$currentUrl = $this->pagination->getCurrent();
foreach ($this->pagination->getItems() as $url => $caption):
if ($url === $currentUrl):
?><span><strong><?php echo prepareHTML($caption) ?></strong></span> <?php
else:
?><a href="<?php echo $url ?>"><?php echo prepareHTML($caption) ?></a> <?php
endif;
endforeach;
$nextUrl = $this->pagination->getNext();
if ($nextUrl !== false):
if ($nextUrl === null):
?><img class="link" src="<?php echo $this->imagesPath ?>next-disabled.png" alt="" title="<?php echo gettext('pagination.next') ?>" width="7" height="10" /><?php
else:
?><a href="<?php echo $nextUrl ?>"><?php
?><img class="link" src="<?php echo $this->imagesPath ?>next.png" alt="" title="<?php echo gettext('pagination.next') ?>" width="7" height="10" /><?php
?></a><?php
endif;
endif;
$lastUrl = $this->pagination->getLast();
if ($lastUrl !== false):
if ($lastUrl === null):
?><img class="link" src="<?php echo $this->imagesPath ?>last-disabled.png" alt="" title="<?php echo gettext('pagination.last') ?>" width="10" height="10" /><?php
else:
?><a href="<?php echo $lastUrl ?>"><?php
?><img class="link" src="<?php echo $this->imagesPath ?>last.png" alt="" title="<?php echo gettext('pagination.last') ?>" width="10" height="10" /><?php
?></a><?php
endif;
endif;
?></div>