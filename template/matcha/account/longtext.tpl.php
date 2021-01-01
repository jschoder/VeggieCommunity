<div class="textblock">
<h1><?php echo $this->title?></h1><?php
$lines = explode("\n", prepareHTML($this->longtext, false));
foreach ($lines as $line):
if (strpos($line, 'h2. ') === 0):
?><h2><?php echo trim(substr($line, 3)) ?></h2><?php
else:
?><p><?php echo trim($line) ?></p><?php
endif;
endforeach;
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
