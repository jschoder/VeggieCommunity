<?php
namespace vc\helper;

class StartChartHelper
{
    const NORTH = 1;
    const EAST = 2;
    const SOUTH = 3;
    const WEST = 4;

    private $chartWidth;
    private $heightRelation;
    private $chartHeight;

    private $legendFontSize = 10;

    private $legendPadding = 8;

    private $legendMargin = array(
            self::NORTH => 16,
            self::EAST => 10,
            self::SOUTH => 16,
            self::WEST => 4,
    );

    // Colors
    private $backgroundColor = 0X00F4F5F2;

    private $borderColor = 0X009494A7;

    private $fontColor = 0X009494A7;

    private $chartColors = array(
        0X0090B530,
        0X00A3CB35,
        0X00356005,
        0X00E78E25,
        0X00E7B880,
        0X00DADAE8,
        0X009494A7
    );

    private $chartSources = array(
        'm' => array(),
        'l' => array()
    );

    // Chart Data
    private $charts = array();

    public function __construct()
    {
        $this->chartWidth = 1024;
        $this->heightRelation = 0.75;
        $this->chartHeight = $this->chartWidth * $this->heightRelation;

        $this->charts[] = array(
            'x' => 0.5,
            'y' => 0.5,
            'width' => 300,
            'source' => 'l',
            'caption' => 'search.field.nutrition',
            'fontSize' => 24,
            'legendPosition' => self::SOUTH
        );
        $this->charts[] = array(
            'x' => 0.27,
            'y' => 0.3,
            'width' => 200,
            'source' => 'm',
            'caption' => 'search.field.age',
            'fontSize' => 18,
            'legendPosition' => self::WEST
        );
        $this->charts[] = array(
            'x' => 0.27,
            'y' => 0.7,
            'width' => 200,
            'source' => 'm',
            'caption' => 'search.field.countries',
            'fontSize' => 18,
            'legendPosition' => self::WEST
        );
        $this->charts[] = array(
            'x' => 0.73,
            'y' => 0.3,
            'width' => 200,
            'source' => 'm',
            'caption' => 'search.field.search',
            'fontSize' => 18,
            'legendPosition' => self::EAST
        );
        $this->charts[] = array(
            'x' => 0.73,
            'y' => 0.7,
            'width' => 200,
            'source' => 'm',
            'caption' => 'search.field.gender',
            'fontSize' => 18,
            'legendPosition' => self::EAST
        );

        foreach (array_keys($this->chartSources) as $chartKey) {
            for ($i = 1; $i <= count($this->chartColors); $i++) {
                $this->chartSources[$chartKey][] = imagecreatefrompng(
                    APP_LIB . '/chart/chart-' . $chartKey . '-' . $i . '.png'
                );
            }
        }
    }

    public function updateChart($profileModel, $i14nComponent, $cacheModel, $locale, $outputFile)
    {
        $i14nComponent->loadLocale($locale);

        $image = imagecreatetruecolor($this->chartWidth, $this->chartHeight);

        imagefill($image, 0, 0, $this->backgroundColor);

        // Fill Data
        $this->charts[0]['data'] = $this->getNutritionData($profileModel);
        $this->charts[1]['data'] = $this->getAgeData($profileModel);
        $this->charts[2]['data'] = $this->getCountryData($profileModel, $locale, $cacheModel);
        $this->charts[3]['data'] = $this->getSearchData($profileModel);
        $this->charts[4]['data'] = $this->getGenderData($profileModel);

        // Draw Pie Chart
        foreach ($this->charts as $index => $chart) {
            $this->drawPieChart($image, $index);
        }

        // Chart Labels
        foreach ($this->charts as $index => $chart) {
            $this->drawChartCaption($image, $index);
        }
        // Chart Legend
        foreach ($this->charts as $index => $chart) {
            $this->drawChartLegend($image, $index);
        }

        // Add Logo and texts
        $logoImage = imagecreatefrompng(APP_LIB . '/chart/logo.png');
        imagecopyresampled(
            $image,
            $logoImage,
            15,
            15,
            0,
            0,
            imagesx($logoImage),
            imagesy($logoImage),
            imagesx($logoImage),
            imagesy($logoImage)
        );

        $ccImage = imagecreatefrompng(APP_LIB . '/chart/creativeCommons.png');
        imagecopyresampled(
            $image,
            $ccImage,
            $this->chartWidth - imagesx($ccImage) - 15,
            $this->chartHeight - imagesy($ccImage) - 15,
            0,
            0,
            imagesx($ccImage),
            imagesy($ccImage),
            imagesx($ccImage),
            imagesy($ccImage)
        );

        $timestampText = gettext('chart.lastUpdate') . ': ' . date('d.m.Y H:i');
        imagettftext(
            $image,
            10,
            0,
            $this->legendFontSize,
            $this->chartHeight - 15,
            $this->fontColor,
            APP_LIB . '/chart/PtSans-Normal.ttf',
            $timestampText
        );

        imagepng($image, $outputFile);
        imagepng($image, $outputFile);
        imagedestroy($image);
    }

