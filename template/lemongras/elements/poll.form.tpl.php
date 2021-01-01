<form class="showPoll" action="<?php echo $this->path ?>poll/vote/" method="post"><?php
?><input type="hidden" name="poll_id" value="<?php echo $this->poll->id?>" /><?php
?><input type="hidden" name="redirect" value="<?php echo $this->path ?>mysite" /><?php
?><ul><?php
foreach ($this->poll->options as $option)
{
?><li><?php
?><input id="polloption.<?php echo $this->poll->id?>.<?php echo $option['ID']?>" name="selected_option" value="<?php echo $option['ID']?>" type="radio" <?php if ($option['ID'] == $this->poll->own_vote) { ?>checked="checked"<?php }?> /><?php
?><label for="polloption.<?php echo $this->poll->id?>.<?php echo $option['ID']?>"><?php echo $option['OPTION']?></label><?php
?></li><?php
}
?></ul><?php
?><div class="buttons clearfix"><?php
?><a href="#" onclick="return loadPollResult(<?php echo $this->poll->id?>)"><?php echo gettext('face.poll.viewresults')?></a><?php
?><button type="submit"><?php echo gettext("face.poll.submitvote")?></button><?php
?></div><?php
?></form>