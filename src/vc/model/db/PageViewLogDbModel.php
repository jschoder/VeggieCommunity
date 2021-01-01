<?php
namespace vc\model\db;

class PageViewLogDbModel extends AbstractDbModel
{
    const REQUEST_METHOD_GET = 1;
    const REQUEST_METHOD_POST = 2;

    public function log($requestMethod, $site, $siteParams, $sessionId, $profileId, $scriptTime)
    {
        $query = 'INSERT INTO vc_page_view_log
                  SET
                    request_method = ?,
                    site = ?,
                    site_params = ?,
                    session_id = ?,
                    profile_id = ?,
                    script_time = ?,
                    created_at = ?';
        if (empty($siteParams)) {
            $siteParamsImploded = null;
        } else {
            $siteParamsImploded = implode('/', $siteParams);
            if (strlen($siteParamsImploded) > 250) {
                $siteParamsImploded = substr($siteParamsImploded, 0, 250);
            }
        }
        if ($scriptTime > 65000) {
            $scriptTime = 65000;
        }
        $this->getDb()->executePrepared(
            $query,
            array(
                $requestMethod,
                strlen($site) > 250 ? substr($site, 0, 250) : $site,
                $siteParamsImploded,
                $sessionId,
                $profileId,
                $scriptTime,
                date('Y-m-d H:i:s')
            )
        );
    }
}