    private function drawPieChart($image, $chartId)
    {
        $centerX = round($this->chartWidth * $this->charts[$chartId]['x']);
        $centerY = round($this->chartHeight * $this->charts[$chartId]['y']);

        $width = $this->charts[$chartId]['width'];
        $radius = $width / 2;
        $chartSources = $this->chartSources[$this->charts[$chartId]['source']];

        // To prevent issues with rounding errors draw a full circle with the default color
        imagecopyresampled(
            $image,
            $chartSources[0],
            $centerX - $radius,
            $centerY - $radius,
            0,
            0,
            $width,
            $width,
            $width,
            $width
        );

        $percentageStart = 0;
        $totalSum = array_sum($this->charts[$chartId]['data']);

        $colorIndex = 0;
        foreach ($this->charts[$chartId]['data'] as $value) {
            $arcFilterImage = imagecreatetruecolor($width, $width);

            $percentageEnd = $percentageStart + ($value / $totalSum);
            imagefilledarc(
                $arcFilterImage,
                $radius,
                $radius,
                $width + 10,
                $width + 10,
                270 - ($percentageEnd * 360),
                270 - ($percentageStart * 360),
                0X00FFFFFF,
                IMG_ARC_EDGED
            );

            // Apply mask to source
            $blended = $this->imagealphamask($chartSources[$colorIndex], $arcFilterImage);

            imagecopyresampled(
                $image,
                $blended,
                $centerX - $radius,
                $centerY - $radius,
                0,
                0,
                $width,
                $width,
                $width,
                $width
            );

            $percentageStart = $percentageEnd;
            $colorIndex++;
        }
    }

    private function imagealphamask($picture, $mask)
    {
        // Get sizes and set up new picture
        $xSize = imagesx($picture);
        $ySize = imagesy($picture);
        $newPicture = imagecreatetruecolor($xSize, $ySize);
        imagesavealpha($newPicture, true);
        imagefill($newPicture, 0, 0, imagecolorallocatealpha($newPicture, 0, 0, 0, 127));

        // Resize mask if necessary
        if ($xSize != imagesx($mask) || $ySize != imagesy($mask)) {
            $tempPic = imagecreatetruecolor($xSize, $ySize);
            imagecopyresampled($tempPic, $mask, 0, 0, 0, 0, $xSize, $ySize, imagesx($mask), imagesy($mask));
            imagedestroy($mask);
            $mask = $tempPic;
        }

        // Perform pixel-based alpha map application
        for ($x = 0; $x < $xSize; $x++) {
            for ($y = 0; $y < $ySize; $y++) {
                $alpha = imagecolorsforindex($mask, imagecolorat($mask, $x, $y));
                $alpha = 127 - floor($alpha['red'] / 2);
                $color = imagecolorsforindex($picture, imagecolorat($picture, $x, $y));
                imagesetpixel(
                    $newPicture,
                    $x,
                    $y,
                    imagecolorallocatealpha(
                        $newPicture,
                        $color['red'],
                        $color['green'],
                        $color['blue'],
                        max($alpha, $color['alpha'])
                    )
                );
            }
        }

        // Copy back to original picture
        return $newPicture;
    }

