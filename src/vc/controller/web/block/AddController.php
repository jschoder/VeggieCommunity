<?php
namespace vc\controller\web\block;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('blockprofile.noactivesession'));
            return;
        }
        if (empty($_POST['profileid'])) {
            echo \vc\view\json\View::renderStatus(false, gettext('blockprofile.failed'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $blockedId = intval($_POST['profileid']);
        $currentProfileId = $this->getSession()->getUserId();
        \vc\lib\Assert::assertLong('blockedid', $blockedId, 1, 2147483647, false);

        // Can't block yourself
        if (intval($blockedId) === $currentProfileId) {
            echo \vc\view\json\View::renderStatus(false, gettext('blockprofile.failed'));
            return;
        }

        // User has been unblocked in the last 48 hours
        $blockedModel = $this->getDbModel('Blocked');
        $reblockCount = $blockedModel->getCount(
            array(
                'profile_id' => $this->getSession()->getUserId(),
                'blocked_id' => $blockedId,
                'deleted_at >' => date('Y-m-d H:i:s', time() - 172800)
            )
        );
        if ($reblockCount > 0) {
            echo \vc\view\json\View::renderStatus(false, gettext('blockprofile.timelock'));
            return;
        }

        // Already blocked
        if (in_array(intval($blockedId), $this->getBlocked())) {
            echo \vc\view\json\View::renderStatus(true, gettext('blockprofile.success'));
            return;
        }

        $cacheModel = $this->getModel('Cache');
        $profileRelations = $cacheModel->getProfileRelations($this->getSession()->getUserId());
        if (in_array($blockedId, $profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED])) {
            $this->getEventService()->preDelete(
                \vc\config\EntityTypes::FRIEND,
                $blockedId,
                $this->getSession()->getUserId()
            );
        }

        $success = $blockedModel->addBlock($this->getSession()->getUserId(), $blockedId);
        if ($success === false) {
            echo \vc\view\json\View::renderStatus(false, gettext('blockprofile.failed'));
        } else {
            $cacheModel->resetProfileRelations($currentProfileId);
            $cacheModel->resetProfileRelations(intval($blockedId));

            if (in_array($blockedId, $profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED])) {
                $this->getEventService()->deleted(
                    \vc\config\EntityTypes::FRIEND,
                    $blockedId,
                    $this->getSession()->getUserId()
                );
            }

            echo \vc\view\json\View::renderStatus(true, gettext('blockprofile.success'));
        }
    }
}
