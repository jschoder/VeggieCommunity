<?php
namespace vc\shell\import;

// http://download.geonames.org/export/zip/

class UpdateGeonamesShell extends \vc\shell\AbstractShell
{
    public function run()
    {
//        $this->importCountry('AT', 43);
//        $this->importCountry('CA', 2);
//        $this->importCountry('CH', 41);
//        $this->importCountry('DE', 49);
//        $this->importCountry('ES', 34);
//        $this->importCountry('FR', 33);
//        $this->importCountry('GB', 44);
//        $this->importCountry('NL', 31);
//        $this->importCountry('NZ', 64);
//        $this->importCountry('US', 1);

        // Updating the database. Cleaning up the structure.
        echo('Optimize' . "\n");
        if (!$this->isTestMode()) {
            $this->getDb()->execute('OPTIMIZE TABLE vc_geoname');
        }

        $this->importOldCoordinates();

        echo('Complete' . "\n");
    }

    private function importCountry($isoCode, $countryId)
    {
        echo('Import ' . $isoCode . ' / ' . $countryId . "\n");


        $path = APP_ROOT . '/data/geonames/' . $isoCode . '.zip';
        if (!file_exists($path)) {
            echo('!!! Zip missing' . "\n");
            return;
        }

        if (!$this->isTestMode()) {
            // Deleting country from the database
            // :TODO: JOE - deprecated
            $this->getDb()->delete('DELETE FROM vc_geoname WHERE country_id = ' . intval($countryId));
        }

        $archive = new  \ZipArchive();
        if ($archive->open($path)) {
            $stream = $archive->getStream($isoCode . '.txt');
            if (!$stream) {
                echo('!!! Stream missing' . "\n");
                return;
            }

            $postalCodes = 0;
            while (($buffer = fgets($stream, 4096)) !== false) {
                $values = explode("\t", $buffer);

                //The data format is tab-delimited text in utf8 encoding, with the following fields :
                //
                // country code      : iso country code, 2 characters
                // postal code       : varchar(20)
                // place name        : varchar(180)
                // admin name1       : 1. order subdivision (state) varchar(100)
                // admin code1       : 1. order subdivision (state) varchar(20)
                // admin name2       : 2. order subdivision (county/province) varchar(100)
                // admin code2       : 2. order subdivision (county/province) varchar(20)
                // admin name3       : 3. order subdivision (community) varchar(100)
                // admin code3       : 3. order subdivision (community) varchar(20)
                // latitude          : estimated latitude (wgs84)
                // longitude         : estimated longitude (wgs84)
                // accuracy          : accuracy of lat/lng from 1=estimated to 6=centroid

                if (count($values) < 11) {
                    echo("!!! Invalid line [" . $isoCode . "]: " . $buffer);
                } else {
                    if (!empty($values[9]) && !empty($values[10])) {
                        if (!$this->isTestMode()) {
                            $this->getDb()->executePrepared(
                                'INSERT INTO vc_geoname SET
                                    country_id = ?,
                                    postal_code = ?,
                                    place_name = ?,
                                    latitude = ?,
                                    longitude = ?,
                                    accuracy = ?',
                                array(
                                    intval($countryId),
                                    $values[1],
                                    $values[2],
                                    $values[9],
                                    $values[10],
                                    intval($values[11])
                                )
                            );
                        }
                    }
                }
                $postalCodes++;
            }
        } else {
            echo('!!! Can\'t open path.' . "\n");
            return;
        }
        echo('Imported ' . $postalCodes . ' postCodes.' . "\n");
    }

    private function importOldCoordinates()
    {

        echo("Importing old coordinates \n");

        $updatedProfiles = 0;
        $geoComponent = $this->getComponent('Geo');
        $query = 'SELECT id, postalcode, residence, region, country
                  FROM vc_profile
                  WHERE latitude = 0 AND longitude = 0 AND (postalcode != \'\' OR residence != \'\') AND active >= 0';
        // :TODO: JOE - deprecated
        $result = $this->getDb()->select($query);
        while ($row = $result->fetch_row()) {
            $coordinates = $geoComponent->getCoordinates('en', $row[1], $row[2], $row[3], $row[4], false);
            if ($coordinates[0] != 0 || $coordinates[1] != 0) {
                echo('   - ' . implode(', ', $row) . ' => ' . $coordinates[0] . ';' . $coordinates[1] . "\n");

                $query = 'UPDATE vc_profile SET ' .
                         "latitude=" . number_format($coordinates[0], 6, '.', '') . "," .
                         "longitude=" . number_format($coordinates[1], 6, '.', '') . "," .
                         "sin_latitude = SIN(PI() * " . number_format($coordinates[0], 6, '.', '') . "/180)," .
                         "cos_latitude = COS(PI() * " . number_format($coordinates[0], 6, '.', '') . "/180)," .
                         "longitude_radius = PI() * " . number_format($coordinates[1], 6, '.', '') . "/180 " .
                         "WHERE id = " . intval($row[0]);
                if (!$this->isTestMode()) {
                    // :TODO: JOE - deprecated
                    $this->getDb()->update($query);
                }
                $updatedProfiles++;
            }
        }
        echo($updatedProfiles . ' profiles have been updated. ' . "\n");
    }
}
