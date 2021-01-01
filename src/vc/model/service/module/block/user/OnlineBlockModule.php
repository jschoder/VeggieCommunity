<?php
namespace vc\model\service\module\block\user;

class OnlineBlockModule extends \vc\model\service\module\block\AbstractBlockModule
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
        if (count($this->usersOnline) === 0) {
            $onlineUsers = array();
            $profiles = array();
            $pictures = array();
        } else {
            if (empty($this->getUserId())) {
                $blocked = array();
            } else {
                $cacheModel = $this->getModel('Cache');
                $profileRelations = $cacheModel->getProfileRelations($this->getUserId());
                $blocked = $profileRelations[\vc\model\CacheModel::RELATIONS_BLOCKED];
            }
            $onlineUsers = array_slice(array_diff($this->usersOnline, $blocked), 0, 24);

            $profileModel = $this->getDbModel('Profile');
            $profiles = $profileModel->getSmallProfiles($this->getLocale(), $onlineUsers);
            $pictureModel = $this->getDbModel('Picture');
            $userId = $this->getUserId();
            $pictures = $pictureModel->readPictures($userId, $profiles);
        }

        return $this->element(
            'block/user/online',
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
        return 180;
    }
}
