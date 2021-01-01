<?php
namespace vc\controller\web\account;

class SetController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('error.noactivesession'));
            return;
        }

        if (count($this->siteParams) < 3) {
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        if ($this->siteParams[0] == 'settings' &&
            is_numeric($this->siteParams[1])) {
            $settingsModel = $this->getDbModel('Settings');
            if ($this->siteParams[2] === '0' ||
                $this->siteParams[2] === '1') {
                $success = $settingsModel->setBooleanValue(
                    $this->getSession()->getUserId(),
                    intval($this->siteParams[1]),
                    intval($this->siteParams[2])
                );
            } else {
                $success = $settingsModel->setStringValue(
                    $this->getSession()->getUserId(),
                    intval($this->siteParams[1]),
                    $this->siteParams[2]
                );
            }

            $cacheModel = $this->getModel('Cache');
            $cacheModel->resetSettingsCache($this->getSession()->getUserId());
            echo \vc\view\json\View::renderStatus($success);
        } else {
            echo \vc\view\json\View::renderStatus(false);
        }
    }
}
