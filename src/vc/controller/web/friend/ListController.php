<?php
namespace vc\controller\web\friend;

class ListController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('mysite.tab.friends'));

        $friendModel = $this->getDbModel('Friend');
        $profileModel = $this->getDbModel('Profile');
        $pictureModel = $this->getDbModel('Picture');

        $ownFriendsToConfirm = $this->getOwnFriendsToConfirm();
        if (empty($ownFriendsToConfirm)) {
            $unconfirmedProfiles = array();
            $unconfirmedPictures = array();
        } else {
            $unconfirmedProfiles = $profileModel->getSmallProfiles(
                $this->locale,
                $ownFriendsToConfirm,
                "last_update DESC"
            );

            $unconfirmedPictures = $pictureModel->readPictures(
                $this->getSession()->getUserId(),
                $unconfirmedProfiles
            );
        }
        $this->getView()->set('unconfirmedProfiles', $unconfirmedProfiles);
        $this->getView()->set('unconfirmedPictures', $unconfirmedPictures);


        $friends = $friendModel->getFriends(
            $this->getSession()->getUserId(),
            \vc\object\Friend::FILTER_CONFIRMED,
            true
        );
        $this->getView()->set('friends', $friends);

        $friendProfiles = $profileModel->getSmallProfiles(
            $this->locale,
            $this->getOwnFriendsConfirmed(),
            "last_update DESC"
        );
        $this->getView()->set('profiles', $friendProfiles);

        $pictures = $pictureModel->readPictures(
            $this->getSession()->getUserId(),
            $friendProfiles
        );
        $this->getView()->set('pictures', $pictures);

        echo $this->getView()->render('friend/list', true);
    }
}
