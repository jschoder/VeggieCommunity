<?php
namespace vc\controller\web;

class CssController extends AbstractWebController
{
    protected function logPageView()
    {
        return false;
    }

    /**
     * Certain elements should not collapsed into single blocks.
     */
    private static $nonCollapseElements = array('@font-face');

    public function handleGet(\vc\controller\Request $request)
    {
        if (count($this->siteParams) == 0) {
            throw new \vc\exception\NotFoundException();
        }

        // Overwriting the design
        $this->design = $this->siteParams[0];
        // Requiring the version for sprites
        $this->version = \vc\config\Globals::VERSION;

        if (empty($_GET['custom'])) {
            $customKey = null;
        } else {
            $customKey = $_GET['custom'];
        }

        $cacheModel = $this->getModel('Cache');
        $content = $cacheModel->getJsCssCache(
            'CSS',
            $this->locale,
            $this->design,
            $customKey
        );

        if ($content === null || $this->getServer() == 'local') {
            $content = $this->getContent();
            $cacheModel->setJsCssCache(
                'CSS',
                $this->locale,
                $this->design,
                $content,
                $customKey
            );
        }

        header('Content-Type: text/css', true);
        header('Expires: Wed, 01 Jan 2025 01:00:00 GMT', true);
        header('Last-Modified: Wed, 01 Jan 2015 01:00:00 GMT', true);
        header('Cache-Control:max-age=31536000', true);
        header_remove('Pragma');
        echo($content);
    }

    private function getContent()
    {
        $content = '@charset "utf-8";';
        if ($this->design == 'lemongras') {
            // Pre-SASS-Design but uncompressed
            $content .= $this->getCss('lemongras', false);
            $compressedContent = $this->compressResult($content);
            return $compressedContent;
        } else {
            $content .= $this->getCss($this->design, true, 3, null);

            // Custom variables
            if ($this->getSession()->hasActiveSession() &&
                $this->getSession()->getPlusLevel() >= \vc\object\Plus::PLUS_TYPE_STANDARD) {
                $customDesignModel = $this->getDbModel('CustomDesign');
                $customDesignObject = $customDesignModel->loadObject(
                    array('profile_id' => $this->getSession()->getUserId())
                );
                if ($customDesignObject !== null) {
                    $customColors = json_decode($customDesignObject->colors, true);
                    if (!empty($customColors)) {
                        foreach ($customColors as $colorId => $colorValue) {
                            $content .= '$' . \vc\object\CustomDesign::$keys[$colorId] .= ':' . $colorValue . ';';
                        }
                    }
                }
            }

            $content .= $this->getCss($this->design, true, null, 4);

            // Import external fonts
            $content = preg_replace_callback('~@import \'([^\']*)\';~', array($this, 'sassImport'), $content);

            // Replace placeholders
            $content = str_replace(
                array(
                    '%VERSION%',
                    '%LOCALE%'
                ),
                array(
                    \vc\config\Globals::VERSION,
                    $this->getLocale()
                ),
                $content
            );

            $scss = new \scssc();
            $scss->setFormatter('scss_formatter_compressed');
            $compiledContent = $scss->compile($content);
            if ($this->getSession()->hasActiveSession() && !empty($customDesignObject)) {
                $compiledContent .= $customDesignObject->css;
            }

            $compressedContent = $this->compressResult($compiledContent);
            return $compressedContent;
        }
    }

