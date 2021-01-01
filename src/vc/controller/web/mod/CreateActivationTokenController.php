<?php
namespace vc\controller\web\mod;

class CreateActivationTokenController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin() || count($this->siteParams) == 0) {
            throw new \vc\exception\NotFoundException();
        }

        $profileId = $this->siteParams[0];
        $activationTokenModel = $this->getDbModel('ActivationToken');
        $activationTokenModel->addUniqueToken($profileId, time());

        throw new \vc\exception\RedirectException($this->path . 'user/view/' . $profileId . '/mod/');
    }
}