    private function drawChartCaption($image, $chartId)
    {
        $centerX = round($this->chartWidth * $this->charts[$chartId]['x']);
        $centerY = round($this->chartHeight * $this->charts[$chartId]['y']);

        $caption = gettext($this->charts[$chartId]['caption']);
        $ttfBox = imageTTFBbox(
            $this->charts[$chartId]['fontSize'],
            0,
            APP_LIB . '/chart/PtSans-Bold.ttf',
            $caption
        );
        $ttfWidth = abs($ttfBox[4] - $ttfBox[0]);

        imagettftext(
            $image,
            $this->charts[$chartId]['fontSize'],
            0,
            $centerX - ($ttfWidth / 2),
            $centerY + ($this->charts[$chartId]['fontSize'] / 2),
            $this->fontColor,
            APP_LIB . '/chart/PtSans-Bold.ttf',
            $caption
        );
    }

    private function drawChartLegend($image, $chartId)
    {
        $centerX = round($this->chartWidth * $this->charts[$chartId]['x']);
        $centerY = round($this->chartHeight * $this->charts[$chartId]['y']);
        $radius = $this->charts[$chartId]['width'] / 2;

        $leftColWidths = array();
        $colLeftMax = 0;
        $colRightMax = 0;
        $lineHeight = 0;

        foreach ($this->charts[$chartId]['data'] as $caption => $value) {
            $ttfBox = imageTTFBbox(
                $this->legendFontSize,
                0,
                APP_LIB . '/chart/PtSans-Normal.ttf',
                $value
            );
            $leftColWidths[] = abs($ttfBox[4] - $ttfBox[0]);
            $colLeftMax = max($colLeftMax, abs($ttfBox[4] - $ttfBox[0]));
            $lineHeight = max($lineHeight, abs($ttfBox[5] - $ttfBox[1]));

            $ttfBox = imageTTFBbox(
                $this->legendFontSize,
                0,
                APP_LIB . '/chart/PtSans-Normal.ttf',
                $caption
            );
            $colRightMax = max($colRightMax, abs($ttfBox[4] - $ttfBox[0]));
            $lineHeight = max($lineHeight, abs($ttfBox[5] - $ttfBox[1]));
        }

        $legendWidth = $colLeftMax + $this->legendPadding + $lineHeight + $this->legendPadding + $colRightMax;
        $lines = count($this->charts[$chartId]['data']);
        $legendHeight = ($lines * $lineHeight) + (($lines - 1) * $this->legendPadding);

        switch ($this->charts[$chartId]['legendPosition']) {
            case self::NORTH:
                $legendTopX = $centerX - ($legendWidth / 2);
                $legendTopY = $centerY - $radius - $this->legendMargin[self::NORTH] - $legendHeight;
                break;
            case self::EAST:
                $legendTopX = $centerX + $radius + $this->legendMargin[self::EAST];
                $legendTopY = $centerY - ($legendHeight / 2);
                break;
            case self::SOUTH:
                $legendTopX = $centerX - ($legendWidth / 2);
                $legendTopY = $centerY + $radius + $this->legendMargin[self::SOUTH];
                break;
            case self::WEST:
                $legendTopX = $centerX - $radius - $this->legendMargin[self::WEST] - $legendWidth;
                $legendTopY = $centerY - ($legendHeight / 2);
                break;
            default:
                return;
        }

        $lineY = $legendTopY;
        $line = 0;
        foreach ($this->charts[$chartId]['data'] as $caption => $value) {
            // Left Column
            imagettftext(
                $image,
                $this->legendFontSize,
                0,
                $legendTopX + ($colLeftMax - $leftColWidths[$line]),
                $lineY + $lineHeight,
                $this->fontColor,
                APP_LIB . '/chart/PtSans-Normal.ttf',
                $value
            );

            // Color Icon
            imagefilledrectangle(
                $image,
                $legendTopX + $colLeftMax + $this->legendPadding,
                $lineY,
                $legendTopX + $colLeftMax + $this->legendPadding + $lineHeight,
                $lineY + $lineHeight,
                $this->chartColors[$line]
            );

            // Right Column
            imagettftext(
                $image,
                $this->legendFontSize,
                0,
                $legendTopX + $colLeftMax + $this->legendPadding + $lineHeight + $this->legendPadding,
                $lineY + $lineHeight,
                $this->fontColor,
                APP_LIB . '/chart/PtSans-Normal.ttf',
                $caption
            );

            $lineY += $lineHeight + $this->legendPadding;
            $line++;
        }
    }

