<?php

if (empty($_COOKIE['cc'])):
?><div class="cookies">
    <div><?php
        echo gettext('cookie.popup')
        ?><a class="close" href="#"></a>
    </div>
</div><?php
endif;

$jsPath = $this->path . 'js/' . $this->version . '/' . urlencode(gettext('profile.nutrition.vegan')) . '.js';
?><script src="<?php echo $jsPath ?>" ></script><?php

$script = $this->getScript();
?><script><?php
    if ($this->currentUser !== null):
    ?>vc.ui.titleCount = <?php echo ($this->newMessages + $this->openFriendRequests + $this->groupNotifications) ?>;<?php
    endif;
    echo $script
?></script><?php
/*
if ($this->sessionSettings->getValue(\vc\object\Settings::TRACKING, 1)):
?><script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//www.veggiecommunity.org/p/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script><?php
endif;
*/
?></body></html>