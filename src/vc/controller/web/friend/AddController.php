<?php
namespace vc\controller\web\friend;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.add.noactivesession'));
            return;
        }
        if (empty($_POST["profileid"])) {
            \vc\lib\ErrorHandler::warning(
                "The parameter 'profileid' is not set.",
                __FILE__,
                __LINE__
            );
            echo \vc\view\json\View::renderStatus(false, gettext('friend.add.failed'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $newFriend = intval($_POST["profileid"]);
        if (in_array($newFriend, $this->getOwnFriendsConfirmed()) ||
            in_array($newFriend, $this->getOwnFriendsToConfirm()) ||
            in_array($newFriend, $this->getOwnFriendsWaitForConfirm())) {
            echo \vc\view\json\View::renderStatus(true, gettext('friend.add.alreadyset'));
            return;
        }

        $friendModel = $this->getDbModel('Friend');
        $success = $friendModel->addFriend($this->getSession()->getUserId(), $newFriend);
        if (!$success) {
            echo \vc\view\json\View::renderStatus(false, gettext('friend.add.failed'));
        } else {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_FRIEND_REQUEST
            );
        
            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations($this->getSession()->getUserId());
            $cacheModel->resetProfileRelations($newFriend);

            // inform the user about the friendrequest
            $settings = $cacheModel->getSettings($newFriend);
            if ($settings->getValue(\vc\object\Settings::NEW_FRIEND_NOTIFICATION)) {
                $mailComponent = $this->getComponent('Mail');
                $currentUser = $this->getSession()->getProfile();
                $mailComponent->sendMailToUser(
                    $this->locale,
                    $newFriend,
                    'friend.add.mail.subject',
                    'friendrequest',
                    array(
                        'nickname' => $currentUser->nickname,
                        'userId' => $this->getSession()->getUserId(),
                        'userLink' => 'user/view/' . $this->getSession()->getUserId() . '/'
                    ),
                    array(
                        'user-' . $this->getSession()->getUserId() => array('user', $this->getSession()->getUserId())
                    )
                );
            }
            echo \vc\view\json\View::renderStatus(true, gettext('friend.add.success'));
        }
    }
}
