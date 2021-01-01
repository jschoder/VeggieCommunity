<?php
namespace vc\model\service\listener;

class UserDeletedListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        // Created by MIGHT(!) be an admin
        $this->removeFeedEntries($entityId, $createdBy);
    }

    private function removeFeedEntries($entityId)
    {
        $feedThreadModel = $this->getDbModel('FeedThread');
        $feedThreadModel->removeAllThreads($entityId);
    }
}
