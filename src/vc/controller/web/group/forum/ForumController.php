<?php
namespace vc\controller\web\group\forum;

class ForumController extends \vc\controller\web\group\AbstractGroupController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (count($this->siteParams) === 0) {
            throw new \vc\exception\NotFoundException();
        }
        if (count($this->siteParams) > 2 && intval($this->siteParams[2]) === 0) {
            throw new \vc\exception\NotFoundException();
        }

        if ($this->getSession()->hasActiveSession()) {
            $termsModel = $this->getDbModel('Terms');
            if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
                throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
            }
        }

        $this->loadGroup($this->siteParams[0]);
        $groupObject = $this->getGroupObject();
        $this->setTitle($groupObject->name);


        $groupForumModel = $this->getDbModel('GroupForum');
        if (count($this->siteParams) > 1) {
            $currentForum = $groupForumModel->getCurrentForum($this->getForums(), $this->siteParams[1]);
        } else {
            $currentForum = $groupForumModel->getCurrentForum($this->getForums(), null);
        }

        // Don't index non-public forums
        if ($currentForum->contentVisibility !== \vc\object\GroupForum::CONTENT_VISIBILITY_PUBLIC) {
            $this->getView()->setHeader('robots', 'noindex, follow');
        }

        if (empty($currentForum)) {
            \vc\lib\ErrorHandler::error(
                'No current forum for group #' . $groupObject->id,
                __FILE__,
                __LINE__
            );
            throw new \vc\exception\NotFoundException();
        }
        $this->getView()->set('currentForum', $currentForum);

        $forumService = new \vc\model\service\forum\GroupForumService(
            $this->modelFactory,
            $this->path,
            $groupObject,
            $currentForum,
            $this->getSession()->getUserId(),
            $this->getSession()->isAdmin()
        );

        $forumService->loadThreads(
            $this,
            $this->getView(),
            $this->getSession()->getUserId(),
            $currentForum->isMain && count($this->siteParams) > 1
                ? intval($this->siteParams[1])
                : (count($this->siteParams) > 2
                    ? intval($this->siteParams[2])
                    : 0)
        );

        if (empty($forumService->getThreads())) {
            $this->getView()->setHeader('robots', 'noindex, follow');
        }

        if ($this->getSession()->hasActiveSession()) {
            $subscriptionModel = $this->getDbModel('Subscription');
            $forumSubscriptions = $subscriptionModel->getSubscriptions(
                $this->getSession()->getUserId(),
                \vc\config\EntityTypes::GROUP_FORUM
            );
        } else {
            $forumSubscriptions = array();
        }

        $this->getView()->set('forumSubscriptions', $forumSubscriptions);
        echo $this->getView()->render('group/forum/forum', true);
    }
}
