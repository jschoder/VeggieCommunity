<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('menu.mysite') ?></h1><?php
echo $this->element('tabs/mysite',
array('path' => $this->path,
'site' => $this->site,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFavorites' => $this->ownFavorites));
foreach ($this->news as $news):
?><div class="notifyNews"><?php
?><a class="close" data-url="<?php echo $this->path ?>news/delete/<?php echo $news->id ?>/" href="#" title="<?php echo gettext('mysite.news.close') ?>"></a><?php
$newsContent = nl2br($news->content);
$newsContent = preg_replace("@[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]@", "<a href=\"\\0\" target=\"_blank\" rel=\"nofollow\">\\0</a>", $newsContent);
echo $newsContent;
?></div><?php
endforeach;
?>
<?php if (!empty($this->locale) && $this->locale === 'de'): ?>
<div class="newsitem notifyWarn">
    <p>Die Seite wird zum 1. Januar abgeschaltet. Ihr bekommt dann alle komplett kostenlos einen Link via E-Mail zugeschickt um euren Nachrichtenverlauf als PDF herunterladen zu können. Die Daten werden dauerhaft am 1. Juli gelöscht.</p>
</div>
<div class="notifyNews">
    <strong>Zur aktuellen Corona-Epidemie:</strong> Bitte gebt auf euch acht und befolgt die Empfehlungen des <a href="https://www.bundesgesundheitsministerium.de/coronavirus.html">Bundesgesundheitsministeriums</a>. Und bitte achtet ihr darauf, dass ihr keine Verschwörungstheorien oder Verharmlosungen teilt, da diese von uns entfernt und die entsprechenden Verteiler gesperrt werden. <strong>Leben gefährden wird von uns nicht toleriert.</strong>
</div>
<?php endif; ?>
<?php if (!empty($this->locale) && $this->locale === 'en'): ?>
<div class="newsitem notifyWarn">
    <p>The site will be closed on January 1st. You will then all receive a link via email completely free of charge so that you can download your message history as a PDF. The data will be permanently deleted on July 1st.</p>
</div>
<?php endif; ?>
<div class="wideCol">
<div><?php
if (!empty($this->ownFriendsConfirmed)):
?><section id="mysiteActivity" class="collapsible">
<h2><?php echo gettext('mysite.feed.title')?></h2>
<div id="jFeed">
<div class="jLoading"></div>
</div>
</section><?php
else:
echo '&nbsp;';
endif;
?></div>
<div> <?php
if ($this->sessionSettings->getValue(\vc\object\Settings::VISIBLE_LAST_VISITOR) &&
count($this->visitors) > 0):
?><section class="collapsible" id="visitorsContent">
<h2><?php echo gettext('start.popup.lastvisitors')?></h2>
<div>
<div id="visitors"><?php
echo $this->render('mysite/visitors', false);
?></div>
<footer class="actions">
<ul class="h">
<li title="<?php echo gettext('mysite.visitors.showmore.tooltiptext')?>"><?php
echo gettext('mysite.visitors.totalvisitors') . ': ' . $this->visitorCount;
?></li><?php
if (count($this->visitors) > 0 && !is_object($this->visitors[count($this->visitors) - 1])):
?><li><a href="javascript:showVisitors()"><?php echo gettext("mysite.visitors.showmore")?></a></li><?php
endif;
?></ul>
</footer>
</div>
</section><?php
endif;
?><section id="searches">
<h2><?php echo gettext('mysite.savedsearches')?></h2><?php
if (count($this->savedSearches) === 0):
?><div><?php echo gettext("mysite.savedsearches.empty")?></div><?php
else:
foreach ($this->savedSearches as $search):
?><article id="search<?php echo $search->id ?>" class="collapsible">
<h3><?php echo $search->name ?></h3>
<div>
<div id="search<?php echo $search->id ?>Content">
<span class="jLoading"></span>
</div>
<footer class="actions"><?php
?><a class="details" href="<?php echo $this->path ?>user/result/?<?php echo str_replace('&', '&amp;', $search->url) ?>"><?php echo gettext("mysite.savedsearch.showfullresult")?></a> <?php
?><a class="delete"
href="javascript:deleteSavedSearch(<?php echo $search->id?>)"><?php
?><?php echo gettext("mysite.savedsearch.deletesearch")?></a><?php
?></footer>
</div>
</article><?php
endforeach;
endif;
?></section>
</div>
</div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
if (!empty($this->ownFriendsConfirmed)):
$this->addScript(
'loadURL(\'jFeed\', \'' . $this->path . 'mysite/feed/?inline=true\', false);'
);
endif;
$this->addScript(
'var searchIDs = new Array();' .
'var searchURLs = new Array();'
);
foreach ($this->savedSearches as $search) {
$this->addScript(
'searchIDs.push(' . $search->id . ');' .
'searchURLs.push(\'' . $this->path . 'user/result/short/?inline=true&' . $search->url  . '\');'
);
}
$this->addScript(
'showSearchResult(searchIDs, searchURLs);'
);
