<?php
namespace vc\controller\web\undo;

class BlockController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('error.noactivesession'));
            return;
        }

        if (empty($this->siteParams)) {
            echo \vc\view\json\View::renderStatus(false, gettext('undo.block.failed'));
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'site' => $this->site,
                    'siteParams' => $this->siteParams
                )
            );
            return;
        }

        $blockedModel = $this->getDbModel('Blocked');
        $deleted = $blockedModel->delete(
            array(
                'profile_id' => $this->getSession()->getUserId(),
                'blocked_id' => $this->siteParams[0],
                'created_at >' => date('Y-m-d H:i:s', time() - 3600)
            )
        );

        if ($deleted) {
            echo \vc\view\json\View::renderStatus(true, gettext('undo.block.success'));
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('undo.block.failed'));
        }
    }
}
