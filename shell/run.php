<?php

require_once('../bootstrap.php');

try {
    if (count($argv) > 2) {
        $subPath = $argv[1];
        $shellName = $argv[2];

        $shellClassName = '\\vc\\shell\\' . $subPath . '\\' . $shellName . 'Shell';

        if (!class_exists($shellClassName)) {
            echo 'File ' . $shellClassName . ' doesn\'t exit.';
            // :TODO: migrator2 - errorhandling

        } else {
            $params = array();
            foreach ($argv as $arg) {
                $dividerPos = strpos($arg, ':');
                if ($dividerPos > 0 && strlen($arg) > $dividerPos + 1) {
                    $key = substr($arg, 0, $dividerPos);
                    $value = substr($arg, $dividerPos + 1);

                    $params[$key] = $value;
                }
            }

            $shell = new $shellClassName();
            $shell->setParams($params);
            $shell->setup();
            $shell->run();
        }
    } else {
        // :TODO: migrator2 - errorhandling
    }
} catch (Exception $exception) {
    \vc\lib\ErrorHandler::error(
        get_class($exception) . ': ' . $exception->getMessage(),
        __FILE__,
        __LINE__
    );
    echo get_class($exception) . ': ' . $exception->getMessage();
}
