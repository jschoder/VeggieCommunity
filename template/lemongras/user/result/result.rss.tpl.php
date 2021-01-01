<?php
header("Content-Type: text/xml", true);
echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?><rss version="2.0">
<channel>
<title><?php echo gettext("result.rss.title")?></title>
<link>http://www.veggiecommunity.org</link>
<description><?php echo gettext("result.rss.description")?></description>
<language><?php echo gettext("result.rss.language")?></language><?php
foreach ($this->profiles as $profile)
{
?><item>
<title><?php echo $profile->nickname?><?php if ($profile->hideAge !== true) { echo ' (' . $profile->age . ')'; } ?></title>
<description><?php echo prepareHTML(\vc\config\Fields::getNutritionCaption($profile->nutrition, $profile->nutritionFreetext, $profile->gender))?>,
<?php echo prepareHTML($profile->getHtmlLocation()) ?></description>
<link><?php echo $this->path?>user/view/<?php echo $profile->id?></link>
<pubDate><?php echo prepareTextDate($profile->lastUpdate)?></pubDate>
</item><?php
}
?></channel>
</rss>