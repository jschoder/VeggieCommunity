<?php
namespace vc\helper;

class CurlHelper
{
    public static function call(
        $url,
        $post = false,
        $postFields = array(),
        $customHeaders = array(),
        $userPassword = null
    ) {
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
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        }
        if ($userPassword !== null) {
            curl_setopt($ch, CURLOPT_USERPWD, $userPassword);
        }

        $webpage = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            \vc\lib\ErrorHandler::warning(
                'Invalid httpCode response ' . $httpCode . ' for curl call.',
                __FILE__,
                __LINE__,
                array(
                    'url' => $url,
                    'post' => $post,
                    'postFields' => $postFields,
                    'customHeaders' => $customHeaders,
                    'userPassword' => $userPassword,
                    'webpage' => $webpage,
                )
            );
            $webpage = null;
        }
        curl_close($ch);
        return $webpage;
    }

    public static function downloadUrlToFile($url, $file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $st = curl_exec($ch);
        $fd = fopen($file, 'w');
        fwrite($fd, $st);
        fclose($fd);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            \vc\lib\ErrorHandler::warning(
                'Invalid httpCode response ' . $httpCode . ' for curl download.',
                __FILE__,
                __LINE__,
                array(
                    'url' => $url,
                    'file' => $file
                )
            );
        } elseif (!file_exists($file)) {
            \vc\lib\ErrorHandler::warning(
                'File doesn\'t exist after download',
                __FILE__,
                __LINE__,
                array(
                    'url' => $url,
                    'file' => $file
                )
            );
        }

        curl_close($ch);
    }
}
