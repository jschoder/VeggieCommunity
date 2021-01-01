<?php
namespace vc\model\service\listener;

class FriendDeletedListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        $this->resetCache($entityId, $createdBy);
        $this->removeFeedEntries($entityId, $createdBy);
    }

    private function resetCache($entityId, $createdBy)
    {
        $cacheModel = $this->getModel('Cache');
        $cacheModel->resetProfileRelations($entityId);
        $cacheModel->resetProfileRelations($createdBy);
    }

    private function removeFeedEntries($entityId, $createdBy)
    {
        $feedThreadModel = $this->getDbModel('FeedThread');

        // Delete in both directions
        $feedThreadModel->removeAllThreads($entityId, $createdBy);
        $feedThreadModel->removeAllThreads($createdBy, $entityId);
    }
}
