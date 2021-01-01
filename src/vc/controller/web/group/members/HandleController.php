<?php
namespace vc\controller\web\group\members;

class HandleController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = $_POST;
        if (!array_key_exists('action', $formValues) ||
            !array_key_exists('groupId', $formValues) ||
            !array_key_exists('profileId', $formValues)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.unconfirmed.failed'));
            return;
        }

        $groupModel = $this->getDbModel('Group');
        $groupObject = $groupModel->loadObject(array('hash_id' => $formValues['groupId']));
        if ($groupObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GROUP,
                array(
                    'groupHashId' => $formValues['groupId']
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.unconfirmed.failed'));
            return;
        }

        $groupRoleModel = $this->getDbModel('GroupRole');
        $groupRole = $groupRoleModel->getRole($groupObject->id, $this->getSession()->getUserId());
        // Neither mod nor admin
        if ($groupRole === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_GROUP_AS_NONADMIN,
                array(
                    'groupId' => $groupObject->id,
                    'userId' => $this->getSession()->getUserId(),
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.unconfirmed.failed'));
            return;
        }

        $groupMemberModel = $this->getDbModel('GroupMember');
        if ($formValues['action'] === 'accept') {
            $success = $groupMemberModel->accept(
                $groupObject->id,
                intval($formValues['profileId']),
                $this->getSession()->getUserId()
            );
            if ($success) {
                $subscriptionModel = $this->getDbModel('Subscription');
                $subscriptionModel->subscribeToAllGroupForums($groupObject->id, intval($formValues['profileId']));

                $groupNotificationModel = $this->getDbModel('GroupNotification');
                $groupNotificationModel->add(
                    intval($formValues['profileId']),
                    \vc\object\GroupNotification::TYPE_GROUP_MEMBER_CONFIRMED,
                    $groupObject->id,
                    $this->getSession()->getUserId()
                );

                // :TODO:  JOE !!! - auto subscribe to groups forums
            }

            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations(intval($formValues['profileId']));
        } elseif ($formValues['action'] === 'deny') {
            $success = $groupMemberModel->deny($groupObject->id, intval($formValues['profileId']));
        } elseif ($formValues['action'] === 'block') {
            $success = $groupMemberModel->deny($groupObject->id, intval($formValues['profileId']));
            if ($success) {
                $groupBanModel = $this->getDbModel('GroupBan');
                $groupBanObject = new \vc\object\GroupBan();
                $groupBanObject->groupId = $groupObject->id;
                $groupBanObject->profileId = intval($formValues['profileId']);
                $groupBanObject->bannedBy = $this->getSession()->getUserId();
                $groupBanObject->reason = '';
                $success = $groupBanModel->insertObject($this->getSession()->getProfile(), $groupBanObject);
            }
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.unconfirmed.failed'));
            return;
        }

        if ($success) {
            echo \vc\view\json\View::render(array('success' => true,
                                                  'user' => $formValues['profileId']));
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('group.members.unconfirmed.failed'));
        }
    }
}
