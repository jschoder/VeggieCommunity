<?php
namespace vc\controller\web\mod;

class SwitchUserController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin() || count($this->siteParams) === 0) {
            throw new \vc\exception\NotFoundException();
        }

        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getProfiles($this->locale, array(intval($this->siteParams[0])));
        if (count($profiles) === 0) {
            throw new \vc\exception\NotFoundException();
        }

        $adminUser = $this->getSession()->getProfile();

        $modMessageModel = $this->getDbModel('ModMessage');
        $modMessageModel->addMessage(
            $this->getSession()->getUserId(),
            $this->getIp(),
            'MOD :: Switching from ' . $adminUser->nickname . ' to ' . $profiles[0]->nickname
        );

        $this->getSession()->killSession();
        $this->getSession()->updateSession($profiles[0]);

        throw new \vc\exception\RedirectException($this->path . 'mysite/');
    }
}
