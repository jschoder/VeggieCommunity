<?php
namespace vc\model\db;

class ProfileHobbyDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_profile_hobby';
    const OBJECT_CLASS = '\\vc\object\\ProfileHobby';

    public function updateProfileHobbies($profileId, $hobbies)
    {
        // Deleting the not selected ones.
        if (empty($hobbies)) {
            $this->delete(
                array(
                    'profileid' => $profileId
                )
            );
            return true;

        } else {
            $this->delete(
                array(
                    'profileid' => $profileId,
                    'hobbyid NOT' => $hobbies
                )
            );

            // Adding the new hobbies (and keeping the existing ones)
            $query = 'INSERT INTO vc_profile_hobby (profileid, hobbyid)
                      VALUES (?,?)' . str_repeat(',(?,?)', count($hobbies) - 1) . '
                      ON DUPLICATE KEY UPDATE profileid = ?';
            $queryParams = array();
            foreach ($hobbies as $newHobby) {
                $queryParams[] = intval($profileId);
                $queryParams[] = intval($newHobby);
            }
            $queryParams[] = intval($profileId);

            return $this->getDb()->executePrepared($query, $queryParams);
        }
    }
}
