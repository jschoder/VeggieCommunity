<?php
header("Content-type: text/html; charset=UTF-8");
if ($this->locale == 'en') {
$alternativeLocale = 'de';
} else {
$alternativeLocale = 'en';
}
$path = '';
if ($this->site !== 'start') {
$path .= $this->site . '/';
}
if (!empty($this->siteParams)) {
$path .= implode('/', $this->siteParams) . '/';
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->locale; ?>" lang="<?php echo $this->locale; ?>">
<head>
<title><?php echo $this->title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="Language" content="<?php echo $this->locale ?>" />
<meta name="Description" content="<?php echo htmlentities($this->header['description']) ?>" />
<meta name="Keywords" content="<?php echo htmlentities($this->header['keywords']) ?>" />
<meta name="Robots" content="<?php echo $this->header['robots'] ?>" />
<meta property="og:title " content="<?php echo $this->title; ?>" />
<meta property="og:description" content="<?php echo htmlentities($this->header['description']) ?>" />
<meta property="og:url" content="https://www.veggiecommunity.org/<?php echo $this->locale . '/' . $path ?>" />
<meta property="og:image" content="<?php echo $this->header['image'] ?>" />
<meta property="og:type" content="<?php echo $this->header['ogType'] ?>" /><?php
if (!empty($this->header['canonical'])):
?><link rel="canonical" href="<?php echo $this->header['canonical'] ?>" /><?php
endif;
if (!empty($this->header['prev'])):
?><link rel="prev" href="<?php echo $this->header['prev'] ?>" /><?php
endif;
if (!empty($this->header['next'])):
?><link rel="next" href="<?php echo $this->header['next'] ?>" /><?php
endif;
?><link rel="alternate" hreflang="<?php echo $alternativeLocale ?>" href="https://www.veggiecommunity.org/<?php echo $alternativeLocale . '/' . $path ?>" />
<link rel="icon" href="/img/favicon.png" type="image/png" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/img/apple-touch-icon-114x114-precomposed.png" />
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/img/apple-touch-icon-72x72-precomposed.png" />
<link rel="apple-touch-icon-precomposed" href="/img/apple-touch-icon-57x57-precomposed.png" /><?php
if (isset($rssUrl))
{
?><link rel="alternate" type="application/rss+xml" title="<?php echo gettext("header.title"); ?>" href="<?php echo $rssUrl; ?>" /><?php
}
// Using key words in JS-URL
?><script type="text/javascript" src="<?php echo $this->path; ?>js/<?php echo $this->version; ?>/<?php
?><?php echo urlencode(gettext("profile.nutrition.vegan"))?>/<?php echo urlencode(gettext("profile.nutrition.vegetarian"))?>.js" ></script><?php
// Using keywords in CSS-URL
?><link rel="stylesheet" type="text/css"
href="<?php echo $this->path ?>css/<?php echo $this->design ?>/<?php echo $this->version ?>/<?php
?><?php echo urlencode(gettext("profile.nutrition.vegetarian"))?>/<?php echo urlencode(gettext("profile.nutrition.vegan"))?>.css" /><?php
/*
?><link rel="search" type="application/opensearchdescription+xml"
href="<?php echo $this->path ?>opensearch.xml" title="<?php echo gettext('title')?>" /><?php
*/
?></head><?php
?><body><?php
?><a id="top"></a>