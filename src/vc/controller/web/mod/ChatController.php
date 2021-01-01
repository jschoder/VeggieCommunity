<?php
namespace vc\controller\web\mod;

class ChatController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $channelFilter = null;
        $userFilter = null;
        if (count($this->siteParams) > 1) {
            if ($this->siteParams[0] === 'channel') {
                $channelFilter = intval($this->siteParams[1]);
            } else if ($this->siteParams[0] === 'user') {
                $userFilter = intval($this->siteParams[1]);
            }
        }

        $this->setTitle('Chatlog');

        $chatMessageModel = $this->getDbModel('AjaxChatMessages');
        $this->getView()->set('messages', $chatMessageModel->getMessages($channelFilter, $userFilter));
        $this->getView()->set('userFilter', $userFilter);

        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/chat', true);
    }
}
