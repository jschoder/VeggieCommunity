<?php
namespace vc\controller\web\mod\user;

class DeleteReasonsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle('Delete Reasons');


        if (empty($this->siteParams)) {
            $limit = 50;
        } else {
            $limit = intval($this->siteParams[0]);
        }

        $profileModel = $this->getDbModel('Profile');
        $this->getView()->set('deleteReasons', $profileModel->getDeleteReasons($limit));

        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/deleteReasons', true);
    }

    private function getSuspicionKeys($suspicions)
    {
        $suspicionKeys = array();
        foreach ($suspicions as $suspicion) {
            foreach ($suspicion as $suspicionKey => $suspicionValue) {
                if (!in_array($suspicionKey, $suspicionKeys)) {
                    $suspicionKeys[] = $suspicionKey;
                }
            }
        }
        return $suspicionKeys;
    }
}
