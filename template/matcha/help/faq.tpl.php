<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('menu.help') ?></h1><?php
echo $this->element('tabs/help',
array('path' => $this->path,
'site' => $this->site));
$this->echoWideAd($this->locale, $this->plusLevel);
?><div class="textblock">
<ul class="list"><?php
foreach ($this->faqs as $faq):
?><li class="question"><?php echo $faq->question ?></li><?php
?><li class="answer"><?php echo $faq->answer ?></li><?php
endforeach;
?></ul>
</div><?php
$this->echoWideAd($this->locale, $this->plusLevel);