<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
/*
:TODO: JOE - replace with shariff
?><h1><?php echo gettext('share.title'); ?></h1>
<div class="thinCol">
<div>
<h2><?php echo gettext('share.tellafriend') ?></h2>
<form action="<?php echo $this->path ?>tellafriend/" method="post" accept-charset="UTF-8">
<ul>
<li>
<label for="sendername"><?php echo gettext("tellafriend.yourname")?> </label>
<input id="sendername" type="text" name="sendername" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderName) ?>" />
</li>
<li>
<label for="senderemail"><?php echo gettext("tellafriend.youremail")?> </label>
<input id="senderemail" type="text" name="senderemail" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSenderEmail) ?>" />
</li>
<li>
<label for="subject"><?php echo gettext("tellafriend.subject")?> </label>
<input id="subject" type="text" name="subject" maxlength="70" value="<?php echo $this->escapeAttribute($this->defaultSubject) ?>" />
</li>
<li>
<label for="reciever1"><?php echo gettext("tellafriend.otheremails")?> </label>
<input id="reciever1" type="text" name="reciever[]" maxlength="70" />
<input id="reciever2" type="text" name="reciever[]" maxlength="70" />
<input id="reciever3" type="text" name="reciever[]" maxlength="70" />
<input id="reciever4" type="text" name="reciever[]" maxlength="70" />
<input id="reciever5" type="text" name="reciever[]" maxlength="70" />
<input id="reciever6" type="text" name="reciever[]" maxlength="70" />
</li>
<li>
<label for="message"><?php echo gettext("tellafriend.message")?> </label>
<textarea id="message" name="message"><?php echo $this->defaultMessage?></textarea>
</li>
<li>
<button type="submit"><?php echo gettext('tellafriend.confirm') ?></button>
</li>
</ul>
</form>
</div>
<div>
<h2><?php echo gettext('share.facebook') ?></h2>
<div class="p">
<div class="fb-share-button" data-href="<?php echo $this->pathToShare ?>" data-width="70" data-type="button">...</div>
</div>
<h2><?php echo gettext('share.facebook.like') ?></h2>
<div class="p">
<div class="fb-like-box" data-href="https://www.facebook.com/veggiecommunity.org" data-width="200" data-height="390" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false">...</div>
</div>
<div id="fb-root"></div>
<script>(function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) return;
js = d.createElement(s); js.id = id;
<?php if ($this->locale == 'de') { ?>
js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&amp;version=v2.0";
<?php } else { ?>
js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&amp;version=v2.0";
<?php } ?>
fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
</div>
<div>
<h2><?php echo gettext('share.twitter') ?></h2>
<div class="p">
<a href="https://twitter.com/share" class="twitter-share-button"
data-url="<?php echo $this->pathToShare ?>"
data-text="<?php echo gettext('share.twitter.user.defaulttext') ?>"
data-size="large" data-count="none">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if (!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
</div>
<h2><?php echo gettext('share.twitter.previous') ?></h2>
<div class="p">
<a class="twitter-timeline"  href="https://twitter.com/hashtag/veggiecommunity"  data-widget-id="484331284076847106">#veggiecommunity Tweets</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if (!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</div>
</div>
</div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
*/