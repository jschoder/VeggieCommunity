<?php
namespace vc\controller\web\friend;

class AcceptController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.accept.noactivesession'));
            return;
        }

        if (empty($_POST["profileid"])) {
            \vc\lib\ErrorHandler::warning(
                "The parameter 'profileid' is not set.",
                __FILE__,
                __LINE__
            );
            echo \vc\view\json\View::renderStatus(false, gettext('friend.accept.failed'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $friend = intval($_POST['profileid']);
        if (!in_array($friend, $this->getOwnFriendsToConfirm())) {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.accept.missing'));
            return;
        }

        $friendModel = $this->getDbModel('Friend');
        $success = $friendModel->acceptRequest($this->getSession()->getUserId(), $friend);
        if ($success) {
            $this->getEventService()->added(
                \vc\config\EntityTypes::FRIEND,
                $this->getSession()->getUserId(),
                $friend
            );
            echo \vc\view\json\View::renderStatus(true, gettext('friend.accept.success'));
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.accept.failed'));
            return;
        }
    }
}
