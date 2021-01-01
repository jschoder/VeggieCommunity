<?php
namespace vc\shell\export;

class PageViewLogShell extends \vc\shell\AbstractShell
{
    public function run()
    {
        if (intval(date('N')) === 1 || $this->getParam('force')) {
            $pagelogFile = APP_REPORTS .
                           '/pagelog-' . \vc\config\Globals::VERSION . '-'  . date('Y-m-d', time() - 86400) . '.csv';
            if (file_exists($pagelogFile)) {
                unlink($pagelogFile);
            }

            $df = fopen($pagelogFile, 'w');
            fputcsv(
                $df,
                array(
                    'request_method',
                    'site',
                    'site_params',
                    'session_id',
                    'profile_id',
                    'script_time',
                    'created_at'
                )
            );

            if($this->getParam('force')) {
                $condition = '';
            } else {
                $condition = 'WHERE created_at < TIMESTAMP(current_date)';
            }

            $statement = $this->getDb()->queryPrepared(
                'SELECT * FROM vc_page_view_log ' . $condition . ' ORDER BY created_at ASC'
            );
            $statement->bind_result(
                $requestMethod,
                $site,
                $siteParams,
                $sessionId,
                $profileId,
                $scriptTime,
                $createdAt
            );
            while ($statement->fetch()) {
                fputcsv(
                    $df,
                    array(
                        $requestMethod,
                        $site,
                        $siteParams,
                        $sessionId,
                        $profileId,
                        $scriptTime,
                        $createdAt
                    )
                );
            }
            $statement->close();

            fclose($df);

            $this->getDb()->queryPrepared('DELETE FROM vc_page_view_log ' . $condition);
        }
    }
}
