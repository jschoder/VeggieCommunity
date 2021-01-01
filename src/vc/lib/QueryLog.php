<?php
namespace vc\lib;

class QueryLog
{
    public static function log($time, $query, $params)
    {
        $reportContent = 'TIME ::: ' . $time . "\n" .
                   'QUERY ::: ' . $query . "\n" .
                   'PARAMS ::: ' . json_encode($params);

        $reportName = realpath(APP_ROOT . '/logs/') . '/' . date('H:i:s') . ':::' . rand(0, 9999999);

        $f=fopen($reportName, 'a');
        fwrite($f, $reportContent);
        fclose($f);
        chmod($reportName, 16895); // 0777
    }
}
