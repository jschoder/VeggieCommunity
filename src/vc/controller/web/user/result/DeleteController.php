<?php
namespace vc\controller\web\user\result;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('savedsearch.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $searchid = $_POST["searchid"];
        \vc\lib\Assert::assertLong("searchid", $searchid, 1, 2147483647, false);
        $searchModel = $this->getDbModel('Search');
        $success = $searchModel->delete(
            array(
                'id' => intval($searchid),
                'profileid' => $this->getSession()->getUserId()
            )
        );
        if ($success) {
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('savedsearch.delete.failed'));
        }
    }
}
