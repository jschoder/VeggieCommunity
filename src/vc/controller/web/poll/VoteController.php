<?php
namespace vc\controller\web\poll;

class VoteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if (!empty($_POST['poll_id']) && !empty($_POST['selected_option'])) {
            $pollModel = $this->getDbModel('Poll');
            $pollModel->addVote(
                intval($_POST['poll_id']),
                intval($_POST['selected_option']),
                $this->getSession()->getUserId()
            );
        }

        if (array_key_exists('redirect', $_POST)) {
            throw new \vc\exception\RedirectException($_POST['redirect']);
        } else {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }
    }
}
