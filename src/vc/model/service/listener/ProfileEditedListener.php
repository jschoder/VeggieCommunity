<?php
namespace vc\model\service\listener;

class ProfileEditedListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        $this->addFeedEntry($entityId, $createdBy);
        $this->updateSearchIndex($entityId, $createdBy);
    }

    private function addFeedEntry($entityId, $createdBy)
    {
        $forumThreadModel = $this->getDbModel('ForumThread');
        $forumThread = new \vc\object\ForumThread();
        $forumThread->contextType = \vc\config\EntityTypes::PROFILE;
        $forumThread->contextId = $createdBy;
        $forumThread->threadType = \vc\object\ForumThread::TYPE_ACTVITY_PROFILE_UPDATE;
        $forumThread->additional = [];
        $forumThread->createdBy = $createdBy;
        $threadId = $forumThreadModel->insertObject(null, $forumThread);
        if ($threadId) {
            $this->getEventService()->added(
                \vc\config\EntityTypes::FORUM_THREAD,
                $threadId,
                $createdBy
            );
        }
    }

    private function updateSearchIndex($entityId, $createdBy)
    {
        $searchstringModel = $this->getDbModel('Searchstring');
        $searchstringModel->updateIndex($entityId);
    }
}
