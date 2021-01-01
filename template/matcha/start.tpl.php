<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><div class="wideCol">
<div>
<section><?php
echo gettext('start.infotext');
?><p>
<?php echo gettext('start.nomember'); ?>
<a class="button cta signup" href="<?php echo $this->path ?>account/signup/"><?php echo gettext('start.cta') ?></a>
</p>
</section>
</div>
<div>
<p>
<a class="jZoom zoomIcon" href="/img/chart/statistics-<?php echo $this->locale ?>.png" ><img alt="" src="/img/matcha/statistics-<?php echo $this->locale ?>.png" width="300" /></a>
</p>
<p>     
<a class="button secondary faq" href="<?php echo $this->path ?>help/faq/"><?php echo gettext('help.tab.faq') ?></a>
<a class="button secondary help" href="<?php echo $this->path ?>help/support/"><?php echo gettext('help.tab.feedback') ?></a>
</p><?php 
if (!empty($this->news)): 
?><section>
<h2><?php echo gettext('news.title') ?></h2>
<ul class="list news"><?php
if ($this->locale == 'de'):
$forumHashId = 'nnph7j4';
else:
$forumHashId = '8mtx1if';
endif;
foreach ($this->news as $newsHashId => $newsItem):
echo '<li><a href="' . $this->path . 'groups/forum/86om9vc/' . $forumHashId . '/#' . $newsHashId . '">' .
prepareHTML($newsItem[0]) . 
'</a><p>' . prepareHTML($newsItem[1]) . '</p></li>';
endforeach;
?></ul>
</section><?php 
endif; 
?></div>
</div>
