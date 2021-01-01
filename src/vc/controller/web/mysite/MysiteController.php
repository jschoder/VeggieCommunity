<?php
namespace vc\controller\web\mysite;

class MysiteController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $termsModel = $this->getDbModel('Terms');
        if (!$termsModel->areAllTermsConfirmed($this->getSession()->getUserId())) {
            throw new \vc\exception\RedirectException($this->path . 'account/confirmterms/');
        }

        $this->setTitle(gettext('menu.mysite'));
        $this->getView()->set('activeMenuitem', 'mysite');

        $currentUser = $this->getSession()->getProfile();
        $this->getView()->set('tabHeader', gettext('mysite.welcome.hello') . ' ' . $currentUser->nickname);

        $pictureProfileIDs = array();
        $pictureProfiles = array();

        $newsModel = $this->getDbModel('News');
        $this->getView()->set('news', $newsModel->getNews($this->getSession()->getUserId(), $this->locale));


        // Last visitors
        $profileModel = $this->getDbModel('Profile');
        $this->getView()->set(
            'visitorCount',
            $profileModel->getVisitCount($this->getSession()->getUserId())
        );

        $visitorModel = $this->getDbModel('Visitor');
        $visitors = $visitorModel->getLastVisitors(
            $this->locale,
            $this->getSession()->getUserId(),
            $pictureProfileIDs,
            $pictureProfiles,
            \vc\config\Globals::LAST_VISITORS_XS,
            true
        );
        $this->getView()->set('visitors', $visitors);

        // Saved searches
        $searchModel = $this->getDbModel('Search');
        $savedSearches = $searchModel->loadObjects(
            array('profileid' => $this->getSession()->getUserId()),
            array(),
            'weight ASC'
        );
        $this->getView()->set('savedSearches', $savedSearches);

        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures(
            $this->getSession()->getUserId(),
            $pictureProfiles
        );
        $this->getView()->set('pictures', $pictures);

        echo $this->getView()->render('mysite/mysite', true);
    }
}
