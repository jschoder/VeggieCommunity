<?php
namespace vc\shell\cron\task\workers\daily;

class GeoIpTask extends \vc\shell\cron\task\AbstractCronTask
{
    private $success = false;

    public function execute()
    {
        if (date("w", time()) == 3) {
            $this->prepareOld();
            $file = $this->download();
            $this->import($file);
            $this->cleanup();
        }
    }

    private function prepareOld()
    {
        $query = 'UPDATE LOW_PRIORITY geoip_country SET updated = 0';
        $statement = $this->getDb()->prepare($query);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while preparing old: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array()
            );
        }
        $statement->close();
    }

    private function download()
    {
        $file = tempnam(TMP_DIR, 'GeoIPCountryCSV');
        \vc\helper\CurlHelper::downloadUrlToFile(
            'https://geolite.maxmind.com/download/geoip/database/GeoLite2-Country-CSV.zip',
            $file
        );
        return $file;
    }

    private function import($file)
    {
        $archive = new  \ZipArchive();
        if ($archive->open($file)) {
            $blockEntry = null;
            $locationsEntry = null;
            for ($i = 0; $i < $archive->numFiles; $i++) {
                $filename = $archive->getNameIndex($i);
                if (strpos($filename, '/GeoLite2-Country-Blocks-IPv4.csv') !== FALSE) {
                    $blockEntry = $filename;
                }
                if (strpos($filename, '/GeoLite2-Country-Locations-en.csv') !== FALSE) {
                    $locationsEntry = $filename;
                }
            }
        
            if (empty($blockEntry) || empty($locationsEntry)) {
                echo('!!! Entry missing' . "\n");
                \vc\lib\ErrorHandler::error(
                    'Can\'t read county whois (#1).',
                    __FILE__,
                    __LINE__
                );      
                return;
            }
        
            $stream = $archive->getStream($locationsEntry);
            if (!$stream) {
                echo('!!! Stream (locations) missing' . "\n");
                \vc\lib\ErrorHandler::error(
                    'Can\'t read county whois (#2).',
                    __FILE__,
                    __LINE__
                );
                return;
            }

            $locations = array();
            while (($buffer = fgets($stream, 4096)) !== false) {
                $row = explode(',', $buffer);
                if (!empty($row[0]) && !empty($row[1])) {
                    $id = intval($row[0]);
                    $country = $row[4];
                    
                    if (!empty($id) && mb_strlen($row[4]) === 2) {
                        $locations[$id] = $country;
                    }
                }
            }
        
            $stream = $archive->getStream($blockEntry);
            if (!$stream) {
                echo('!!! Stream (block) missing' . "\n");
                \vc\lib\ErrorHandler::error(
                    'Can\'t read county whois (#2).',
                    __FILE__,
                    __LINE__
                );
                return;
            }

            while (($buffer = fgets($stream, 4096)) !== false) {
                $row = explode(',', $buffer);
                if (count($row) < 2) {
                    continue;
                }
                if (!array_key_exists($row[1], $locations)) {
                    continue;
                }
                
                
                $range = $this->cidrToRange($row[0]);
                
                $ipFrom = intval($range[0]);
                $ipTo = intval($range[1]);
                $iso2 = $locations[$row[1]];
                
                if (empty($ipFrom) || empty($ipTo) || empty($iso2) || mb_strlen($iso2) !== 2) {
                    \vc\lib\ErrorHandler::error(
                        'GeoModel - Invalid row',
                        __FILE__,
                        __LINE__,
                        array(
                            'network' => $row[0],
                            'ipFrom' => $ipFrom,
                            'ipTo' => $ipTo,
                            'iso2' => $iso2
                        )
                    );
                    continue;
                }

                $query = 'INSERT LOW_PRIORITY INTO geoip_country SET ip_from = ?, ip_to = ?, iso2 = ?, updated = 1
                          ON DUPLICATE KEY UPDATE updated = 1';
                $statement = $this->getDb()->prepare($query);
                $statement->bind_param('iis', $ipFrom, $ipTo, $iso2);
                $executed = $statement->execute();
                if (!$executed) {
                    \vc\lib\ErrorHandler::error(
                        'Error while inserting geo ip: ' . $statement->errno . ' / ' . $statement->error,
                        __FILE__,
                        __LINE__,
                        array('ipFrom' => $ipFrom,
                              'ipTo' => $ipTo,
                              'iso2' => $iso2)
                    );
                }
                $statement->close();
            }
            $this->success = true;
        }
    }

    private function cidrToRange($cidr) {
        $range = array();
        $cidr = explode('/', $cidr);
        if (count($cidr) !== 2) {
            return null;
        }
        
        $range = array();
        $range[] = ip2long($cidr[0]) & (-1 << (32 - (int)$cidr[1]));
        $range[] = $range[0] + pow(2, (32 - (int)$cidr[1])) - 1;
        return $range;
    }
    
    private function cleanup()
    {
        if ($this->success) {
            $query = 'DELETE LOW_PRIORITY FROM geoip_country WHERE updated = 0';
            $statement = $this->getDb()->prepare($query);
            $executed = $statement->execute();
            if (!$executed) {
                \vc\lib\ErrorHandler::error(
                    'Error while cleaning up geo ip: ' . $statement->errno . ' / ' . $statement->error,
                    __FILE__,
                    __LINE__,
                    array()
                );
            }
            $statement->close();
        }
    }
}
