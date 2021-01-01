<?php
namespace vc\model\service\forum;

abstract class AbstractForumService
{
    private $threads;

    abstract public function getContextType();

    abstract public function getContextId();

    abstract public function canRead();

    abstract public function doDisplayAuthors();

    abstract public function isLikable();

    abstract public function canPostThread();

    abstract public function canPostComment();

    abstract public function getThreadActions($threadObject);

    abstract public function getCommentActions($threadCommentObject);

    abstract public function getFlags();

    public function loadThreads($controller, $view, $currentUserId, $page)
    {
        $forumThreadModel = $controller->getDbModel('ForumThread');
        $forumThreadCommentModel = $controller->getDbModel('ForumThreadComment');

        $threads = $this->getThreadObjects($forumThreadModel, $page);
        $threadCount = $this->getThreadCount($forumThreadModel);

        $profileIds = array();
        $threadIds = array();
        $threadCommentIds = array();
        $threadActions = array();
        $commentActions = array();

        foreach ($threads as $i => $thread) {
            $threadIds[] = $thread->id;
            $profileIds[] = $thread->createdBy;
            $comments = $forumThreadCommentModel->loadObjects(
                array(
                    'thread_id' => $thread->id,
                    'deleted_at IS NULL'
                ),
                array(),
                'created_at DESC',
                \vc\config\Globals::DEFAULT_THREAD_COMMENT_COUNT + 1
            );
            $threads[$i]->comments = array_reverse($comments);
            $threadActions[$thread->id] = $this->getThreadActions($thread);
            foreach ($comments as $i => $comment) {
                $threadCommentIds[] = $comment->id;
                $profileIds[] = $comment->createdBy;
                $commentActions[$comment->id] = $this->getCommentActions($comment);
            }
        }

        $this->alterProfileIds($threads, $profileIds);

        $profileIds = array_unique($profileIds);

        $profileModel = $controller->getDbModel('Profile');
        $profiles = $profileModel->getProfiles($controller->getLocale(), $profileIds);
        $profilesIndexed = array();
        foreach ($profiles as $profile) {
            $profilesIndexed[$profile->id] = $profile;
        }

        $pictureModel = $controller->getDbModel('Picture');
        $pictures = $pictureModel->readPictures($currentUserId, $profiles);

        $lastUpdateTimestamp = $forumThreadModel->getLastUpdateTimestamp(
            $this->getContextType(),
            $this->getContextId()
        );

        $likeModel = $controller->getDbModel('Like');
        $likes = $likeModel->getForumLikes($threadIds, $threadCommentIds);
        $flags = $this->getFlags();

        $pagination = new \vc\object\param\NumericPaginationObject(
            $this->getPaginationRootPath() . '%INDEX%/',
            $page,
            ceil($threadCount / \vc\config\Globals::THREADS_PER_PAGE)
        );
        $pagination->setDefaultUrl($this->getPaginationRootPath());
        $pagination->setDefaultUrlParams(array('%INDEX%' => 0, '%LIMIT%' => 1));
        $view->set('pagination', $pagination);

        $this->threads = $threads;

        $view->set('page', $page);
        $view->set('threads', $threads);
        $view->set('profiles', $profilesIndexed);
        $view->set('pictures', $pictures);
        $view->set('lastUpdateTimestamp', $lastUpdateTimestamp);
        $view->set('likes', $likes);
        $view->set('flags', $flags);
        $view->set('threadActions', $threadActions);
        $view->set('commentActions', $commentActions);
        $view->set('displayAuthors', $this->doDisplayAuthors());
        $view->set('isLikable', $this->isLikable());
        $view->set('canPostThread', $this->canPostThread());
        $view->set('canPostComment', $this->canPostComment());
    }

    protected function getThreadQueryWhere()
    {
        return array(
            'context_type' => $this->getContextType(),
            'context_id' => $this->getContextId(),
            'deleted_at IS NULL'
        );
    }

    protected function getThreadQueryJoins()
    {
        return  array();
    }

    protected function getThreadObjects($forumThreadModel, $page)
    {
        $threadQueryWhere = $this->getThreadQueryWhere();
        $threadQueryJoins = $this->getThreadQueryJoins();
        return $forumThreadModel->loadObjects(
            $threadQueryWhere,
            $threadQueryJoins,
            'vc_forum_thread.content_updated_at DESC',
            ($page * \vc\config\Globals::THREADS_PER_PAGE) . ',' . \vc\config\Globals::THREADS_PER_PAGE
        );
    }

    protected function getThreadCount($forumThreadModel)
    {
        $threadQueryWhere = $this->getThreadQueryWhere();
        $threadQueryJoins = $this->getThreadQueryJoins();
        return $forumThreadModel->getCount(
            $threadQueryWhere,
            $threadQueryJoins
        );
    }

    protected function alterProfileIds($threads, &$profileIds)
    {
        // Allows specific implementations to add custom profileIds depending on the content (e.g. new friends)
    }

    abstract public function getPaginationRootPath();

    public function getThreads()
    {
        return $this->threads;
    }
}
