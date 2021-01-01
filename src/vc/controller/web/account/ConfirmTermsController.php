<?php
namespace vc\controller\web\account;

class ConfirmTermsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        $this->view();
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        \vc\lib\Assert::assertLong('terms_of_use_id', $_POST['terms_of_use_id'], 1, 99999, true);
        \vc\lib\Assert::assertLong('privacy_policy_id', $_POST['privacy_policy_id'], 1, 99999, true);

        if (!empty($_POST['terms_of_use_id']) &&
            !empty($_POST['privacy_policy_id'])) {
            $termsModel = $this->getDbModel('Terms');
            $termsModel->saveTerms(
                \vc\object\Terms::TYPE_TERMS_OF_USE,
                $_POST['terms_of_use_id'],
                $this->getSession()->getUserId(),
                $this->getIp()
            );
            $termsModel->saveTerms(
                \vc\object\Terms::TYPE_PRIVACY_POLICY,
                $_POST['privacy_policy_id'],
                $this->getSession()->getUserId(),
                $this->getIp()
            );
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }
    }

    private function view()
    {
        $this->setTitle(gettext('menu.mysite'));
        $this->getView()->set('activeMenuitem', 'mysite');
        $this->getView()->setHeader('robots', 'noindex, follow');

        if ($this->getSession()->hasActiveSession()) {
            $termsModel = $this->getDbModel('Terms');
            if ($termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
                throw new \vc\exception\RedirectException($this->path . 'mysite/');
            } else {
                $this->getView()->set(
                    'termsOfUse',
                    $termsModel->getLatestVersion(\vc\object\Terms::TYPE_TERMS_OF_USE, $this->locale)
                );
                $this->getView()->set(
                    'privacyPolicy',
                    $termsModel->getLatestVersion(\vc\object\Terms::TYPE_PRIVACY_POLICY, $this->locale)
                );
                $this->getView()->set(
                    'changes',
                    $termsModel->getChanges($this->locale, $this->getSession()->getUserId())
                );
            }
        } else {
            throw new \vc\exception\LoginRequiredException();
        }
        echo $this->getView()->render('account/confirmTerms', true);
    }
}
