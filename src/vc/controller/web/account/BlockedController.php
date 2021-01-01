<?php
namespace vc\controller\web\account;

class BlockedController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('blocked.title'));
        $this->getView()->set('activeMenuitem', 'login');
        $this->getView()->setHeader('robots', 'noindex, follow');

        $blockedModel = $this->getDbModel('Blocked');
        $blockedUsers = $blockedModel->getFieldMap(
            'blocked_id',
            'created_at',
            array(
                'profile_id' => $this->getSession()->getUserId(),
                'deleted_at IS NULL'
            ),
            array(),
            'created_at DESC'
        );

        if (empty($blockedUsers)) {
            $blockedProfiles = array();
        } else {
            $profileModel = $this->getDbModel('Profile');
            $blockedProfiles = $profileModel->getFieldMap(
                'id',
                'nickname',
                array(
                    'id' => array_keys($blockedUsers)
                )
            );
        }

        $this->getView()->set('blockedUsers', $blockedUsers);
        $this->getView()->set('blockedProfiles', $blockedProfiles);

        echo $this->getView()->render('account/blocked', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        if (!empty($_POST['unblock'])) {
            $unblockIds = array_map('intval', $_POST['unblock']);
            $blockedModel = $this->getDbModel('Blocked');
            $blockedModel->update(
                array(
                    'profile_id' => $this->getSession()->getUserId(),
                    'blocked_id' => $unblockIds
                ),
                array(
                    'deleted_at' => date('Y-m-d H:i:s')
                ),
                false
            );

            // Clear the relations cache for the current user and all unblocked ones.
            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetProfileRelations($this->getSession()->getUserId());
            foreach ($unblockIds as $unblockId) {
                $cacheModel->resetProfileRelations($unblockId);
            }
        }

        throw new \vc\exception\RedirectException($this->path . 'account/blocked/');
    }
}
