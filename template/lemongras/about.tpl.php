<h1><?php echo gettext('about.title')?></h1>
<div id="imprint">
<h2><?php echo gettext('about.operator.title')?></h2>
<div>
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
</div>
<h2><?php echo gettext('about.thirdpartyproviders')?></h2>
<div>
<p><?php echo gettext('about.thirdpartyproviders.geo'); ?></p>
</div>
<h2><?php echo gettext('about.notices')?></h2>
<div>
<p><?php echo gettext('about.disclaimer'); ?></p>
<p><?php echo gettext('about.legalnotice'); ?></p>
</div>
<h2><?php echo gettext('about.copyright.title')?></h2>
<div>
<p>&copy; Copyright 2001-<?php echo vc\config\Globals::COPYRIGHT_YEAR ?> Joachim Schoder, <?php echo gettext("about.copyright")?></p>
</div>
</div>