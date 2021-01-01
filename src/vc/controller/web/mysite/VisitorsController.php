<?php
namespace vc\controller\web\mysite;

class VisitorsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        $this->setFullPage(false);

        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }
        if (empty($this->siteParams)) {
            throw new \vc\exception\NotFoundException();
        }
        if (!$this->getSession()->getSetting(\vc\object\Settings::VISIBLE_LAST_VISITOR)) {
            throw new \vc\exception\NotFoundException();
        }

        \vc\lib\Assert::assertLong("visitors", $this->siteParams[0], 5, 100, false);
        $pictureProfileIDs = array();
        $pictureProfiles = array();

        $visitorModel = $this->getDbModel('Visitor');
        $visitors = $visitorModel->getLastVisitors(
            $this->locale,
            $this->getSession()->getUserId(),
            $pictureProfileIDs,
            $pictureProfiles,
            intval($this->siteParams[0]),
            false
        );
        $this->getView()->set('visitors', $visitors);

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures(
            $this->getSession()->getUserId(),
            $pictureProfiles
        );
        $this->getView()->set('pictures', $pictures);

        echo $this->getView()->render('mysite/visitors', false);
    }
}
