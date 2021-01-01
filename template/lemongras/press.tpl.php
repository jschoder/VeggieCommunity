<h1><?php echo $this->siteData['SITE_TITLE']?></h1><?php
require(dirname(__FILE__) . '/_tabs.php');
$logos = array();
$logos['default.png'] = 'veggiecommunity-logo-default.png';
$logos['bw.png'] = 'veggiecommunity-logo-bw.png';
$logos['bw-inverted.png'] = 'veggiecommunity-logo-bw-inverted.png';
$screenshots = array();
//	$screenshots['chins.jpg'] = 'chins.jpg';
$photos = array();
//	$photos['caulfield.gif'] = 'caulfield.gif';
//	$photos['dictator.jpg'] = 'dictator.jpg';
new Template('press.' . $tabID,
array('tabTitle'=>$siteData['TABS'][$tabID]['CAPTION'],
'logos' => $logos,
'screenshots' => $screenshots,
'photos' => $photos));
