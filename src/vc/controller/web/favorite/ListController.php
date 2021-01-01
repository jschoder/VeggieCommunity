<?php
namespace vc\controller\web\favorite;

class ListController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('mysite.tab.favorites'));

        $profileModel = $this->getDbModel('Profile');
        $favoriteProfiles = $profileModel->getSmallProfiles(
            $this->locale,
            $this->getOwnFavorites(),
            "last_update DESC"
        );
        $this->getView()->set('favoriteProfiles', $favoriteProfiles);

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures(
            $this->getSession()->getUserId(),
            $favoriteProfiles
        );
        $this->getView()->set('pictures', $pictures);

        echo $this->getView()->render('favorite/list', true);
    }
}
