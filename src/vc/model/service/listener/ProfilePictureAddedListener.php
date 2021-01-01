<?php
namespace vc\model\service\listener;

class ProfilePictureAddedListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        $this->addFeedEntry($entityId, $createdBy);
    }

    private function addFeedEntry($entityId, $createdBy)
    {
        $pictureModel = $this->getDbModel('Picture');
        $filename = $pictureModel->getField('filename', 'id', $entityId);

        $forumThreadModel = $this->getDbModel('ForumThread');
        $forumThread = new \vc\object\ForumThread();
        $forumThread->contextType = \vc\config\EntityTypes::PROFILE;
        $forumThread->contextId = $createdBy;
        $forumThread->threadType = \vc\object\ForumThread::TYPE_ACTVITY_PICTURE_ADDED;
        $forumThread->additional = [
            \vc\object\ForumThread::ADDITIONAL_PICTURE_PATH => 'user/picture',
            \vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME => $filename
        ];
        $forumThread->createdBy = $createdBy;
        $threadId = $forumThreadModel->insertObject(null, $forumThread);
        if ($threadId) {
            $this->getEventService()->added(
                \vc\config\EntityTypes::FORUM_THREAD,
                $threadId,
                $createdBy
            );

            $pictureModel->update(
                array('id' => $entityId),
                array('feed_thread_id' => $threadId)
            );
        }
    }
}
