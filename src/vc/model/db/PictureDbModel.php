<?php
namespace vc\model\db;

class PictureDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_picture';
    const OBJECT_CLASS = '\\vc\object\\SavedPicture';

    private $profilePictures = array();

    public function readPictures($currentUserId, $profiles)
    {
        if (empty($profiles)) {
            return array();
        }

        $indexedProfiles = array();
        $profileIds = array();
        foreach ($profiles as $profile) {
            $indexedProfiles[$profile->id] = $profile;
            $profileIds[] = $profile->id;
        }

        $pictures = array();
        $profilePictures = $this->loadObjects(
            array('defaultpic' => 1, 'profileid' => $profileIds)
        );

        foreach ($profilePictures as $picture) {
            $addPicture = null;
            switch ($picture->visibility) {
                case \vc\object\SavedPicture::VISIBILITY_PUBLIC:
                    $addPicture = $picture;
                    break;
                case \vc\object\SavedPicture::VISIBILITY_REGISTERED:
                    if (!empty($currentUserId)) {
                        $addPicture = $picture;
                    }
                    break;
                case \vc\object\SavedPicture::VISIBILITY_FRIENDS_FAVORITES:
                    if (!empty($currentUserId)) {
                        $cacheModel = $this->getModel('Cache');
                        $relations = $cacheModel->getProfileRelations($picture->profileid);
                        if (in_array($currentUserId, $relations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED]) ||
                            in_array($currentUserId, $relations[\vc\model\CacheModel::RELATIONS_FAVORITES])) {
                            $addPicture = $picture;
                        }
                    }
                    break;
                case \vc\object\SavedPicture::VISIBILITY_FRIENDS:
                    if (!empty($currentUserId)) {
                        $cacheModel = $this->getModel('Cache');
                        $relations = $cacheModel->getProfileRelations($picture->profileid);
                        if (in_array($currentUserId, $relations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED])) {
                            $addPicture = $picture;
                        }
                    }
                    break;
            }

            if ($addPicture === null) {
                $profile = $indexedProfiles[$picture->profileid];

                $picture = new \vc\object\DefaultPicture();
                $picture->gender = $profile->gender;
                $picture->hiddenImage = true;
                $pictures[$profile->id] = $picture;
            } else {
                $pictures[$picture->profileid] = $addPicture;
            }
        }

        foreach ($profiles as $profile) {
            if (empty($pictures[$profile->id])) {
                $picture = new \vc\object\DefaultPicture();
                $picture->gender = $profile->gender;
                $picture->hiddenImage = false;
                $pictures[$profile->id] = $picture;
            }
        }

        return $pictures;
    }

    public function getFilteredProfilePicture($profile, $currentUserId)
    {
        $pictures = array(
            'default' => null,
            'album' => array(),
            'hidden' => false
        );
        $defaultPictureHidden = false;
        if (!empty($currentUserId)) {
            $cacheModel = $this->getModel('Cache');
            $relations = $cacheModel->getProfileRelations($profile->id);
        }

        foreach ($this->getAllProfilePictures($profile->id) as $picture) {
            $addPicture = null;
            switch ($picture->visibility) {
                case \vc\object\SavedPicture::VISIBILITY_PUBLIC:
                    $addPicture = $picture;
                    break;
                case \vc\object\SavedPicture::VISIBILITY_REGISTERED:
                    if (!empty($currentUserId)) {
                        $addPicture = $picture;
                    } else {
                        if ($picture->defaultpic) {
                            $defaultPictureHidden = true;
                        }
                    }
                    break;
                case \vc\object\SavedPicture::VISIBILITY_FRIENDS_FAVORITES:
                    if (!empty($currentUserId) &&
                        (
                            in_array($currentUserId, $relations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED]) ||
                            in_array($currentUserId, $relations[\vc\model\CacheModel::RELATIONS_FAVORITES])
                        )) {
                        $addPicture = $picture;
                    } else {
                        if ($picture->defaultpic) {
                            $defaultPictureHidden = true;
                        }
                    }
                    break;
                case \vc\object\SavedPicture::VISIBILITY_FRIENDS:
                    if (!empty($currentUserId) &&
                        in_array($currentUserId, $relations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED])) {
                        $addPicture = $picture;
                    } else {
                        if ($picture->defaultpic) {
                            $defaultPictureHidden = true;
                        }
                    }
                    break;
            }
            if ($addPicture === null) {
                $pictures['hidden'] = true;
            } else {
                if ($addPicture->defaultpic) {
                    $pictures['default'] = $addPicture;
                }
                $pictures['album'][] = $addPicture;
            }
        }

        if (empty($pictures['default'])) {
            $picture = new \vc\object\DefaultPicture();
            $picture->gender = $profile->gender;
            $picture->hiddenImage = $defaultPictureHidden;
            $pictures['default'] = $picture;
        }

        return $pictures;
    }

    public function getAllProfilePictures($profileId)
    {
        return $this->loadObjects(
            array('profileid' => intval($profileId)),
            array(),
            'weight ASC, id ASC'
        );
    }

    /**
     * Updates the oldest picture if none is the default picture.
     * Updates none if one (or more) pictures are marked as defaultpic.
     *
     * @param int $profileId
     */
    public function updateDefaultPicture($profileId)
    {
        $query = 'UPDATE vc_picture
                 SET defaultpic = 1
                 WHERE vc_picture.profileid = ?
                 ORDER BY defaultpic DESC, id ASC
                 LIMIT 1';
        return $this->getDb()->executePrepared($query, array(intval($profileId)));
    }

    public function deletePictures($profileId, $pictureIds)
    {
        $pictureIds = array_map('intval', $pictureIds);

        $query = 'DELETE FROM vc_activity
                  WHERE
                      vc_activity.activity_type = ? AND
                      vc_activity.message IN (SELECT filename FROM vc_picture WHERE
                          vc_picture.profileid = ? AND
                          vc_picture.id IN (' . $this->fillQuery(count($pictureIds)) . '))';
        $params = array_merge(
            array(
                \vc\object\ActivityReport::TYPE_PICTURE_ADDED,
                $profileId
            ),
            $pictureIds
        );
        $activityDel = $this->getDb()->executePrepared($query, $params);

        $deleted = $this->delete(
            array(
                'id' => $pictureIds,
                'profileid' => $profileId
            )
        );

        return $deleted;
    }

    public function removeWatermarkPictures($profileId)
    {
        $pictures = $this->getAllProfilePictures($profileId);
        foreach ($pictures as $picture) {
            $file = PROFILE_PIC_DIR . '/full-watermark/' . $picture->filename;
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
