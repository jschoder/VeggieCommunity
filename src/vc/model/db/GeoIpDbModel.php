<?php
namespace vc\model\db;

class GeoIpDbModel extends AbstractDbModel
{
    /* Temporary caching the country id for this call */
    private $ipCountryMappingCache = array();

    public function getIso2ByIp($ip)
    {
        $iplong = ip2long($ip);
        $query = 'SELECT iso2 FROM geoip_country WHERE ? BETWEEN ip_from AND ip_to LIMIT 1';
        $statement = $this->getDb()->queryPrepared($query, array($iplong));

        $statement->bind_result($iso2);
        $fetched = $statement->fetch();
        $statement->close();
        if (!$fetched) {
            return null;
        }

        return $iso2;
    }

    public function getCountryByIp($ip)
    {
        $countryId = null;
        if (!empty($ip)) {
            if (array_key_exists($ip, $this->ipCountryMappingCache)) {
                return $this->ipCountryMappingCache[$ip];
            } else {
                $ipCountry = $this->getIso2ByIp($ip);
                if ($ipCountry !== null) {
                    $countryModel = $this->getDbModel('Country');
                    $countryId = $countryModel->getField('id', 'iso2', $ipCountry);
                }
                $this->ipCountryMappingCache[$ip] = $countryId;
            }
        }
        return $countryId;
    }
}
