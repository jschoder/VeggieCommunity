<?php
namespace vc\controller\web\pm;

class PmController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('menu.messages'));

        $termsModel = $this->getDbModel('Terms');
        if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
            throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
        }

        $pmThreadModel = $this->getDbModel('PmThread');
        $threads = $pmThreadModel->getThreads(
            $this->getSession()->getUserId(),
            \vc\config\Globals::THREAD_COUNT_INITIAL
        );

        if (count($threads) === 0) {
            $defaultContact = 0;
        } else {
            foreach ($threads as $i => $thread) {
                $threads[$i]['lastMessage'] = prepareHTML($thread['lastMessage']);
                switch ($thread['contact']['gender']) {
                    case 2:
                        $threads[$i]['contact']['gender'] = 'm';
                        break;
                    case 4:
                        $threads[$i]['contact']['gender'] = 'f';
                        break;
                    case 6:
                        $threads[$i]['contact']['gender'] = 'o';
                        break;
                    default:
                        $threads[$i]['contact']['gender'] = 'a';
                }
            }
            $defaultContact = $threads[0]['contact']['id'];
        }

        $customSidebar = $this->getView()->element('pm.sidebar', array());

        $this->getView()->set('hideMenu', true);
        $this->getView()->set('customSidebar', $customSidebar);
        $this->getView()->set('threads', $threads);
        $this->getView()->set('defaultContact', $defaultContact);
        $this->getView()->set('filterOutgoing', $this->getSession()->getSetting(\vc\object\Settings::PM_FILTER_OUTGOING));

        echo $this->getView()->render('pm/pm', true);
    }
}
