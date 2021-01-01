<?php
namespace vc\shell\import;

class QueryLogShell extends \vc\shell\AbstractShell
{
    private $queries = array();

    public function run()
    {
        echo 'Reading files ';
        $directory = APP_ROOT . '/logs/logs/';

        if ($reportDirHandle = opendir($directory)) {
            $files = 0;
            while (false !== ($file = readdir($reportDirHandle))) {
                if ($file != '.' && $file != '..' && !is_dir($file)) {
                    if (strpos($file, '.rar') === false) {
                        $log = file_get_contents($directory . $file);
                        $this->addLog($log);
                        $files++;
                    }

                    if ($files % 1000 === 0) {
                        echo '.';
                    }
                }
            }
        }
        echo "done \n\n";

        uasort(
            $this->queries,
            function ($a, $b) {
                return $a[1] - $b[1];
            }
        );

        foreach ($this->queries as $query => $results) {
            $avgTime = round($results[1] / $results[0], 4);

            if ($results[1] > 0.05 || $avgTime > 0.01) {
                echo $results[0] . ' / ' . $results[1] . ' [' . $avgTime . ']    :::   ' . $query . "\n\n";
            }
        }

//        var_dump(array_values($queries));
    }

    private function addLog($log)
    {
        $queryLocation = mb_strpos($log, 'QUERY ::: ');
        $paramsLocation = mb_strpos($log, 'PARAMS ::: ');

        $time = floatval(str_replace(',', '.', mb_substr($log, 9, $queryLocation - 10)));
        $query = mb_substr($log, $queryLocation + 10, $paramsLocation - $queryLocation - 11);
//                    $params = mb_substr($log, $paramsLocation + 11);

        if (array_key_exists($query, $this->queries)) {
            $this->queries[$query][0] = $this->queries[$query][0] + 1;
            $this->queries[$query][1] = $this->queries[$query][1] + $time;
        } else {
            $this->queries[$query] = array(1, $time);
        }
    }
}
