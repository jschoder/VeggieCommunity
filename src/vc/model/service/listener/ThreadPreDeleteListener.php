<?php
namespace vc\model\service\listener;

class ThreadPreDeleteListener extends AbstractListener
{
    public function trigger($entityId, $createdBy)
    {
        $this->addUpdates($entityId);
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
                \vc\object\Update::ACTION_REMOVE,
                $threadObject->contextType,
                $threadObject->contextId
            );
        } else {
            \vc\lib\ErrorHandler::error(
                'Can\'t add delete-updates for thread',
                __FILE__,
                __LINE__,
                array(
                    'entityId' => $entityId
                )
            );
        }
    }
}
