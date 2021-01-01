<?php
namespace vc\model\db;

class JavascriptLogDbModel extends AbstractDbModel
{
    public function insert($profileId, $userAgent, $url, $line, $message)
    {
        $query = 'INSERT INTO vc_javascript_log SET
                    profile_id = ?,
                    user_agent = ?,
                    url = ?,
                    line = ?,
                    message = ?,
                    created_at = ?';
        $inserted = $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                strlen($userAgent) > 255 ? substr($userAgent, 0, 255) : $userAgent,
                strlen($url) > 255 ? substr($url, 0, 255) : $url,
                intval($line),
                strlen($message) > 255 ? substr($message, 0, 255) : $message,
                date('Y-m-d H:i:s')
            )
        );
    }
}