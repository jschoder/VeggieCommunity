<?php
namespace vc\controller\web\account;

class PrivacyPolicyController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('privacypolicy.title'));
        $this->getView()->set('activeMenuitem', 'privacypolicy');

        $this->getView()->set('title', gettext('privacypolicy.title'));
        $this->getView()->setHeader('robots', 'noindex, follow');

        $termsModel = $this->getDbModel('Terms');
        $latestVersion = $termsModel->getLatestVersion(\vc\object\Terms::TYPE_PRIVACY_POLICY, $this->locale);
        $this->getView()->set('longtext', $latestVersion->content);

        echo $this->getView()->render('account/longtext', true);
    }
}
