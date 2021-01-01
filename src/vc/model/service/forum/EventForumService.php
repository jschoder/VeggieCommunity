<?php
namespace vc\model\service\forum;

class EventForumService extends AbstractForumService
{
    private $modelFactory;

    private $path;

    private $event;

    private $profileId;

    private $eventParticipant;

    private $threadSubscriptions;

    private $canSeeEvent;

    private $flags = array();

    private $isAdmin;

    public function __construct($modelFactory, $path, $event, $profileId, $eventParticipant, $isAdmin)
    {
        $this->modelFactory = $modelFactory;
        $this->path = $path;
        $this->event = $event;
        $this->profileId = $profileId;
        $this->eventParticipant = $eventParticipant;

        if (empty($profileId)) {
            $this->threadSubscriptions = array();
        } else {
            $subscriptionModel = $this->modelFactory->getDbModel('Subscription');
            $this->threadSubscriptions = $subscriptionModel->getSubscriptions(
                $profileId,
                \vc\config\EntityTypes::FORUM_THREAD
            );
        }
        $eventModel = $this->modelFactory->getDbModel('Event');
        $this->canSeeEvent = $eventModel->canSeeEvent(
            $profileId,
            $this->event,
            $this->eventParticipant
        );
        $this->isAdmin = $isAdmin;
    }

    public function getContextType()
    {
        return \vc\config\EntityTypes::EVENT;
    }

    public function getContextId()
    {
        return $this->event->id;
    }

    public function canRead()
    {
        return $this->canSeeEvent;
    }

    public function doDisplayAuthors()
    {
        switch ($this->event->guestVisibility) {
            case \vc\object\Event::GUEST_VISIBILITY_REGISTERED:
                return !empty($this->profileId);
            case \vc\object\Event::GUEST_VISIBILITY_GROUP:
                $groupMemberModel = $this->modelFactory->getDbModel('GroupMember');
                $isMember = $groupMemberModel->isMember($this->event->groupId, $this->profileId);
                return $isMember !== null && $isMember !== false;
            case \vc\object\Event::GUEST_VISIBILITY_FRIENDS:
                return false;
            case \vc\object\Event::GUEST_VISIBILITY_INVITEE:
                return !empty($this->eventParticipant);
            default:
                return false;
        }
    }

    public function isLikable()
    {
        return !empty($this->profileId);
    }

    public function canPostThread()
    {
        return !empty($this->profileId);
    }

    public function canPostComment()
    {
        return !empty($this->profileId);
    }

    public function getThreadActions($threadObject)
    {
        $actions = array();
        if (!empty($this->profileId)) {
            if ($this->canSeeEvent) {
                if (in_array($threadObject->id, $this->threadSubscriptions)) {
                    $action = new \vc\object\Action();
                    $action->setHref('#')
                           ->setClass('unsubscribe')
                           ->setData('entity-id', $threadObject->hashId)
                           ->setCaption(gettext('forum.thread.unsubscribe'));
                    $actions[] = $action;
                } else {
                    $action = new \vc\object\Action();
                    $action->setHref('#')
                           ->setClass('subscribe')
                           ->setData('entity-id', $threadObject->hashId)
                           ->setCaption(gettext('forum.thread.subscribe'));
                    $actions[] = $action;
                }
            }
            if ($this->profileId == $threadObject->createdBy) {
                    $action = new \vc\object\Action();
                    $action->setHref('#')
                           ->setClass('edit')
                           ->setData('entity-id', $threadObject->hashId)
                           ->setCaption(gettext('forum.thread.edit'));
                    $actions[] = $action;
            }
            if ($this->profileId == $threadObject->createdBy) {
                    $action = new \vc\object\Action();
                    $action->setHref('#')
                           ->setClass('delete')
                           ->setData('entity-id', $threadObject->hashId)
                           ->setCaption(gettext('forum.thread.delete'));
                    $actions[] = $action;
            }
            if ($this->profileId != $threadObject->createdBy &&
                !in_array($threadObject->threadType, \vc\object\ForumThread::$unflaggableThreadTypes)) {
                    $action = new \vc\object\Action();
                    $action->setHref('#')
                           ->setClass('flag')
                           ->setData('entity-id', $threadObject->hashId)
                           ->setCaption(gettext('forum.thread.flag'));
                    $actions[] = $action;
            }
        }
        return $actions;
    }

    public function getCommentActions($threadCommentObject)
    {
        $actions = array();
        if (!empty($this->profileId)) {
            if ($this->profileId == $threadCommentObject->createdBy) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('edit')
                       ->setData('entity-id', $threadCommentObject->hashId)
                       ->setCaption(gettext('forum.comment.edit'));
                $actions[] = $action;
            }

            if ($this->profileId == $threadCommentObject->createdBy ||
                $this->groupRole !== null) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('delete')
                       ->setData('entity-id', $threadCommentObject->hashId)
                       ->setCaption(gettext('forum.comment.delete'));
                $actions[] = $action;
            }

            if (array_key_exists(\vc\config\EntityTypes::FORUM_COMMENT, $this->flags) &&
                array_key_exists($threadCommentObject->id, $this->flags[\vc\config\EntityTypes::FORUM_COMMENT])) {
                if ($this->isAdmin) {
                    $action = new \vc\object\Action();
                    $action->setHref('#')
                           ->setClass('unflag')
                           ->setData(
                               'flag-id',
                               $this->flags[\vc\config\EntityTypes::FORUM_COMMENT][$threadCommentObject->id]
                           )
                           ->setCaption(gettext('forum.comment.unflag'));
                    $actions[] = $action;
                }
            } elseif ($this->profileId != $threadCommentObject->createdBy) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('flag')
                       ->setData('entity-id', $threadCommentObject->hashId)
                       ->setCaption(gettext('forum.comment.flag'));
                $actions[] = $action;
            }
        }
        return $actions;
    }

    public function getFlags()
    {
        return array();
    }

    public function getPaginationRootPath()
    {
        return $this->path . 'events/view/' . $this->event->hashId . '/discussion/';
    }
}
