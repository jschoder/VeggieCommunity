<?php
namespace vc\controller\web\mysite;

class FeedController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $isInline = $request->getBoolean('inline');

        $this->setTitle(gettext('feed.title'));

        $forumService = new \vc\model\service\forum\ProfileForumService(
            $this->modelFactory,
            $this->path,
            $this->getSession()->getUserId()
        );

        $forumService->loadThreads(
            $this,
            $this->getView(),
            $this->getSession()->getUserId(),
            count($this->siteParams) > 0
                ? intval($this->siteParams[0])
                : 0
        );

        $this->getView()->set('isInline', $isInline);
        $this->getView()->set('forumUserId', $this->getSession()->getUserId());
        echo $this->getView()->render('mysite/feed', !$isInline);
    }
}
