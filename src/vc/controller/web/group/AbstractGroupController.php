<?php
namespace vc\controller\web\group;

abstract class AbstractGroupController extends \vc\controller\web\AbstractWebController
{
    private $groupObject;

    private $groupRole;

    private $forums;

    protected function loadGroup($hashId)
    {
        $groupModel = $this->getDbModel('Group');
        $this->groupObject = $groupModel->loadObject(array('hash_id' => $hashId));
        if ($this->groupObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array('SiteParams' => $this->siteParams)
            );
            throw new \vc\exception\NotFoundException();
        }
        if ($this->groupObject->deletedAt !== null) {
            throw new \vc\exception\NotFoundException();
        }

        $this->getView()->setHeader(
            'description',
            str_replace(array("\n", "\r"), array(' ', ''), $this->groupObject->description)
        );
        if (empty($this->groupObject->image)) {
            $this->getView()->setHeader(
                'image',
                'https://www.veggiecommunity.org' . $this->imagesPath . 'default-group.png'
            );
        } else {
            $this->getView()->setHeader(
                'image',
                'https://www.veggiecommunity.org/groups/picture/crop/200/200/' . $this->groupObject->image
            );
        }
        $this->getView()->set('group', $this->groupObject);
        $groupMemberModel = $this->getDbModel('GroupMember');
        $this->getView()->set('memberCount', $groupMemberModel->getMemberCount($this->groupObject->id));
        $isMember = $groupMemberModel->isMember($this->groupObject->id, $this->getSession()->getUserId());
        $this->getView()->set('isConfirmedMember', ($isMember !== null && $isMember !== false));
        $this->getView()->set('isMemberWaitingForConfirmation', ($isMember === null));

        $groupRoleModel = $this->getDbModel('GroupRole');
        $groupRoles = $groupRoleModel->getGroupRoles($this->groupObject->id);
        if (in_array($this->getSession()->getUserId(), $groupRoles[\vc\object\GroupRole::ROLE_MODERATOR])) {
            $this->groupRole = \vc\object\GroupRole::ROLE_MODERATOR;
        } elseif (in_array($this->getSession()->getUserId(), $groupRoles[\vc\object\GroupRole::ROLE_ADMIN])) {
            $this->groupRole = \vc\object\GroupRole::ROLE_ADMIN;
        } else {
            $this->groupRole = null;
        }
        $this->getView()->set('groupRoles', $groupRoles);
        $this->getView()->set('groupRole', $this->groupRole);

        $groupForumModel = $this->getDbModel('GroupForum');
        $this->forums = $groupForumModel->loadObjects(
            array('group_id' => $this->groupObject->id, 'deleted_at IS NULL'),
            array(),
            'weight ASC'
        );
        if (empty($this->forums)) {
            \vc\lib\ErrorHandler::error(
                'No forums for group #' . $this->groupObject->id,
                __FILE__,
                __LINE__
            );
            throw new \vc\exception\NotFoundException();
        }
        $this->getView()->set('forums', $this->forums);
    }

    protected function getGroupObject()
    {
        return $this->groupObject;
    }

    protected function getGroupRole()
    {
        return $this->groupRole;
    }

    protected function getForums()
    {
        return $this->forums;
    }
}
