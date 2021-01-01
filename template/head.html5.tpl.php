<?php
header("Content-type: text/html; charset=UTF-8");
if ($this->locale == 'en') {
$alternativeLocale = 'de';
} else {
$alternativeLocale = 'en';
}
?><!DOCTYPE html>
<html lang="<?php echo $this->locale; ?>">
<head>
<title><?php echo $this->title; ?></title>
<meta charset="utf-8" />
<meta name="Language" content="<?php echo $this->locale ?>" />
<meta name="Description" content="<?php echo htmlentities($this->header['description']) ?>" />
<meta name="Keywords" content="<?php echo htmlentities($this->header['keywords']) ?>" />
<meta name="Robots" content="<?php echo $this->header['robots'] ?>" />
<meta property="og:title " content="<?php echo $this->title; ?>" />
<meta property="og:description" content="<?php echo htmlentities($this->header['description']) ?>" />
<meta property="og:url" content="https://www.veggiecommunity.org/<?php echo $this->locale . '/' . $this->noLocalePath ?>" />
<meta property="og:image" content="<?php echo $this->header['image'] ?>" />
<meta property="og:type" content="<?php echo $this->header['ogType'] ?>" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="@VeggieCommunity" />
<meta name="twitter:title" content="<?php echo $this->title; ?>" />
<meta name="twitter:description" content="<?php echo htmlentities($this->header['description']) ?>" />
<meta name="twitter:image" content="<?php echo $this->header['image'] ?>" /><?php
if (!empty($this->header['canonical'])):
?><link rel="canonical" href="<?php echo $this->header['canonical'] ?>" /><?php
else:
?><link rel="canonical" href="https://www.veggiecommunity.org/<?php echo $this->locale . '/' . $this->noLocalePath ?>" /><?php
endif;
if (!empty($this->header['prev'])):
?><link rel="prev" href="<?php echo $this->header['prev'] ?>" /><?php
endif;
if (!empty($this->header['next'])):
?><link rel="next" href="<?php echo $this->header['next'] ?>" /><?php
endif;
?><link rel="alternate" hreflang="<?php echo $alternativeLocale ?>" href="https://www.veggiecommunity.org/<?php echo $alternativeLocale . '/' . $this->noLocalePath ?>" />
<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="/img/favicon.png" />
<meta name="theme-color" content="#90b530" />
<link rel="apple-touch-icon-precomposed" sizes="120x120" href="/img/apple-touch-icon-120x120-precomposed.png" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/img/apple-touch-icon-114x114-precomposed.png" />
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/img/apple-touch-icon-72x72-precomposed.png" />
<link rel="apple-touch-icon-precomposed" href="/img/apple-touch-icon-57x57-precomposed.png" /><?php
if (isset($rssUrl)):
?><link rel="alternate" type="application/rss+xml" title="<?php echo gettext("header.title"); ?>" href="<?php echo $rssUrl; ?>" /><?php
endif;
?><!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]--><?php
$cssPath = $this->path . 'css/' . $this->design  . '/' . $this->version . '/' . urlencode(gettext('profile.nutrition.vegan')) . '.css';
if($this->currentUser !== null &&
$this->plusLevel >= \vc\object\Plus::PLUS_TYPE_STANDARD &&
!empty($_COOKIE['CUSTOM_DESIGN'])):
$cssPath .= '?custom=' . urlencode($_COOKIE['CUSTOM_DESIGN']);
endif;
?><link rel="stylesheet" type="text/css" href="<?php echo $cssPath ?>" /><?php
/*
?><link rel="search" type="application/opensearchdescription+xml"
href="<?php echo $this->path ?>opensearch.xml" title="<?php echo gettext('title')?>" /><?php
*/
?></head><?php
?><body><?php
?><a id="top"></a>