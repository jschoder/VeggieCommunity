<?php
namespace vc\controller\web\user;

class ListController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        $this->setFullPage(false);

        if (count($this->siteParams) > 0) {
            $profileModel = $this->getDbModel('Profile');

            if (count($this->siteParams) > 1) {
                $limit = intval($this->siteParams[1]);
            } else {
                $limit = null;
            }

            if ($this->siteParams[0] == 'lastvisitors' &&
                $this->getSession()->hasActiveSession() &&
                $this->getSession()->getSetting(\vc\object\Settings::VISIBLE_LAST_VISITOR)) {
                $visitorModel = $this->getDbModel('Visitor');
                $profileIds = $visitorModel->getFieldList(
                    'visitor_id',
                    array(
                        'profile_id' => $this->getSession()->getUserId()
                    ),
                    array(),
                    'last_visit DESC',
                    $limit === null ? 30 : intval($limit)
                );
            } elseif ($this->siteParams[0] == 'onlineprofiles') {
                $profileIds = array();
                $onlineProfileIDs = $this->getUsersOnline();
                if ($limit === null) {
                    $limit = 30;
                }
                for ($i=0; $i < $limit && $i < count($onlineProfileIDs); $i++) {
                    $profileIds[] = $onlineProfileIDs[$i];
                }
            } elseif ($this->siteParams[0] == 'newprofiles') {
                $profileIds = $profileModel->getProfileIdsByColumn(
                    'first_entry',
                    $limit === null ? 25 : intval($limit),
                    $this->getBlocked()
                );
            } elseif ($this->siteParams[0] == 'lastupdates') {
                $profileIds = $profileModel->getProfileIdsByColumn(
                    'last_update',
                    $limit === null ? 25 : intval($limit),
                    $this->getBlocked()
                );
            } elseif ($this->siteParams[0] == 'lastlogins') {
                $profileIds = $profileModel->getProfileIdsByColumn(
                    'last_login',
                    $limit === null ? 25 : intval($limit),
                    $this->getBlocked()
                );
            } else {
                $profileIds = array();
            }
            if (count($profileIds) === 0) {
                $profiles = array();
                $pictures = array();
            } else {
                $profiles = $profileModel->getSmallProfiles($this->locale, $profileIds);
                $pictureModel = $this->getDbModel('Picture');
                $pictures = $pictureModel->readPictures(
                    $this->getSession()->getUserId(),
                    $profiles
                );
            }
        } else {
            $profiles = array();
            $pictures = array();
        }
        $this->getView()->set('profiles', $profiles);
        $this->getView()->set('pictures', $pictures);
        echo $this->getView()->render('user/list', false);
    }
}
