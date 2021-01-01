<?php
namespace vc\model\service;

class EventService
{
    const ADDED = 1;

    const PRE_EDIT = 4;
    const EDITED = 5;

    const PRE_DELETE = 9;
    const DELETED = 10;

    private $modelFactory;

    private $listeners;

    public function __construct($modelFactory)
    {
        $this->modelFactory = $modelFactory;
        $this->listeners = [
            \vc\config\EntityTypes::PROFILE => [
                self::EDITED => 'ProfileEditedListener',
                self::DELETED => 'UserDeletedListener'
            ],
            \vc\config\EntityTypes::PROFILE_PICTURE => [
                self::ADDED => 'ProfilePictureAddedListener',
                self::PRE_DELETE => 'ProfilePicturePreDeleteListener'
            ],
            \vc\config\EntityTypes::FORUM_THREAD => [
                self::ADDED => 'ThreadAddedListener',
                self::PRE_DELETE => 'ThreadPreDeleteListener'
            ],
            \vc\config\EntityTypes::FRIEND => [
                self::ADDED => 'FriendAddedListener',
                self::PRE_DELETE => 'FriendPreDeleteListener',
                self::DELETED => 'FriendDeletedListener'
            ]
        ];
    }

    public function added($entityType, $entityId, $createdBy)
    {
        $this->triggerListener(self::ADDED, $entityType, $entityId, $createdBy);
    }

    public function edited($entityType, $entityId, $createdBy)
    {
        $this->triggerListener(self::EDITED, $entityType, $entityId, $createdBy);
    }

    public function preDelete($entityType, $entityId, $createdBy)
    {
        $this->triggerListener(self::PRE_DELETE, $entityType, $entityId, $createdBy);
    }

    public function deleted($entityType, $entityId, $createdBy)
    {
        $this->triggerListener(self::DELETED, $entityType, $entityId, $createdBy);
    }

    private function triggerListener($action, $entityType, $entityId, $createdBy)
    {
        if (!empty($this->listeners[$entityType][$action])) {
            $className = '\\vc\\model\\service\\listener\\' . $this->listeners[$entityType][$action];
            if (!class_exists($className)) {
                throw new \vc\exception\FatalSystemException('Listener ' . $className. ' can\'t be created.');
            }
            $listener = new $className($this, $this->modelFactory);
            $listener->trigger($entityId, $createdBy);
        }
    }
}
