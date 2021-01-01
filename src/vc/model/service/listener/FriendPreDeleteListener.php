<?php
namespace vc\model\service\listener;

class FriendPreDeleteListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        $this->deleteFeedEntry($entityId, $createdBy);
    }

    private function deleteFeedEntry($entityId, $createdBy)
    {
        $forumThreadModel = $this->getDbModel('ForumThread');
        $thread = $forumThreadModel->getFriendThread($createdBy, intval($entityId));
        if (!empty($thread)) {
            $forumThreadModel->update(
                array(
                    'id' => $thread->id
                ),
                array(
                    'deleted_by' => $createdBy,
                    'deleted_at' => date('Y-m-d H:i:s')
                )
            );

            $updateModel = $this->getDbModel('Update');
            $updateModel->add(
                $thread->id,
                \vc\config\EntityTypes::FORUM_THREAD,
                \vc\object\Update::ACTION_REMOVE,
                $thread->contextType,
                $thread->contextId
            );
        }
    }
}
