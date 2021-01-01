<div class="pollResults"><?php
?><ul><?php
foreach ($this->poll->options as $option)
{
if ($this->poll->total == 0)
{
$percentage = 0;
}
else
{
$percentage = round($option['COUNT'] / ($this->poll->total / 100));
}
?><li><?php
?><span class="resultText"><?php echo $option['OPTION']?></span><?php
?><div id="result1" class="resultBarBox"><?php
?><div style="width: <?php echo $percentage?>%;" class="resultBar"></div><?php
?><span class="resultPer"><?php echo $percentage?>%</span><?php
?><span class="resultVotes"> (<?php echo $option['COUNT']?> <?php echo gettext('face.poll.votes')?>)</span><?php
?></div><?php
?></li><?php
}
?></ul><?php
?><div class="buttons clearfix"><?php
?><a href="#" onclick="return loadPollForm(<?php echo $this->poll->id?>)"><?php echo gettext('face.poll.viewform')?></a><?php
?><span><?php echo gettext('face.poll.totalvotes')?>: <?php echo $this->poll->total?></span><?php
?></div><?php
?></div>