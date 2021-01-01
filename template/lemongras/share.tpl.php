<h1><?php echo gettext('share.title'); ?></h1>
<?php
if (!empty($this->notification)) {
echo $this->element('notification',
array('notification' => $this->notification));
}
/*
?>
:TODO: JOE - replace with shariff
<div class="share">
<div class="tellafriend">
<h2><?php echo gettext('share.tellafriend') ?></h2>
<form action="<?php echo $this->path ?>tellafriend/" method="post" accept-charset="UTF-8">
<div class="form">
<dl id="tellafriend-sender" class="clearfix">
<dt class="caption"><label for="sendername"><?php echo gettext("tellafriend.yourname")?> </label></dt>
<dd class="field"><input id="sendername" class="text180" type="text" name="sendername" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderName) ?>" /></dd>
<dt class="caption"><label for="senderemail"><?php echo gettext("tellafriend.youremail")?> </label></dt>
<dd class="field"><input id="senderemail" class="text180" type="text" name="senderemail" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderEmail) ?>" /></dd>
<dt class="caption"><label for="subject"><?php echo gettext("tellafriend.subject")?> </label></dt>
<dd class="field"><input id="subject" class="text180" type="text" name="subject" maxlength="70" value="<?php echo $this->defaultSubject?>" /></dd>
</dl>
<p><label for="reciever1"><?php echo gettext("tellafriend.otheremails")?> </label></p>
<ul id="tellafriend-reciever" class="clearfix">
<li><input id="reciever1" class="text130" type="text" name="reciever[]" maxlength="70" /></li>
<li><input id="reciever2" class="text130" type="text" name="reciever[]" maxlength="70" /></li>
<li><input id="reciever3" class="text130" type="text" name="reciever[]" maxlength="70" /></li>
<li><input id="reciever4" class="text130" type="text" name="reciever[]" maxlength="70" /></li>
<li><input id="reciever5" class="text130" type="text" name="reciever[]" maxlength="70" /></li>
<li><input id="reciever6" class="text130" type="text" name="reciever[]" maxlength="70" /></li>
</ul>
<p><label for="message"><?php echo gettext("tellafriend.message")?> </label></p>
<textarea id="message" class="text" name="message"><?php echo $this->defaultMessage?></textarea>
<div class="buttons">
<button type="submit"><?php echo gettext('tellafriend.confirm') ?></button>
</div>
</div>
</form>
</div>
<div class="facebook">
<h2><?php echo gettext('share.facebook') ?></h2>
<div class="fb-share-button" data-href="<?php echo $this->pathToShare ?>" data-width="70" data-type="button">...</div>
<h2><?php echo gettext('share.facebook.like') ?></h2>
<div class="fb-like-box" data-href="https://www.facebook.com/veggiecommunity.org" data-width="240" data-height="390" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false">...</div>
<div id="fb-root"></div>
<script>(function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) return;
js = d.createElement(s); js.id = id;
<?php if ($this->locale == 'de') { ?>
js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&version=v2.0";
<?php } else { ?>
js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
<?php } ?>
fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
</div>
<div class="twitter">
<h2><?php echo gettext('share.twitter') ?></h2>
<a href="https://twitter.com/share/" class="twitter-share-button"
data-url="<?php echo $this->pathToShare ?>"
data-text="<?php echo gettext('share.twitter.user.defaulttext') ?>"
data-size="large" data-count="none">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if (!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<h2><?php echo gettext('share.twitter.previous') ?></h2>
<a class="twitter-timeline"  href="https://twitter.com/hashtag/veggiecommunity/"  data-widget-id="484331284076847106">#veggiecommunity Tweets</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if (!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</div>
</div>
*/