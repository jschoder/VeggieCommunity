<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license GNU Affero General Public License
 * @link https://blueimp.net/ajax/
 */

if(!array_key_exists('HTTPS', $_SERVER) || 
   $_SERVER['HTTPS'] != 'on') {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
        
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    saveReport(
        $errno,
        $errstr,
        $errfile,
        $errline,
        '-'
    );
}


function fatalHandler()
{
    $error = error_get_last();
    if( $error !== NULL) {
	saveReport(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line'],
            '-'
        );
    }
}

function saveReport($errno, $errstr, $errfile, $errline, $errquery, $debugInfo = null)
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
        "user: - \n" .
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
    ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();
    if (!empty($trace)) {
        $reportContent .= 'TRACE: ' . $trace . "\n";
    }
    $reportContent .=
        "_GET\n" .
        getTextFromArray($_GET, "  ") .
        "\n" .
        "_POST\n" .
        getTextFromArray($_POST, "  ") .
        "\n" .
        "_SESSION\n" .
        getTextFromArray($_SESSION, "  ") .
        "\n" .
        "_COOKIE\n" .
        getTextFromArray($_COOKIE, "  ") .
        "\n";

    $directory = realpath(dirname(__FILE__) . '/../main/reports/');

    // Only log if error directory exists
    if (file_exists($directory)) {
        $reportDayDirectory = $directory . '/' . date("Y-m-d") . "/";
        if (!file_exists($directory)) {
            mkdir($directory);
            chmod($directory, 16895); // 0777
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
}

function getTextFromArray(&$array, $indent)
{
    $text = "";
    if (!empty($array)) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (count($value) == 0) {
                    $text .= $indent . $key . "=[EMPTY_ARRAY]\n";
                } else {
                    $text .= $indent . $key . "[array]\n" . getTextFromArray($value, $indent . "   ") . "\n";
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

set_error_handler("customErrorHandler");
register_shutdown_function( "fatalHandler" );

// Show all errors:
//error_reporting(E_ALL);

// Path to the chat directory:
define('AJAX_CHAT_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');

// Include custom libraries and initialization code:
require(AJAX_CHAT_PATH.'lib/custom.php');

// Include Class libraries:
require(AJAX_CHAT_PATH.'lib/classes.php');

// Initialize the chat:
$ajaxChat = new CustomAJAXChat();
