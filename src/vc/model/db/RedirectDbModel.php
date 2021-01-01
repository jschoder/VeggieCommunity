<?php
namespace vc\model\db;

class RedirectDbModel extends AbstractDbModel
{
    public function addRedirect($url)
    {
        $now = date('Y-m-d H:i:s');
        return $this->getDb()->executePrepared(
            'INSERT INTO vc_redirect SET url = ?, count = 1, last_redirect = ?
             ON DUPLICATE KEY UPDATE count = count + 1, last_redirect = ?',
            array(
                $url,
                $now,
                $now
            )
        );
    }
}
