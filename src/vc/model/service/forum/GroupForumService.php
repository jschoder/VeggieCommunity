<?php
namespace vc\model\service\forum;

class GroupForumService extends AbstractForumService
{
    private $modelFactory;

    private $path;

    private $groupObject;

    private $forumObject;

    private $profileId;

    private $isMember;

    private $groupRole;

    private $flags;

    private $isAdmin;

    public function __construct($modelFactory, $path, $groupObject, $forumObject, $profileId, $isAdmin)
    {
        $this->modelFactory = $modelFactory;
        $this->path = $path;
        $this->groupObject = $groupObject;
        $this->forumObject = $forumObject;
        $this->profileId = $profileId;

        $groupMemberModel = $this->modelFactory->getDbModel('GroupMember');
        $this->isMember = $groupMemberModel->isMember($this->forumObject->groupId, $this->profileId);

        if (empty($profileId)) {
            $this->groupRole = null;
        } else {
            $groupRoleModel = $this->modelFactory->getDbModel('GroupRole');
            $this->groupRole = $groupRoleModel->getRole($this->forumObject->groupId, $profileId);
        }

        if ($this->groupRole !== null) {
            $flagModel = $this->modelFactory->getDbModel('Flag');
            $this->flags = $flagModel->getFlaggedIds(
                \vc\config\EntityTypes::GROUP_FORUM,
                $this->forumObject->id,
                array(
                    \vc\config\EntityTypes::FORUM_THREAD,
                    \vc\config\EntityTypes::FORUM_COMMENT
                )
            );
        } else {
            $this->flags = array();
        }
        $this->isAdmin = $isAdmin;
    }

    public function getContextType()
    {
        return \vc\config\EntityTypes::GROUP_FORUM;
    }

    public function getContextId()
    {
        return $this->forumObject->id;
    }

    public function canRead()
    {
        if ($this->forumObject->contentVisibility == \vc\object\GroupForum::CONTENT_VISIBILITY_MEMBER) {
            // No active session
            if (empty($this->profileId)) {
                return false;
            } elseif (!$this->isMember) {
                 return false;
            }
        } elseif ($this->forumObject->contentVisibility == \vc\object\GroupForum::CONTENT_VISIBILITY_REGISTERED) {
            // No active session
            if (empty($this->profileId)) {
                return false;
            }
        }
        return true;
    }

    public function doDisplayAuthors()
    {
        if (empty($this->profileId)) {
            return false;
        } else {
            if ($this->isMember !== null && $this->isMember !== false) {
                // Active Member
                return true;
            } else {
                if ($this->groupObject->memberVisibility == \vc\object\Group::MEMBER_VISBILITY_SITE_MEMBERS) {
                    // Visible for all registered site members
                    return true;
                } else { // $group->memberVisibility == \vc\object\Group::MEMBER_VISBILITY_MEMBER_ONLY
                    return false;
                }
            }
        }
    }

    public function isLikable()
    {
        return !empty($this->profileId) && $this->isMember !== null && $this->isMember !== false;
    }

    public function canPostThread()
    {
        return !empty($this->profileId) && $this->isMember !== null && $this->isMember !== false;
    }

    public function canPostComment()
    {
        return !empty($this->profileId) && $this->isMember !== null && $this->isMember !== false;
    }

    public function getThreadActions($threadObject)
    {
        $actions = array();
        if (!empty($this->profileId) && $this->isMember) {
            $subscriptionModel = $this->modelFactory->getDbModel('Subscription');
            $this->threadSubscriptions = $subscriptionModel->getSubscriptions(
                $this->profileId,
                \vc\config\EntityTypes::FORUM_THREAD
            );
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
        if ($this->profileId == $threadObject->createdBy ||
            $this->groupRole !== null) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('delete')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.delete'));
                $actions[] = $action;
        }
        if (array_key_exists(\vc\config\EntityTypes::FORUM_THREAD, $this->flags) &&
            array_key_exists($threadObject->id, $this->flags[\vc\config\EntityTypes::FORUM_THREAD])) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('unflag')
                       ->setData('flag-id', $this->flags[\vc\config\EntityTypes::FORUM_THREAD][$threadObject->id])
                       ->setCaption(gettext('forum.thread.unflag'));
                $actions[] = $action;
        } elseif ($this->profileId != $threadObject->createdBy &&
                  !in_array($threadObject->threadType, \vc\object\ForumThread::$unflaggableThreadTypes)) {
                $action = new \vc\object\Action();
                $action->setHref('#')
                       ->setClass('flag')
                       ->setData('entity-id', $threadObject->hashId)
                       ->setCaption(gettext('forum.thread.flag'));
                $actions[] = $action;
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
        return $this->flags;
    }

    public function getPaginationRootPath()
    {
        $path =  $this->path . 'groups/forum/' . $this->groupObject->hashId . '/';
        if (!$this->forumObject->isMain) {
            $path .= $this->forumObject->hashId . '/';
        }
        return $path;
    }
}
