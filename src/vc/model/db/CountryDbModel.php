<?php
namespace vc\model\db;

class CountryDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_country';

    public function getCountries($locale, $ipCountry, $defaultCountries)
    {
        $query = 'SELECT id, name_' . $locale . ' FROM vc_country ORDER BY name_' . $locale;
        $statement = $this->getDb()->queryPrepared($query);
        $statement->bind_result($id, $name);
        $indexedCountryNames = array();
        while ($statement->fetch()) {
            $indexedCountryNames[$id] = $name;
        }
        $statement->close();

        $countries = array();
        if (!empty($ipCountry) && array_key_exists($ipCountry, $indexedCountryNames)) {
            $countries[] = array($ipCountry, $indexedCountryNames[$ipCountry]);
        }
        if ($defaultCountries) {
            if (array_key_exists($locale, \vc\config\Globals::$defaultCountries)) {
                $defaultCountries = \vc\config\Globals::$defaultCountries[$locale];
            } else {
                // @codeCoverageIgnoreStart
                // Skipping fallback since there is currently no other language in the database
                $defaultCountries = array();
                // @codeCoverageIgnoreEnd
            }
            foreach ($defaultCountries as $countryKey) {
                if ($countryKey != $ipCountry) {
                    $countries[] = array($countryKey, $indexedCountryNames[$countryKey]);
                }
            }
        }
        foreach ($indexedCountryNames as $id => $name) {
            $countries[] = array($id, $name);
        }
        return $countries;
    }
}