    private function getCss($design, $removeDuplicates = true, $till = null, $from = null)
    {
        $cssDesignRootDir = TEMPLATE_DIR . '/' . $design . '/css';
        if (!file_exists($cssDesignRootDir)) {
            return null;
        }

        $sourceFiles = scandir($cssDesignRootDir);

        $fullContent = '';
        $trees = array();

        foreach ($sourceFiles as $sourceFile) {
            if (substr($sourceFile, -4) === '.css' ||
                substr($sourceFile, -5) === '.scss') {
                $number = intval($sourceFile);

                if (($till === null || $number <= $till) &&
                    ($from === null || $number >= $from)) {
                    if (!array_key_exists($number, $trees)) {
                        $trees[$number] = array();
                    }

                    $contents = file_get_contents($cssDesignRootDir . '/' . $sourceFile);

                    // Filter simple and multiline comments
                    $contents = $this->filterComments($contents);

                    // Removing unnecessary whitespace
                    $contents = preg_replace('/:(\s)+/', ':', $contents);
                    $contents = preg_replace('/,(\s)+/', ',', $contents);
                    $contents = preg_replace('/>(\s)+/', '>', $contents);
                    $contents = preg_replace('/(\s)+/', ' ', $contents);

                    if ($number === 0) {
                        // Sass-Code-Only -> Just import
                        $fullContent .= $contents;
                    } else {
                        $this->buildTree($contents, $trees[$number]);
                    }
                }
            }
        }


        foreach ($trees as $treeBranch) {
// :TODO: using this breaks the honeypot (and probably some others)
//            if ($removeDuplicates) {
//                $treeBranch = $this->removeDuplicates($treeBranch);
//            }
            $fullContent .= $this->collapseTreeBranch($treeBranch);
        }

        return $fullContent;
    }

    private function filterComments($contents)
    {
        while (strpos($contents, '/*') !== false) {
            $startIndex = strpos($contents, '/*');
            $endIndex = strpos($contents, '*/', $startIndex + 1);
            $contents = substr($contents, 0, $startIndex) .
                        substr($contents, $endIndex + 2);
        }

        $filteredContents = '';
        $start = 0;
        $end = strlen($contents);
        while ($start !== false) {
            $index = strpos($contents, '//', $start);
            if ($index === false) {
                $filteredContents .= substr($contents, $start, $end - $start);
                $start = false;
            } else {
                $filteredContents .= substr($contents, $start, $index - $start);
                if ($index > 5 &&
                    (
                        substr($contents, $index - 6, 6) === 'https:' ||
                        substr($contents, $index - 5, 5) === 'http:'
                    )) {
                    $filteredContents .= '//';
                    $start = $index + 2;
                } else {
                    $lineBreakIndex = strpos($contents, "\n", $index);
                    if ($lineBreakIndex === false) {
                        // File ends with comment
                        $start = false;
                    } else {
                        $start = $lineBreakIndex + 1;
                    }
                }
            }
        }

        return $filteredContents;
    }

    private function buildTree($contents, &$map)
    {
        $start = 0;
        $end = strlen($contents);

        $keys = array();
        $currentKey = null;
        $currentSpecial = null;
        $specialLevel = null;

        while ($start < $end) {
            $semicolonIndex = strpos($contents, ';', $start);
            $openIndex = strpos($contents, '{', $start);
            $closeIndex = strpos($contents, '}', $start);
            $recalculateKey = false;

            $index = min(
                $semicolonIndex === false ? $end : $semicolonIndex,
                $openIndex === false ? $end : $openIndex,
                $closeIndex === false ? $end : $closeIndex
            );

            $line = trim(substr($contents, $start, $index - $start));

            if ($index === $semicolonIndex) {
                if (!empty($line)) {
                    if ($currentSpecial === null) {
                        $map[$currentKey][] = $line;
                    } else {
                        $map[$currentSpecial][$currentKey][] = $line;
                    }
                }
            } elseif ($index === $openIndex) {
                if (strpos($line, '@media') === 0 ||
                    strpos($line, '@keyframes') === 0 ||
                    in_array($line, self::$nonCollapseElements)) {
                    $currentSpecial = $line;
                    $specialLevel = count($keys);

                    if (!array_key_exists($currentSpecial, $map)) {
                        $map[$currentSpecial] = array();
                    }

                    if (in_array($line, self::$nonCollapseElements)) {
                        $currentKey = count($map[$currentSpecial]);
                        $map[$currentSpecial][] = array();
                    }
                } else {
                    $keys[] = $line;
                    $recalculateKey = true;
                }
            } elseif ($index === $closeIndex) {
                if ($currentSpecial !== null && count($keys) == $specialLevel) {
                    $currentSpecial = null;
                    $specialLevel = null;
                } else {
                    array_pop($keys);
                    $recalculateKey = true;
                }
            } else {
                if (!empty($line)) {
                    \vc\lib\ErrorHandler::error(
                        'CSS: Non empty content',
                        __FILE__,
                        __LINE__,
                        array(
                            'line' => $line
                        )
                    );
                }
            }

            if ($recalculateKey) {
                $expandedKey = array();
                foreach ($keys as $key) {
                    $explodedKey = explode(',', $key);
                    if (empty($expandedKey)) {
                        $newExpandedKey = $explodedKey;
                    } else {
                        $newExpandedKey = array();
                        foreach ($expandedKey as $keyRoot) {
                            foreach ($explodedKey as $keyBranch) {
                                $newExpandedKey[] = $keyRoot . ' ' . $keyBranch;
                            }
                        }
                    }
                    $expandedKey = $newExpandedKey;
                }
                $currentKey = str_replace(' &', '', implode(',', $expandedKey));

                if ($currentSpecial === null) {
                    if (!array_key_exists($currentKey, $map)) {
                        $map[$currentKey] = array();
                    }
                } else {
                    if (!array_key_exists($currentKey, $map[$currentSpecial])) {
                        $map[$currentSpecial][$currentKey] = array();
                    }
                }
            }

            $start = $index + 1;
        }
    }

