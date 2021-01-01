<?php
namespace vc\model\service\module\block\user;

class UpdatedBlockModule extends \vc\model\service\module\block\AbstractBlockModule
{
    private $usersOnline;

    public function __construct($usersOnline)
    {
        $this->usersOnline = $usersOnline;
    }

    protected function isUserSpecific()
    {
        return true;
    }

    protected function render($path, $imagesPath)
    {
        if (empty($this->getUserId())) {
            $blocked = array();
        } else {
            $cacheModel = $this->getModel('Cache');
            $profileRelations = $cacheModel->getProfileRelations($this->getUserId());
            $blocked = $profileRelations[\vc\model\CacheModel::RELATIONS_BLOCKED];
        }
        $profileModel = $this->getDbModel('Profile');
        $profileIds = $profileModel->getProfileIdsByColumn(
            'last_update',
            12,
            $blocked
        );

        if (count($profileIds) === 0) {
            $profiles = array();
            $pictures = array();
        } else {
            $profiles = $profileModel->getSmallProfiles($this->getLocale(), $profileIds);
            $pictureModel = $this->getDbModel('Picture');
            $userId = $this->getUserId();
            $pictures = $pictureModel->readPictures($userId, $profiles);
        }

        return $this->element(
            'block/user/updated',
            array(
                'path' => $path,
                'imagesPath' => $imagesPath,
                'profiles' => $profiles,
                'pictures' => $pictures,
                'usersOnline' => $this->usersOnline
            )
        );
    }

    protected function getCacheExpires()
    {
        return 1200;
    }
}
