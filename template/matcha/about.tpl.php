<div class="textblock">
<h1><?php echo gettext('about.title')?></h1>
<h2><?php echo gettext('about.operator.title')?></h2>
<p>
Joachim Schoder<br />
Kienestrasse 1<br />
80933 M&uuml;nchen<br />
Germany
</p>
<p>
<?php echo gettext('about.email')?>: <a href="<?php echo $this->escapeLink('mailto:webmaster@veggiecommunity.org')?>"><?php echo $this->escapeLink('webmaster@veggiecommunity.org')?></a><br />
<?php echo gettext('about.internet')?>: <a href="https://www.veggiecommunity.org/<?php echo $this->locale ?>/" target="_blank">https://www.veggiecommunity.org/<?php echo $this->locale ?>/</a>
</p>
<p>
<?php echo gettext('about.responsibleforcontents')?>: Joachim Schoder<br />
<?php echo gettext('about.dataProtectionOfficer')?>: Joachim Schoder
</p>
<h2><?php echo gettext('about.thirdpartyproviders')?></h2>
<p><?php echo gettext('about.thirdpartyproviders.geo'); ?></p>
<h2><?php echo gettext('about.notices')?></h2>
<div>
<p><?php echo gettext('about.disclaimer'); ?></p>
<p><?php echo gettext('about.legalnotice'); ?></p>
</div>
<?php
/*
:TODO: JOE - show desperate cookies
<h2><?php echo gettext('about.cookies.title')?></h2>
<div>
<p><?php echo gettext('about.cookies.paragraph'); ?></p>
<ul class="list">
<li><strong>CUSTOM_DESIGN</strong> <?php echo gettext('about.cookies.'); ?></li>
<li><strong>CUSTOM_DESIGN</strong> <?php echo gettext('about.cookies.'); ?></li>
<li><strong>CUSTOM_DESIGN</strong> <?php echo gettext('about.cookies.'); ?></li>
<li><strong>CUSTOM_DESIGN</strong> <?php echo gettext('about.cookies.'); ?></li>
<li><strong>CUSTOM_DESIGN</strong> <?php echo gettext('about.cookies.'); ?></li>
</ul>
</div>
*/
?>
<h2><?php echo gettext('about.copyright.title')?></h2>
<p>
&copy; Copyright 2001-<?php echo vc\config\Globals::COPYRIGHT_YEAR ?> Joachim Schoder, <?php echo gettext("about.copyright")?>
</p>
</div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
