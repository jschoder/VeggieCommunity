<?php
namespace vc\controller\web\group\invitation;

class IgnoreController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'groups/');
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['id'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            throw new \vc\exception\RedirectException($this->path . 'groups/');
        }

        $groupModel = $this->getDbModel('Group');
        $groupId = $groupModel->getIdByHashId($formValues['id']);
        if ($groupId === null) {
            throw new \vc\exception\RedirectException($this->path . 'groups/');
        }

        $groupInvitationModel = $this->getDbModel('GroupInvitation');
        $groupInvitationModel->update(
            array(
                'group_id' => $groupId,
                'profile_id' => $this->getSession()->getUserId(),
                'updated_at' => null
            ),
            array(
                'updated_at' => date('Y-m-d H:i:s')
            ),
            false
        );

        throw new \vc\exception\RedirectException($this->path . 'groups/info/' . $formValues['id'] . '/');
    }
}
