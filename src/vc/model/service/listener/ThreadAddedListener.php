<?php
namespace vc\model\service\listener;

class ThreadAddedListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        $this->addToUserFeeds($entityId, $createdBy);
        $this->addUpdates($entityId);
    }

    private function addToUserFeeds($entityId, $createdBy)
    {
        // The query makes sure that only the right entries are added to the feed
        $feedThreadModel = $this->getDbModel('FeedThread');
        $feedThreadModel->addThread($entityId, $createdBy);
    }

    private function addUpdates($entityId)
    {
        $threadModel = $this->getDbModel('ForumThread');
        $threadObject = $threadModel->loadObject(array('id' => $entityId));
        if ($threadObject) {
            $updateModel = $this->getDbModel('Update');
            $updateModel->add(
                $threadObject->id,
                \vc\config\EntityTypes::FORUM_THREAD,
                \vc\object\Update::ACTION_ADD,
                $threadObject->contextType,
                $threadObject->contextId
            );
        } else {
            \vc\lib\ErrorHandler::error(
                'Can\'t add add-updates for thread',
                __FILE__,
                __LINE__,
                array(
                    'entityId' => $entityId
                )
            );
        }
    }
}
