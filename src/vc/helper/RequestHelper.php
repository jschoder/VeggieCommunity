<?php
namespace vc\helper;

class RequestHelper
{
    public static function getIp()
    {
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            \vc\lib\ErrorHandler::error(
                'Can\'t locate ip address',
                __FILE__,
                __LINE__,
                array(
                    'SERVER' => var_export($_SERVER, true)
                )
            );
            return '';
        }
    }
}
