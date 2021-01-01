<?php
namespace vc\controller\web\account;

class TermsOfServiceController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('termsofuse.title'));
        $this->getView()->set('activeMenuitem', 'termsofservice');

        $this->getView()->set('title', gettext('termsofuse.title'));
        $this->getView()->setHeader('robots', 'noindex, follow');

        $termsModel = $this->getDbModel('Terms');
        $latestVersion = $termsModel->getLatestVersion(\vc\object\Terms::TYPE_TERMS_OF_USE, $this->locale);
        $this->getView()->set('longtext', $latestVersion->content);

        echo $this->getView()->render('account/longtext', true);
    }
}
