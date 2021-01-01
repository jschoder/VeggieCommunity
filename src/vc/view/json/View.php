<?php
namespace vc\view\json;

class View
{
    // :TODO: make this an instance function
    public static function renderStatus($success, $message = null)
    {
        $return = array('success' => $success,
                        'message' => empty($message) ? '' : $message);
        header('Content-Type:  application/json', true);
        $encoded = json_encode($return);
        if (json_last_error() !== 0) {
            \vc\lib\ErrorHandler::getInstance()->setViewVariable('success', $success);
            \vc\lib\ErrorHandler::getInstance()->setViewVariable('message', $message);
            \vc\lib\ErrorHandler::error(
                'JSON-Error (1): ' . json_last_error_msg(),
                __FILE__,
                __LINE__
            );
        }
        return $encoded;
    }

    public static function render($status)
    {
        header('Content-Type:  application/json', true);
        $encoded = json_encode($status);
        if (json_last_error() !== 0) {
            \vc\lib\ErrorHandler::getInstance()->setViewVariable('status', $status);
            \vc\lib\ErrorHandler::error(
                'JSON-Error (2): ' . json_last_error_msg(),
                __FILE__,
                __LINE__
            );
        }
        return $encoded;
    }
}
