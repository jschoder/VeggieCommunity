<?php
namespace vc\shell\test;

class TestJob
{
    private $shell;
    private $rootUrl;

    private $username;
    private $password;
    private $locale;
    private $designs;
    private $followLinks;

    private $initialUrls;
    private $checkedUrls = array();
    private $urlPipeline;
    private $referers = array();


    private $htmlParser;
    private $cookieFile;

    public function __construct(
        $shell,
        $rootUrl,
        $username,
        $password,
        $locale,
        $designs,
        $followLinks,
        $initialUrls = array()
    ) {
        $this->shell = $shell;
        $this->rootUrl = $rootUrl;
        $this->username = $username;
        $this->password = $password;
        $this->locale = $locale;
        $this->designs = $designs;
        $this->followLinks = $followLinks;

        $this->initialUrls = $initialUrls;
        $this->urlPipeline = $initialUrls;

        $this->htmlParser = new \Masterminds\HTML5();
        $this->cookieFile = sys_get_temp_dir() . '/cookie.txt';
    }

    public function run()
    {
        // Logging
        if ($this->username === null) {
            echo 'Anonymous [' . $this->locale . '] ' . "\n";
        } else {
            echo 'Login[' . $this->username . '|' . $this->locale . '] ' . "\n";
            $this->curl(
                $this->rootUrl . '/' . $this->locale . '/login/',
                true,
                array('login-email' => $this->username, 'login-password' => $this->password)
            );
        }

        // Parsing the urls
        while (!empty($this->urlPipeline)) {
            $subPath = array_shift($this->urlPipeline);
            $this->checkedUrls[] = $subPath;

            foreach ($this->designs as $design) {
                $url = $this->rootUrl . $subPath;
                if (strpos($url, '?') === false) {
                    $url .= '?DESIGN=' . $design;
                } else {
                    $url .= '&DESIGN=' . $design;
                }
                $return = $this->curl($url);

                if (!in_array($subPath, $this->initialUrls) &&
                    $return['HttpCode'] !== 200) {
                    $this->shell->addError(
                        array(
                            'url' => $url,
                            'error' => 'Invalid HttpCode',
                            'details' => $return['HttpCode'],
                            'referer' => array_key_exists($subPath, $this->referers) ? $this->referers[$subPath] : ''
                        )
                    );
                } else {
                    if ($subPath == '/sitemap.xml') {
                        $this->parseSitemap($url, $return['Content']);
                    } elseif (strpos($return['ContentType'], 'text/html') === 0) {
                        $this->parseHtml($url, $return['Content'], $design);
                    }
                }
            }

            if (count($this->checkedUrls) % 100 === 0) {
                echo ' [' . count($this->checkedUrls) . '|' . count($this->urlPipeline) . ']';
                file_put_contents(
                    './urls-' . $this->username . '-' . $this->locale . '.txt',
                    implode("\n", $this->getCheckedUrls())
                );
            }
        }

        echo ' [' . count($this->checkedUrls) . ']';

        if ($this->username !== null) {
            $this->curl($this->rootUrl . '/' . $this->locale . '/logout/');
            unlink($this->cookieFile);
        }
    }

