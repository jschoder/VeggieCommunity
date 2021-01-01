<?php
namespace vc\model\service\module\block\event;

class UpcomingBlockModule extends \vc\model\service\module\block\AbstractBlockModule
{
    protected function isDifferentForRegistered()
    {
        return true;
    }

    protected function render($path, $imagesPath)
    {
        $userId = $this->getUserId();
        if (empty($userId)) {
            $visibilityFilter = \vc\object\Event::EVENT_VISIBILITY_PUBLIC;
        } else {
            $visibilityFilter = array(
                \vc\object\Event::EVENT_VISIBILITY_PUBLIC,
                \vc\object\Event::EVENT_VISIBILITY_REGISTERED
            );
        }
        $eventModel = $this->getDbModel('Event');
        $events = $eventModel->loadObjects(
            array(
                'vc_event.end_date >' => date('Y-m-d H:i:s'),
                'event_visibility' => $visibilityFilter,
                'deleted_at IS NULL'
            ),
            array(),
            'vc_event.start_date ASC',
            12
        );

        return $this->element(
            'block/event/upcoming',
            array(
                'path' => $path,
                'imagesPath' => $imagesPath,
                'events' => $events,
                'eventCategories' => \vc\config\Fields::getEventCategories()
            )
        );
    }
}
