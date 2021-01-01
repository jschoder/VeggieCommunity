<?php
namespace vc\model\service\forum;

class ProfileForumService extends AbstractForumService
{
    private $modelFactory;

    private $path;

    private $currentUserId;

    private $threadSubscriptions;

    private $flags = array();

    public function __construct($modelFactory, $path, $currentUserId)
    {
        $this->modelFactory = $modelFactory;
        $this->path = $path;
        $this->currentUserId = $currentUserId;

        if (empty($currentUserId)) {
            $this->threadSubscriptions = array();
        } else {
            $subscriptionModel = $this->modelFactory->getDbModel('Subscription');
            $this->threadSubscriptions = $subscriptionModel->getSubscriptions(
                $currentUserId,
                \vc\config\EntityTypes::FORUM_THREAD
            );
        }
    }

    public function getContextType()
    {
        return \vc\config\EntityTypes::PROFILE;
    }

    public function getContextId()
    {
        return $this->currentUserId;
    }

    public function canRead()
    {
        return !empty($this->currentUserId);
    }

    public function doDisplayAuthors()
    {
        return !empty($this->currentUserId);
    }

    public function isLikable()
    {
        return !empty($this->currentUserId);
    }

    public function canPostThread()
    {
        return !empty($this->currentUserId);
    }

    public function canPostComment()
    {
        return !empty($this->currentUserId);
    }

    public function getThreadActions($threadObject)
    {
        $actions = array();
        if (!empty($this->currentUserId)) {
            if (in_array($threadObject->id, $this->threadSubscriptions)) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('unsubscribe')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.unsubscribe'));
                $actions[] = $action;
            } else {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('subscribe')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.subscribe'));
                $actions[] = $action;
            }
            if ($threadObject->createdBy == $this->currentUserId &&
                $threadObject->threadType == \vc\object\ForumThread::TYPE_NORMAL) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('edit')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.edit'));
                $actions[] = $action;
            }
            if ($threadObject->createdBy == $this->currentUserId) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('delete')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.delete'));
                $actions[] = $action;
            } else {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('hide')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.hide'));
                $actions[] = $action;
            }

            if ($threadObject->createdBy !== $this->currentUserId &&
                !in_array($threadObject->threadType, \vc\object\ForumThread::$unflaggableThreadTypes)) {

                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('flag')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.flag'));
                $actions[] = $action;
            }
        }
        return $actions;
    }

    public function getCommentActions($threadCommentObject)
    {
        $actions = array();
        if (!empty($this->currentUserId)) {
            if ($this->currentUserId == $threadCommentObject->createdBy) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('edit')
                       ->setData('entity-id', $threadCommentObject->hashId)
                       ->setCaption(gettext('forum.comment.edit'));
                $actions[] = $action;
            }

            if ($this->currentUserId == $threadCommentObject->createdBy) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('delete')
                       ->setData('entity-id', $threadCommentObject->hashId)
                       ->setCaption(gettext('forum.comment.delete'));
                $actions[] = $action;
            }

            if ($this->currentUserId != $threadCommentObject->createdBy) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('flag')
                       ->setData('entity-id', $threadCommentObject->hashId)
                       ->setCaption(gettext('forum.comment.flag'));
                $actions[] = $action;
            }
        }
        return $actions;
    }

    public function getFlags()
    {
        return array();
    }

    protected function getThreadObjects($forumThreadModel, $page)
    {
        $threadQueryJoins['INNER JOIN (
                SELECT id FROM vc_forum_thread
                    INNER JOIN vc_feed_thread ON vc_forum_thread.id = vc_feed_thread.thread_id AND vc_feed_thread.user_id = ?
                    WHERE deleted_at IS NULL
                UNION
                    SELECT id FROM vc_forum_thread
                    WHERE context_type = ' . \vc\config\EntityTypes::PROFILE . ' AND created_by = ? AND deleted_at IS NULL
            ) as temp ON temp.id = vc_forum_thread.id'] = array($this->getContextId(), $this->getContextId());
        return $forumThreadModel->loadObjects(
            array(),
            $threadQueryJoins,
            'vc_forum_thread.content_updated_at DESC',
            ($page * \vc\config\Globals::THREADS_PER_PAGE) . ',' . \vc\config\Globals::THREADS_PER_PAGE
        );
    }

    protected function getThreadCount($forumThreadModel)
    {
        $feedThreadModel = $this->modelFactory->getDbModel('FeedThread');
        return $feedThreadModel->getFeedThreadCount($this->getContextId());
    }

    protected function alterProfileIds($threads, &$profileIds)
    {
        if (!empty($threads)) {
            foreach ($threads as $thread) {
                if ($thread->has(\vc\object\ForumThread::ADDITIONAL_ACTIVITY_PROFILE_ID)) {
                    $profileIds[] = $thread->additional[\vc\object\ForumThread::ADDITIONAL_ACTIVITY_PROFILE_ID];
                }
            }
        }
    }

    public function getPaginationRootPath()
    {
        return $this->path . 'mysite/feed/';
    }
}