    private function removeDuplicates(&$treeBranch)
    {
        $valueMapping = array();
        $duplicates = array();

        foreach ($treeBranch as $key => $values) {
            $valueHaveArray = false;
            foreach ($values as $value) {
                if (is_array($value)) {
                    $valueHaveArray = true;
                }
            }

            if ($valueHaveArray === false) {
                $collapsedValues = implode(';', $values);
                if (!empty($collapsedValues)) {
                    if (array_key_exists($collapsedValues, $valueMapping)) {
                        if (!array_key_exists($collapsedValues, $duplicates)) {
                            $duplicates[$collapsedValues] = array(
                                'keys' => array(
                                    $valueMapping[$collapsedValues]
                                ),
                                'values' => $values
                            );
                        }
                        $duplicates[$collapsedValues]['keys'][] = $key;
                    } else {
                        $valueMapping[$collapsedValues] = $key;
                    }
                }
            }
        }

        foreach ($duplicates as $duplicate) {
            // Remove duplicate entries
            foreach ($duplicate['keys'] as $key) {
                unset($treeBranch[$key]);
            }
            $treeBranch[implode(',', $duplicate['keys'])] = $duplicate['values'];
        }

        return $treeBranch;
    }

    private function collapseTreeBranch($treeBranch)
    {
        $content = '';
        $mediaContent = '';
        foreach ($treeBranch as $key => $value) {
            if (is_array($value)) {
                if (!empty($value)) {
                    if (empty($key)) {
                        $content .= $this->collapseTreeBranch($value);
                    } elseif (in_array($key, self::$nonCollapseElements)) {
                        foreach ($value as $subValue) {
                            $content .= $key .
                                        '{' .
                                        $this->collapseTreeBranch($subValue) .
                                        '}' . "\n";
                        }
                    } else {
                        // Adding line breaks for easier debugging. Not actually delivered to enduser since it
                        // is processed before that
                        $subContent = $key .
                                      '{' .
                                      $this->collapseTreeBranch($value) .
                                      '}' . "\n";

                        if (strpos($key, '@media ') === 0) {
                            $mediaContent .= $subContent;
                        } else {
                            $content .= $subContent;
                        }
                    }
                }
            } else {
                $content .= $value . ';';
            }
        }
        return $content . $mediaContent;
    }

    private function compressResult($content)
    {
        return str_replace(
            array(
                "\n", // Line breaks
                ';}', // Semicolon before close bracket
                ' > ', // Unnecessary spaces
                '0.' // Prefix zero
            ),
            array(
                '', // Line breaks
                '}', // Semicolon before close bracket
                '>', // Unnecessary spaces,
                '.'
            ),
            $content
        );
    }

    public function sassImport($match)
    {
        $curlContent = \vc\helper\CurlHelper::call(
            $match[1],
            false,
            array(),
            array('Accept-Language', 'de,en-US;q=0.7,en;q=0.3')
        );
        if (!empty($curlContent)) {
            $curlContent = preg_replace('/:(\s)+/', ':', $curlContent);
            $curlContent = preg_replace('/,(\s)+/', ',', $curlContent);
            $curlContent = preg_replace('/>(\s)+/', '>', $curlContent);
            $curlContent = preg_replace('/(\s)+/', ' ', $curlContent);
        }
        return $curlContent;
    }
}