    private function getNutritionData($profileModel)
    {
        $dbData = $profileModel->getActiveFieldCount('nutrition');
        $data = array(
            gettext('profile.nutrition.vegetarian') => $dbData[3],
            gettext('profile.nutrition.almost_vegan') => $dbData[4],
            gettext('profile.nutrition.vegan') => $dbData[5],
            gettext('chart.rawfoodist') => $dbData[6] + $dbData[7] + $dbData[8] + $dbData[10],
            gettext('chart.other') => $dbData[2] + $dbData[11]
        );
        asort($data);
        $data = array_reverse($data, true);

        return $data;
    }

    private function getAgeData($profileModel)
    {
        $dbData = $profileModel->getActiveFieldCount('age');
        $ageRanges = array(
            array(14, 17),
            array(18, 24),
            array(25, 29),
            array(30, 37),
            array(38, 44),
            array(45, 59),
            array(60, 120)
        );

        $data = array();
        foreach ($ageRanges as $ageRange) {
            $data[$ageRange[0] . '-' . $ageRange[1]] = 0;
        }

        foreach ($dbData as $age => $count) {
            foreach ($ageRanges as $ageRange) {
                if ($age >= $ageRange[0] && $age <= $ageRange[1]) {
                    $data[$ageRange[0] . '-' . $ageRange[1]] = $data[$ageRange[0] . '-' . $ageRange[1]] + $count;
                }
            }
        }
        return $data;
    }

    private function getCountryData($profileModel, $locale, $cacheModel)
    {
        $dbData = $profileModel->getActiveFieldCount('country');

        $countries = $cacheModel->getCountries($locale);
        $countryNames = array();
        foreach ($countries as $country) {
            $countryNames[$country[0]] = $country[1];
        }

        asort($dbData);
        $dbData = array_reverse($dbData, true);

        $data = array();
        $otherCountryIndex = trim($countryNames[0]);
        foreach ($dbData as $countryId => $count) {
            if (count($data) < count($this->chartColors) - 1) {
                $data[$countryNames[$countryId]] = $count;
            } else {
                if (array_key_exists($otherCountryIndex, $data)) {
                    $data[$otherCountryIndex] = $data[$otherCountryIndex] + $count;
                } else {
                    $data[$otherCountryIndex] = $count;
                }
            }
        }

        return $data;
    }

    private function getSearchData($profileModel)
    {
        $dbData = $profileModel->getActiveFieldCount('search');

        $data = array(
            gettext('profile.search.penpal') => $dbData[1],
            gettext('profile.search.pal') => $dbData[5],
            gettext('profile.search.activist') => $dbData[10],
            gettext('profile.search.letsee') => $dbData[21] + $dbData[25] + $dbData[29],
            gettext('profile.search.date') => $dbData[41] + $dbData[45] + $dbData[49],
            gettext('profile.search.relationship') => $dbData[61] + $dbData[65] + $dbData[69],
            gettext('profile.search.polyamorous') => $dbData[81] + $dbData[85] + $dbData[89],
            gettext('profile.search.marriage') => $dbData[101] + $dbData[105] + $dbData[109]
        );
        asort($data);
        $data = array_reverse($data, true);
        array_pop($data);

        return $data;
    }

    private function getGenderData($profileModel)
    {
        $dbData = $profileModel->getActiveFieldCount('gender');
        asort($dbData);
        $dbData = array_reverse($dbData, true);
        $genderFields = \vc\config\Fields::getGenderFields();

        $data = array();
        foreach ($dbData as $field => $value) {
            $data[$genderFields[$field]] = $value;
        }

        return $data;
    }
}