    private function curl($url, $post = false, $postFields = array(), $customHeaders = array(), $userPassword = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        if (empty($customHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        }
        if ($userPassword !== null) {
            curl_setopt($ch, CURLOPT_USERPWD, $userPassword);
        }

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        return array(
            'HttpCode' => $httpCode,
            'ContentType' => $contentType,
            'Content' => $content
        );
    }

    private function parseHtml($url, $html, $design)
    {
        $htmlDocument = $this->htmlParser->loadHTML($html);
        if ($this->htmlParser->hasErrors()) {
            $errors = $this->htmlParser->getErrors();
            if (!empty($errors) &&
                $errors[0] === 'Line 0, Col 0: No DOCTYPE specified.') {
                array_shift($errors);
            }
            if (!empty($errors) &&
                $design == 'lemongras' &&
                $errors[0] === 'Line 1, Col 120: Malformed DOCTYPE.') {
                array_shift($errors);
            }

            if (!empty($errors)) {
                $this->shell->addError(
                    array(
                        'url' => $url,
                        'error' => 'Invalid HTML',
                        'details' => var_export($errors, true)
                    )
                );
            }
        }

        $this->linksHaveTrailingSlash($url, $htmlDocument);

        if ($this->followLinks) {
            $this->addLinksToPipeline($url, $htmlDocument);
        }
    }

    private function parseSitemap($sourceUrl, $xml)
    {
        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($xml);
        if ($sxe === false) {
            $this->shell->addError(
                array(
                    'url' => $sourceUrl,
                    'error' => 'Invalid xml',
                    'details' => implode("\n", libxml_get_errors())
                )
            );
        } else {
            $urls = array();

            foreach ($sxe->url as $url) {
                $urls[] = $url->loc;
            }

            $this->urlsHaveTrailingSlash($sourceUrl, $urls);

            if ($this->followLinks) {
                foreach ($urls as $url) {
                    $url = str_replace('https://www.veggiecommunity.org', '', $url);

                    if (!in_array($url, $this->checkedUrls) &&
                        !in_array($url, $this->urlPipeline)) {
                        // Don't include logout-link or other unnecessary links
                        if (!$this->skipLink($url)) {
                            $this->urlPipeline[] = $url;
                            $this->referers[$url] = $sourceUrl;
                        }
                    }
                }
            }
        }
    }

    private function linksHaveTrailingSlash($url, $htmlDocument)
    {
        $urls = array();

        // A
        $aNodes = $htmlDocument->getElementsByTagName('a');
        foreach ($aNodes as $node) {
            $hrefAttributeNode = $node->attributes->getNamedItem('href');
            if ($hrefAttributeNode !== null) {
                $urls[] = $hrefAttributeNode->textContent;
            }
        }

        $this->urlsHaveTrailingSlash($url, $urls);
    }

    private function urlsHaveTrailingSlash($rootUrl, $urls)
    {
        foreach ($urls as $url) {
            if (strpos($url, '#') !== 0 &&
                substr($url, -1) !== '/' &&
                substr($url, -4) !== '.pdf' &&
                substr($url, -4) !== '.png' &&
                substr($url, -4) !== '.jpg' &&
                stripos($url, '/?') === false &&
                stripos($url, '/#') === false &&
                strpos($url, 'mailto:') !== 0 &&
                strpos($url, 'javascript:') !== 0 &&
                // Absolute paths only happen on content urls
                strpos($url, 'https:') !== 0 &&
                strpos($url, 'http:') !== 0) {
                $this->errors[] = array(
                    'url' => $rootUrl,
                    'error' => 'Link not ending with slash',
                    'details' => $url
                );
            }
        }
    }

    private function addLinksToPipeline($sourceUrl, $htmlDocument)
    {
        $aNodes = $htmlDocument->getElementsByTagName('a');
        foreach ($aNodes as $node) {
            $hrefAttributeNode = $node->attributes->getNamedItem('href');
            if ($hrefAttributeNode !== null) {
                $link = $hrefAttributeNode->textContent;

                // Strip hash parameters from url to prevent unnecessary duplicate calls
                if (strpos($link, '#') !== false) {
                    $link = substr($link, 0, strpos($link, '#'));
                }

                // Only add paths to pipeline if they start with the right locale
                // Automatically filters out pictures, hashcodes, mailtos, external links
                if (strpos($link, '/' . $this->locale . '/') === 0) {
                    // Automatically strip out any references to the design
                    foreach ($this->designs as $design) {
                        $link = str_ireplace(
                            array('?DESIGN=' . $design . '&', '?DESIGN=' . $design, '&DESIGN=' . $design),
                            array('?', '', ''),
                            $link
                        );
                    }

                    // Also only add paths that aren't in the pipeline already and
                    // haven't been checked yet.
                    if (!in_array($link, $this->checkedUrls) &&
                        !in_array($link, $this->urlPipeline)) {
                        // Don't include logout-link or other unnecessary links
                        if (!$this->skipLink($link)) {
                            $this->urlPipeline[] = $link;
                            $this->referers[$link] = $sourceUrl;
                        }
                    }
                }
            }
        }
    }

    private function skipLink($link)
    {
        if ($link === '/' . $this->locale . '/logout/' ||
            strpos($link, '/' . $this->locale . '/help/support/reportgroup/') === 0 ||
            strpos($link, '/' . $this->locale . '/help/support/reportuser/') === 0 ||
            strpos($link, '/' . $this->locale . '/mod/pictures/') === 0 ||
            strpos($link, '/' . $this->locale . '/mod/pm/') === 0 ||
            strpos($link, '/' . $this->locale . '/mod/tickets/reply/') === 0 ||
            strpos($link, '/' . $this->locale . '/user/share/') === 0 ||
            strpos($link, '/' . $this->locale . '/user/view/') === 0 ||
            strpos($link, '/' . $this->locale . '/mod/switch/') === 0) {
            return true;
        } elseif (strpos($link, '/' . $this->locale . '/user/result/') === 0) {
            $parts = parse_url($link);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                if (!empty($query['index']) && intval($query['index']) > 30) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getCheckedUrls()
    {
        return $this->checkedUrls;
    }
}
