<h1><?php echo gettext('menu.help') ?></h1><?php
//echo $this->render('help/tabs');
echo $this->element('tabs/help',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><div id="faq"><?php
foreach ($this->faqs as $faq) {
?><div class="question"><?php echo $faq->question ?></div><?php
?><div class="answer"><?php echo $faq->answer ?></div><?php
}
?></div>