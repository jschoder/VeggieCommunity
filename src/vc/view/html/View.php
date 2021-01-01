<?php
namespace vc\view\html;

class View
{
    private $design;

    private $designConfig;

    private $header = array();

    private $timeOffset = 0;

    private $script = '';

    public function __construct($design, $designConfig)
    {
        $this->design = $design;
        $this->designConfig = $designConfig;
    }

    public function set($name, $value)
    {
        $this->$name = $value;
        \vc\lib\ErrorHandler::getInstance()->setViewVariable($name, $value);
    }

    public function setHeader($name, $value)
    {
        $this->header[$name] = $value;
        \vc\lib\ErrorHandler::getInstance()->setViewVariable('header.' . $name, $value);
    }

    public function render($file, $fullPage = false)
    {
        $templateFile = $this->getTemplateFile($file . '.tpl.php');
        if ($templateFile !== null) {
            $content = $this->renderTemplate($templateFile);
            if ($fullPage) {
                $this->set('content', $content);
                $fullTemplateFile =$this->getTemplateFile('page.tpl.php');
                if ($fullTemplateFile !== null) {
                    $content = $this->renderTemplate($fullTemplateFile);
                } else {
                    \vc\lib\ErrorHandler::warning(
                        'Full-Template-file can\'t be found.',
                        __FILE__,
                        __LINE__
                    );
                    throw new \vc\exception\NotFoundException('Full-Template-file can\'t be found.');
                }
            }
            return $content;
        } else {
            \vc\lib\ErrorHandler::warning(
                "Template-file can't be found: " . $file .
                " [" . var_export($this->designConfig->getTemplateDirectories(), true) . "]",
                __FILE__,
                __LINE__
            );
            throw new \vc\exception\NotFoundException(
                "Template-file can't be found: " . $file .
                " [" . var_export($this->designConfig->getTemplateDirectories(), true) . "]"
            );
        }
    }

    public function element($elementPath, $params = array())
    {
        $elementView = new View($this->design, $this->designConfig);
        foreach ($params as $key => $param) {
            $elementView->$key = $param;
        }

        $templateFile = $elementView->getTemplateFile('elements/' . $elementPath . '.tpl.php');
        if ($templateFile !== null) {
            return $elementView->renderTemplate($templateFile);
        } else {
            \vc\lib\ErrorHandler::warning(
                "Element-Template-file can't be found: " . $elementPath,
                __FILE__,
                __LINE__
            );
            throw new \vc\exception\NotFoundException(
                "Element-Template-file can't be found: " . $elementPath
            );
        }
    }

    private function getTemplateFile($subPath)
    {
        $templateDirectories = $this->designConfig->getTemplateDirectories();
        foreach ($templateDirectories as $templateDirectory) {
            $templateFile = TEMPLATE_DIR . '/' . $templateDirectory .'/'  . $subPath;
            if (file_exists($templateFile)) {
                return $templateFile;
            }
        }
        return null;
    }

    protected function renderTemplate($templateFile)
    {
        ob_start();
        require($templateFile);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    protected function renderForm(\vc\form\Form $form)
    {
        return $form->render($this);
    }

    public function escapeLink($link)
    {
        $link = str_replace(
            array(
                'a','b','c','d','e','f','g','h','i','j','k','l','m','n',
                'o','p','q','r','s','t','u','v','w','x','y','z','/',':'
            ),
            array(
                '&#97;','&#98;','&#99;','&#100;','&#101;','&#102;','&#103;','&#104;','&#105;','&#106;','&#107;',
                '&#108;','&#109;','&#110;','&#111;','&#112;','&#113;','&#114;','&#115;','&#116;','&#117;','&#118;',
                '&#119;','&#120;','&#121;','&#122;','&#47;','&#58;'
            ),
            $link
        );
        return $link;
    }

    public function escapeAttribute($attribute)
    {
        if (empty($attribute)) {
            return '';
        }
        return htmlspecialchars($attribute, ENT_QUOTES);
    }

    public function setTimeOffset($timeOffset)
    {
        $this->timeOffset = $timeOffset;
    }

    // :TODO: find a better place for this
    public function prepareDate($format, $servertime)
    {
        $localtime = $servertime - ($this->timeOffset * 60);
        return date($format, $localtime);
    }

    public function getShortDate($servertime)
    {
        $date = gettext('date.day.readable');
        switch (date('n', $servertime)) {
            case 1:
                $month = gettext('date.month.short.January');
                break;
            case 2:
                $month = gettext('date.month.short.February');
                break;
            case 3:
                $month = gettext('date.month.short.March');
                break;
            case 4:
                $month = gettext('date.month.short.April');
                break;
            case 5:
                $month = gettext('date.month.short.May');
                break;
            case 6:
                $month = gettext('date.month.short.June');
                break;
            case 7:
                $month = gettext('date.month.short.July');
                break;
            case 8:
                $month = gettext('date.month.short.August');
                break;
            case 9:
                $month = gettext('date.month.short.September');
                break;
            case 10:
                $month = gettext('date.month.short.October');
                break;
            case 11:
                $month = gettext('date.month.short.November');
                break;
            case 12:
                $month = gettext('date.month.short.December');
                break;
        }
        return str_replace(array('d', 'm'), array(date('j', $servertime), $month), gettext('date.day.readable'));
    }

    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function echoWideAd($locale, $plusLevel)
    {
//        if (empty($plusLevel)) {
//            $this->echoAd($locale, 'wide', $locale == 'de' ? 1 : 2);
//        }
    }

    private function echoAd($locale, $class, $zoneId)
    {
//        $adContent = '';
//        if (strpos($_SERVER['SERVER_NAME'], 'veggiecommunity.org') === false) {
//            $random = rand(1, 99999);
//            $adContent = '<a href="https://www.veggiecommunity.org/v/www/admin/index.php" target="_blank">' .
//                            '<img src="/img/banner/roc.gif" border="0" alt="" />' .
//                         '</a>';
//        } else {
//            if (@include_once(MAX_PATH . '/www/delivery/alocal.php')) {
//                if (!isset($phpAds_context)) {
//                    $phpAds_context = array();
//                }
//
//                $phpAdsRaw = view_local('', $zoneId, 0, 0, '', '', '0', $phpAds_context, '');
//                $adContent = $phpAdsRaw['html'];
//
//                // Reset of timezone necessary since OpenX changes it to UTC
//                OA_setTimeZone('Europe/Berlin');
//            }
//        }
//
//        if (!empty($adContent)) {
//            echo $this->element(
//                'ad',
//                array(
//                    'locale' => $locale,
//                    'class' => $class,
//                    'adContent' => $adContent
//                )
//            );
//        }
    }

    public function addScript($script)
    {
        $this->script .= $script;
    }

    public function getScript()
    {
        return $this->script;
    }
}
