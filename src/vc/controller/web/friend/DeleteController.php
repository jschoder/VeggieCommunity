<?php
namespace vc\controller\web\friend;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.delete.noactivesession'));
            return;
        }
        if (empty($_POST["profileid"])) {
            \vc\lib\ErrorHandler::warning(
                "The parameter 'profileid' is not set.",
                __FILE__,
                __LINE__
            );
            echo \vc\view\json\View::renderStatus(false, gettext('friend.delete.failed'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $friend = $request->getInt('profileid');

        $this->getEventService()->preDelete(
            \vc\config\EntityTypes::FRIEND,
            $friend,
            $this->getSession()->getUserId()
        );

        $friendModel = $this->getDbModel('Friend');
        $success = $friendModel->deleteFriend($this->getSession()->getUserId(), $friend);
        if ($success) {
            $this->getEventService()->deleted(
                \vc\config\EntityTypes::FRIEND,
                $friend,
                $this->getSession()->getUserId()
            );

            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.delete.failed'));
        }
    }
}
