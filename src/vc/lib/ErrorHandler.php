<?php
namespace vc\lib;

class ErrorHandler
{

// :TODO: implement file and/or db logging

    private static $instance;

    private $viewVariables;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ErrorHandler();
        }
        return self::$instance;
    }

    public static function error($errstr, $errfile, $errline, $debugInfo = null)
    {
        self::getInstance()->saveReport(E_ERROR, $errstr, $errfile, $errline, '-', $debugInfo);
    }

    public static function warning($errstr, $errfile, $errline, $debugInfo = null)
    {
        self::getInstance()->saveReport(E_WARNING, $errstr, $errfile, $errline, '-', $debugInfo);
    }

    public static function notice($errstr, $errfile, $errline, $debugInfo = null)
    {
        self::getInstance()->saveReport(E_NOTICE, $errstr, $errfile, $errline, '-', $debugInfo);
    }

    public function saveReport($errno, $errstr, $errfile, $errline, $errquery, $debugInfo = null)
    {
        $backtraceExport = '';
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $errors = "Notice";
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $errors = "Warning";
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $errors = "Fatal Error";
                break;
            default:
                $errors = "Unknown (" . $errno . ")";
                break;
        }

        $reportContent =
            "user: " . $this->getUserId() . "\n" .
            "date: " . date("Y-m-d H:i:s") . "\n" .
            "\n" .
            "no: " . $errno . "\n" .
            "no-type: " . $errors . "\n" .
            "str: " . $errstr . "\n" .
            "file: " . $errfile . " : " . $errline. "\n" .
            "query: " . $errquery . "\n" .
            "\n";
        if ($debugInfo !== null) {
            $reportContent .= "\nDEBUG INFO\n" .
                              var_export($debugInfo, true) . "\n";
        }

        $trace = '';
        foreach (debug_backtrace() as $traceLine) {
            if (!empty($traceLine['file']) && !empty($traceLine['line'])) {
                $trace .= $traceLine['file'] . ' : ' . $traceLine['line'] . "\n";
            } else {
                $trace .= @var_export($traceLine, true) . "\n";
            }
        }
        if (!empty($trace)) {
            $reportContent .= 'TRACE: ' . $trace . "\n";
        }
        $reportContent .=
            self::getSystemDebugInfo() .
            "\n" .
            "VIEW:\n" .
            $this->getTextFromArray($this->viewVariables, "  ") .
            "\n" .
            "_GET\n" .
            $this->getTextFromArray($_GET, "  ") .
            "\n" .
            "_POST\n" .
            $this->getTextFromArray($_POST, "  ") .
            "\n" .
            "_SESSION\n" .
            $this->getTextFromArray($_SESSION, "  ") .
            "\n" .
            "_COOKIE\n" .
            $this->getTextFromArray($_COOKIE, "  ") .
            "\n";

        $reportDayDirectory = APP_REPORTS . '/' . date("Y-m-d") . "/";
        if (!file_exists(APP_REPORTS)) {
            mkdir(APP_REPORTS);
            chmod(APP_REPORTS, 16895); // 0777
        }
        if (!file_exists($reportDayDirectory)) {
            mkdir($reportDayDirectory);
            chmod($reportDayDirectory, 16895); // 0777
        }
        $reportBaseName= $reportDayDirectory . date("H-i-s");
        $reportName = $reportBaseName . ".log";
        for ($i2=2; file_exists($reportName); $i2++) {
            $reportName = $reportBaseName . "-" . $i2 .".log";
        }
        $f=fopen($reportName, 'a');
        fwrite($f, $reportContent);
        fclose($f);
        chmod($reportName, 16895); // 0777
    }

    public static function getSystemDebugInfo($longVersion = true)
    {
        if ($longVersion) {
            return self::getServerVariable('REMOTE_ADDR') .
                   self::getServerVariable('HTTP_CLIENT_IP') .
                   self::getServerVariable('HTTP_X_FORWARDED_FOR') .
                   self::getServerVariable('HTTP_USER_AGENT') .
                   "\n" .
                   self::getServerVariable('REQUEST_URI') .
                   self::getServerVariable('REQUEST_METHOD') .
                   self::getServerVariable('SERVER_PROTOCOL') .
                   self::getServerVariable('HTTP_REFERER') .
                   "\n" .
                   self::getServerVariable('HTTP_ACCEPT') .
                   self::getServerVariable('HTTP_ACCEPT_CHARSET') .
                   self::getServerVariable('HTTP_ACCEPT_ENCODING') .
                   self::getServerVariable('HTTP_ACCEPT_LANGUAGE');
        } else {
            return self::getServerVariable('REMOTE_ADDR') .
                   self::getServerVariable('HTTP_CLIENT_IP') .
                   self::getServerVariable('HTTP_X_FORWARDED_FOR') .
                   self::getServerVariable('HTTP_USER_AGENT') .
                   self::getServerVariable('HTTP_REFERER');
        }
    }

    private static function getServerVariable($key)
    {
        global $_SERVER;
        if (array_key_exists($key, $_SERVER)) {
            return $key . ": " . $_SERVER[$key] . "\n";
        } else {
            return "";
        }
    }

    private function getTextFromArray(&$array, $indent)
    {
        $text = "";
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    if (count($value) == 0) {
                        $text .= $indent . $key . "=[EMPTY_ARRAY]\n";
                    } else {
                        $text .= $indent . $key . "[array]\n" . $this->getTextFromArray($value, $indent . "   ") . "\n";
                    }
                } elseif (is_object($value)) {
                    $text .= $indent . $key . " = [OBJECT]\n";
                } else {
                    $text .= $indent . $key . " = " . $value . "\n";
                }
            }
        }
        return $text;
    }

    private function getUserId()
    {
        global $_SESSION;
        if (empty($_SESSION['currentProfile'])) {
            return 0;
        } else {
            return $_SESSION['currentProfile']->id;
        }
    }

    public function setViewVariable($name, $value)
    {
        $this->viewVariables[$name] = $value;
    }
}
