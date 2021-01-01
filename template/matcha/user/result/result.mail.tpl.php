<?php
header("Content-Type: text/plain", true);
if (count($this->profiles) > 0)
{
if ($this->savedSearchType == 1)
{
if ($this->savedSearchInterval = 30)
{
echo gettext("result.mail.header.monthly.new");
}
elseif ($this->savedSearchInterval == 7)
{
echo gettext("result.mail.header.weekly.new");
}
elseif ($this->savedSearchInterval == 1)
{
echo gettext("result.mail.header.daily.new");
}
}
elseif ($this->savedSearchType == 2)
{
if ($this->savedSearchInterval == 30)
{
echo gettext("result.mail.header.monthly.updated");
}
elseif ($this->savedSearchInterval == 7)
{
echo gettext("result.mail.header.weekly.updated");
}
elseif ($this->savedSearchInterval == 1)
{
echo gettext("result.mail.header.daily.updated");
}
}
echo "\n\n";
foreach ($this->profiles as $profile)
{
echo $profile->nickname;
if ($profile->hideAge !== true) {
echo " (" . $profile->age . ") ";
}
echo ', ';
echo \vc\config\Fields::getNutritionCaption($profile->nutrition, $profile->nutritionFreetext, $profile->gender) . ", ";
if (!empty($profile->city))
{
echo $profile->city . ", ";
}
if (!empty($profile->region))
{
echo $profile->region . ", ";
}
echo $profile->countryname . "\n";
echo 'https://www.veggiecommunity.org' . $this->path . "user/view/" . $profile->id . "/\n\n";
}
echo gettext("result.mail.fullresult") . "\n";
echo 'https://www.veggiecommunity.org' . $this->path . "user/result/?" . $this->savedSearchUrl . "\n\n";
echo gettext("result.mail.settings") . "\n";
echo 'https://www.veggiecommunity.org' . $this->path . "mysite/\n\n";
echo gettext("result.mail.edit") . "\n";
echo 'https://www.veggiecommunity.org' . $this->path . "user/search/saved/\n\n";
echo "------------------------------------------------------------------\n\n";
echo 'https://www.veggiecommunity.org/' . $this->path . "/ - " . gettext("header.motto");
}