<?php
echo $this->element('mysite.header',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser' => $this->currentUser,
'ownPicture' => $this->ownPicture,
'onlineSetting' => $this->sessionSettings->getValue(\vc\object\Settings::VISIBLE_ONLINE)));
echo $this->element('tabs/mysite',
array('path' => $this->path,
'site' => $this->site,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFavorites' => $this->ownFavorites));
if (!empty($this->notification)) {
echo $this->element('notification',
array('notification' => $this->notification));
}
foreach ($this->news as $news)
{
?><div id="news<?php echo $news->id?>" class="newsitem notifyNews"><?php
?><a data-url="<?php echo $this->path ?>news/delete/<?php echo $news->id ?>/"  class="close"><?php echo gettext('mysite.news.close')?></a><?php
?><h3><?php echo gettext('mysite.news')?></h3><?php
$newsContent = nl2br($news->content);
$newsContent = preg_replace("@[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]@", "<a href=\"\\0\" target=\"_blank\" rel=\"nofollow\">\\0</a>", $newsContent);
$newsContent = str_replace(
array('<p>', '</p>', '<li>'),
array('<div>', '</div>', '<li style="line-height:2.7em">'),
$newsContent
);
?><p><?php echo $newsContent?></p><?php
?></div><?php
}
if (!empty($this->locale) && $this->locale === 'de'): ?>
<div class="newsitem notifyWarn">
    <p>Die Seite wird zum 1. Januar abgeschaltet. Ihr bekommt dann alle komplett kostenlos einen Link via E-Mail zugeschickt um euren Nachrichtenverlauf als PDF herunterladen zu können. Die Daten werden dauerhaft am 1. Juli gelöscht.</p>
</div>
<div class="newsitem notifyNews">
    <p><strong>Zur aktuellen Corona-Epidemie:</strong> Bitte gebt auf euch acht und befolgt die Empfehlungen des <a href="https://www.bundesgesundheitsministerium.de/coronavirus.html">Bundesgesundheitsministeriums</a>. Und bitte achtet ihr darauf, dass ihr keine Verschwörungstheorien oder Verharmlosungen teilt, da diese von uns entfernt und die entsprechenden Verteiler gesperrt werden. <strong>Leben gefährden wird von uns nicht toleriert.</strong></p>
</div>
<?php endif; 
if (!empty($this->locale) && $this->locale === 'en'): ?>
<div class="newsitem notifyWarn">
    <p>The site will be closed on January 1st. You will then all receive a link via email completely free of charge so that you can download your message history as a PDF. The data will be permanently deleted on July 1st.</p>
</div>
<?php endif; ?>
<div class="wrap clearfix">
<div id="activities">
<h3><?php echo gettext('mysite.feed.title')?></h3>
<div id="jFeed">
<div><img class="loading" src="<?php echo $this->imagesPath?>ajax-loader.gif" width="16" height="16" alt=""/></div>
</div>
</div>
<div id="contentSide">
<?php if ($this->sessionSettings->getValue(\vc\object\Settings::VISIBLE_LAST_VISITOR) &&
count($this->visitors) > 0) { ?>
<div id="visitorsContent">
<h3 id="slide-header-visitorsContent" class="openslide" onclick="setSlideVisible('visitorsContent', false)"><?php echo gettext('start.popup.lastvisitors')?></h3>
<div id="slide-content-visitorsContent" >
<div id="visitors"><?php
echo $this->render('mysite/visitors', false);
?></div><!-- #visitorsContent -->
<p><span title="<?php echo gettext('mysite.visitors.showmore.tooltiptext')?>"><?php echo gettext('mysite.visitors.totalvisitors')?>: <?php echo $this->visitorCount?></span></p><?php
if (count($this->visitors) > 0 && !is_object($this->visitors[count($this->visitors) - 1]))
{
?><p><a href="javascript:showVisitors()"><?php echo gettext("mysite.visitors.showmore")?></a></p><?php
}
?></div>
</div><!-- #visitorsContent -->
<?php } ?>
<div id="searches" >
<h3><?php echo gettext('mysite.savedsearches')?></h3><?php
if (count($this->savedSearches) === 0) {
?><div><?php echo gettext("mysite.savedsearches.empty")?></div><?php
} else {
foreach ($this->savedSearches as $search) {
?><div id="search<?php echo $search->id ?>" class="result">
<h4 id="slide-header-savedsearch<?php echo $search->id ?>" class="openslide" onclick="setSlideVisible('savedsearch<?php echo $search->id ?>', false)"><?php echo $search->name ?></h4>
<div id="slide-content-savedsearch<?php echo $search->id ?>">
<div id="search<?php echo $search->id ?>Content"><img class="loading" src="<?php echo $this->imagesPath?>ajax-loader.gif" width="16" height="16" alt=""/></div>
<p><?php
?><a class="details" href="<?php echo $this->path ?>user/result/?<?php echo str_replace('&', '&amp;', $search->url) ?>"><?php echo gettext("mysite.savedsearch.showfullresult")?></a><?php
?><a class="delete"
href="javascript:deleteSavedSearch(<?php echo $search->id?>)"><?php
?><?php echo gettext("mysite.savedsearch.deletesearch")?></a><?php
?></p>
</div>
</div><!-- .result --><?php
}
}
?></div><!-- #searches -->
</div><!-- #contentSide -->
</div><!-- .wrap --><?php
$this->addScript(
'loadURL(\'jFeed\', \'' . $this->path . 'mysite/feed/?inline=true\', false);' .
'var searchIDs = new Array();' .
'var searchURLs = new Array();'
);
foreach ($this->savedSearches as $search) {
$this->addScript(
'searchIDs.push(' . $search->id . ');
searchURLs.push(\'' . $this->path . 'user/result/short/?inline=true&' . $search->url  . '\');'
);
}
$this->addScript(
'showSearchResult(searchIDs, searchURLs);'
);
