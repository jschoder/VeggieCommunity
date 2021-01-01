<?php
namespace vc\controller\web;

class ShareController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('share.title'));

        if ($this->site == 'user/share' &&
           count($this->siteParams) > 0 &&
           is_numeric($this->siteParams[0])) {
            $defaultMessage = gettext("share.tellafriend.user.message");
            $defaultMessage = str_replace(
                "%PROFILEID%",
                $this->siteParams[0],
                $defaultMessage
            );
            $currentUser = $this->getSession()->getProfile();
            $this->getView()->set('defaultSenderName', $currentUser->nickname);
            $this->getView()->set('defaultSenderEmail', $currentUser->email);
            $this->getView()->set('defaultSubject', gettext('share.tellafriend.user.subject'));
            $this->getView()->set('defaultMessage', $defaultMessage);

            $this->getView()->set(
                'pathToShare',
                'https://www.veggiecommunity.org/' . $this->locale . '/user/view/' . $this->siteParams[0] . '/'
            );
        } else {
            throw new \vc\exception\NotFoundException();
        }
        echo $this->getView()->render('share', true);
    }
}
