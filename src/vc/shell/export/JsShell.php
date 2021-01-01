<?php
namespace vc\shell\export;

class JsShell extends \vc\shell\AbstractShell
{
    public function run()
    {
        $contents = '';
        foreach (\vc\config\Globals::$jsFiles['uncompressed'] as $file) {
            $contents .= file_get_contents(APP_ROOT . '/js/' . $file);
        }

        while (strpos($contents, '/*') !== false) {
            $startIndex = strpos($contents, '/*');
            $endIndex = strpos($contents, '*/', $startIndex + 1);
            $contents = substr($contents, 0, $startIndex) .
                        substr($contents, $endIndex + 2);
        }

        echo $contents;
    }
}
