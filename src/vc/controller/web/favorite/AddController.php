<?php
namespace vc\controller\web\favorite;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('favorite.add.noactivesession'));
            return;
        }
        if (empty($_POST["profileid"])) {
            echo \vc\view\json\View::renderStatus(false, gettext('favorite.add.failed'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $newFavorite = intval($_POST["profileid"]);
        $favoriteModel = $this->getDbModel('Favorite');
        $favoriteCount = $favoriteModel->getCount(array(
            'profileid' => $this->getSession()->getUserId(),
            'favoriteid' => $newFavorite,
        ));
        if ($favoriteCount > 0) {
            echo \vc\view\json\View::renderStatus(true, gettext('favorite.add.alreadyset'));
            return;
        }

        $success = $favoriteModel->addFavorite($this->getSession()->getUserId(), $newFavorite);
        if ($success) {
            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations($this->getSession()->getUserId());
            echo \vc\view\json\View::renderStatus(true, gettext('favorite.add.success'));
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('favorite.add.failed'));
        }
    }
}
