<?php
namespace vc\controller\web\friend;

class DenyController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.deny.noactivesession'));
            return;
        }
        if (empty($_POST["profileid"])) {
            \vc\lib\ErrorHandler::warning(
                "The parameter 'profileid' is not set.",
                __FILE__,
                __LINE__
            );
            echo \vc\view\json\View::renderStatus(false, gettext('friend.deny.failed'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $friend = intval($_POST['profileid']);
        if (!in_array($friend, $this->getOwnFriendsToConfirm())) {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.deny.missing'));
            return;
        }

        $friendModel = $this->getDbModel('Friend');
        $success = $friendModel->denyRequest($this->getSession()->getUserId(), $friend);
        if ($success) {
            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations($this->getSession()->getUserId());
            $cacheModel->resetProfileRelations($friend);

            echo \vc\view\json\View::renderStatus(true, gettext('friend.deny.success'));
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.deny.failed'));
        }
    }
}
