<?php
namespace vc\controller\web\mod;

class FlagController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $flagModel = $this->getDbModel('Flag');
        $flagObjects = $flagModel->loadObjects(array(
            'aggregate_type !=' => \vc\config\EntityTypes::GROUP_FORUM,
            'processed_at IS NULL'
        ));

        // Loading all threads where either the thread itself or one of its comments is flagged
        $forumThreadModel = $this->getDbModel('ForumThread');
        $forumThreadCommentModel = $this->getDbModel('ForumThreadComment');
        $flags = array();
        $flagThreadIds = array();
        $flagCommentIds = array();
        foreach ($flagObjects as $flag) {
            if (!array_key_exists($flag->entityType, $flags)) {
                $flags[$flag->entityType] = array();
            }
            $flags[$flag->entityType][$flag->entityId] = $flag->hashId;

            if ($flag->entityType == \vc\config\EntityTypes::FORUM_THREAD) {
                $flagThreadIds[] = $flag->entityId;
            } else if ($flag->entityType == \vc\config\EntityTypes::FORUM_COMMENT) {
                $flagCommentIds[] = $flag->entityId;
            } else {
                \vc\lib\ErrorHandler::error(
                    'Can\'t edit flags for entity type ' . $flag->entityType,
                    __FILE__,
                    __LINE__
                );
            }
        }
        $threadIds = array_unique(array_merge(
            $flagThreadIds,
            $forumThreadCommentModel->getFieldList('thread_id', array('id' => $flagCommentIds))
        ));

        if (empty($threadIds)) {
            $threads = array();
        } else {
            $threads = $forumThreadModel->loadObjects(array(
                'id' => $threadIds
            ));
        }

        $profileIds = array();
        $threadActions = array();
        $threadCommentIds = array();
        $commentActions = array();
        foreach ($threads as $i => $thread) {
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
            $threadActions[$thread->id] = array();
            foreach ($comments as $i => $comment) {
                $threadCommentIds[] = $comment->id;
                $profileIds[] = $comment->createdBy;
                $commentActions[$comment->id] = array();
            }
        }

        $profileIds = array_unique($profileIds);

        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getProfiles($this->getLocale(), $profileIds);
        $profilesIndexed = array();
        foreach ($profiles as $profile) {
            $profilesIndexed[$profile->id] = $profile;
        }

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures($this->getSession()->getUserId(), $profiles);

        $this->setTitle('Flags');

        $this->getView()->set('threads', $threads);
        $this->getView()->set('profiles', $profilesIndexed);
        $this->getView()->set('pictures', $pictures);
        $this->getView()->set('threadActions', $threadActions);
        $this->getView()->set('commentActions', $commentActions);
        $this->getView()->set('flags', $flags);
        echo $this->getView()->render('mod/flag', true);
    }

}
