<?php
namespace vc\model\service\listener;

class FriendAddedListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        $this->resetCache($entityId, $createdBy);
        $this->addFeedEntry($entityId, $createdBy);
        $this->addAllFeedEntries($entityId, $createdBy);
    }

    private function resetCache($entityId, $createdBy)
    {
        $cacheModel = $this->getModel('Cache');
        $cacheModel->resetProfileRelations($entityId);
        $cacheModel->resetProfileRelations($createdBy);
    }

    private function addFeedEntry($entityId, $createdBy)
    {
        $forumThreadModel = $this->getDbModel('ForumThread');
        $forumThread = new \vc\object\ForumThread();
        $forumThread->contextType = \vc\config\EntityTypes::PROFILE;
        $forumThread->contextId = $createdBy;
        $forumThread->threadType = \vc\object\ForumThread::TYPE_ACTVITY_FRIEND_ADDED;
        $forumThread->additional = [
            \vc\object\ForumThread::ADDITIONAL_ACTIVITY_PROFILE_ID => $entityId
        ];
        $forumThread->createdBy = $createdBy;
        $threadId = $forumThreadModel->insertObject(null, $forumThread);
        if ($threadId) {
            $this->getEventService()->added(
                \vc\config\EntityTypes::FORUM_THREAD,
                $threadId,
                $createdBy
            );
            $this->getEventService()->added(
                \vc\config\EntityTypes::FORUM_THREAD,
                $threadId,
                $entityId
            );

            $friendModel = $this->getDbModel('Friend');
            $friendModel->update(
                array('friend1id' => $createdBy, 'friend2id' => $entityId),
                array('feed_thread_id' => $threadId)
            );
        }
    }

    private function addAllFeedEntries($entityId, $createdBy)
    {
        $feedThreadModel = $this->getDbModel('FeedThread');
        // Add in both directions
        $feedThreadModel->addAllThreads($entityId, $createdBy);
        $feedThreadModel->addAllThreads($createdBy, $entityId);
    }
}
