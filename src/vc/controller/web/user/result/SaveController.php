<?php
namespace vc\controller\web\user\result;

class SaveController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("savesearch.noactivesession"));
        } elseif (empty($_POST["name"])) {
            $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("savesearch.name.missing"));
        } else {
            if ($this->isSuspicionBlocked()) {
                echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
                return;
            }

            if (empty($_POST["url"])) {
                throw new \vc\exception\AssertionException("Parameter url is missing.");
            }
            \vc\lib\Assert::assertLong("type", $_POST["type"], 1, 2, false);
            \vc\lib\Assert::assertLong("interval", $_POST["interval"], 0, 365, true);
            $searchModel = $this->getDbModel('Search');
            $search = new \vc\object\Search();
            $search->profileid = $this->getSession()->getUserId();
            $search->name = $_POST['name'];
            $search->url = str_replace('&amp;', '&', $_POST['url']);
            $search->messageInterval = intval($_POST['interval']);
            $search->messageType = intval($_POST['type']);
            $searchId = $searchModel->insertObject(
                $this->getSession()->getProfile(),
                $search
            );
            if ($searchId === false) {
                $notification = $this->setNotification(self::NOTIFICATION_ERROR, gettext("savesearch.failed"));
            } else {
                $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext("savesearch.success"));
            }
        }
        throw new \vc\exception\RedirectException(
            $this->path . 'user/result/?notification=' . $notification . '&' . $_POST["url"]
        );
    }
}
