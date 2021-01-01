<?php
namespace vc\controller\web;

class ChatController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        // Check status of profile. Block if sending user has been deleted.
        if ($this->getSession()->hasActiveSession() && $this->autoLogout()) {
            throw new \vc\exception\RedirectException($this->path);
        }

        $this->setTitle(gettext("menu.chat"));
        $this->getView()->set('activeMenuitem', 'chat');

        $chatOnlineModel = $this->getDbModel('AjaxChatOnline');
        $profileIDs = $chatOnlineModel->getFieldList(
            'userID',
            array(
                'dateTime >' => date('Y-m-d H:i:00', time() - 600)
            )
        );

        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getSmallProfiles($this->locale, $profileIDs, 'last_update DESC');

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures($this->getSession()->getUserId(), $profiles);

        $this->getView()->set('profiles', $profiles);
        $this->getView()->set('pictures', $pictures);

        echo $this->getView()->render('chat', true);
    }
}
