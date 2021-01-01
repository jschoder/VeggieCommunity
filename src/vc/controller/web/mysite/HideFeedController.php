<?php
namespace vc\controller\web\mysite;

class HideFeedController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.noactivesession'));
            return;
        }

        if (!$request->hasParameter('id')) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $request->getValues()
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.hide.failed'));
            return;
        }

        $threadModel = $this->getDbModel('ForumThread');
        $threadId = $threadModel->getField('id', 'hash_id', $request->getText('id'));

        if (empty($threadId)) {
            $deleted = false;
            // :TODO: JOE !!! - suspicion + error
        } else {
        $feedThreadModel = $this->getDbModel('FeedThread');
            $deleted = $feedThreadModel->delete(
                array(
                    'user_id' => $this->getSession()->getUserId(),
                    'thread_id' => $threadId
                )
            );
        }

        if ($deleted) {
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.hide.failed'));
        }
    }
}
