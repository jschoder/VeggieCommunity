<?php
namespace vc\model\db;

class CustomDesignDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_custom_design';
    const OBJECT_CLASS = '\\vc\object\\CustomDesign';
    
    public function set($profileId, $customColors, $customCss)
    {
        $query = 'INSERT INTO vc_custom_design SET ' .
                 'profile_id = ?, colors = ?, css = ? ' .
                 'ON DUPLICATE KEY UPDATE colors = ?, css = ?';
        $statement = $this->getDb()->prepare($query);
        $statement->bind_param('issss', $profileId, $customColors, $customCss, $customColors, $customCss);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while setting custom design: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('customColors' => $customColors,
                      'customCss' => $customCss)
            );
            return false;
        }

        return true;
    }
}
