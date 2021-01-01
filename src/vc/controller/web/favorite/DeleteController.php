<?php
namespace vc\controller\web\favorite;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('favorite.delete.noactivesession'));
            return;
        }
        if (empty($_POST["profileid"])) {
            \vc\lib\ErrorHandler::warning(
                "The parameter 'profileid' is not set.",
                __FILE__,
                __LINE__
            );
            echo \vc\view\json\View::renderStatus(false, gettext('favorite.delete.failed'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $favorite = $_POST["profileid"];
        \vc\lib\Assert::assertLong("favorite", $favorite, 1, 2147483647, false);

        $favoriteModel = $this->getDbModel('Favorite');
        $success = $favoriteModel->deleteFavorite($this->getSession()->getUserId(), $favorite);
        if ($success) {
            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations($this->getSession()->getUserId());
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('favorite.delete.failed'));
        }
    }
}
