<?php
namespace vc\model\service\module\block\user;

class SuggestionBlockModule extends \vc\model\service\module\block\AbstractBlockModule
{
    private $usersOnline;

    private $blocked;

    public function __construct($usersOnline, $blocked)
    {
        $this->usersOnline = $usersOnline;
        $this->blocked = $blocked;
    }

    protected function isUserSpecific()
    {
        return true;
    }

    protected function render($path, $imagesPath)
    {
        $userId = $this->getUserId();
        if (empty($userId)) {
            $profiles = array();
            $pictures = array();
        } else {
            $matchModel = $this->getDbModel('Match');
            $profileIds = $matchModel->getSuggestedMembers(
                $this->getUserId(),
                $this->blocked,
                8
            );

            if (count($profileIds) === 0) {
                $profiles = array();
                $pictures = array();
            } else {
                $profileModel = $this->getDbModel('Profile');
                $profiles = $profileModel->getSmallProfiles($this->getLocale(), $profileIds);
                $pictureModel = $this->getDbModel('Picture');
                $userId = $this->getUserId();
                $pictures = $pictureModel->readPictures($userId, $profiles);
            }
        }

        return $this->element(
            'block/user/suggestion',
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
        return 86400;
    }
}
