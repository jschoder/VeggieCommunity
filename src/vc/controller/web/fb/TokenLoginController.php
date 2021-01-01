<?php
namespace vc\controller\web\fb;

class TokenLoginController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        $formValues = $_POST;
        $fbMeData = $this->getFacebookMe($formValues['accessToken']);
        if (empty($fbMeData)) {
            echo \vc\view\json\View::renderStatus(false);
        } else {
            $profileModel = $this->getDbModel('Profile');
            $profile = $profileModel->loadObject(array(
                'facebook_id' => $fbMeData->getField('id'),
                'active >' => 0
            ));
            
            if (empty($profile)) {
                echo \vc\view\json\View::renderStatus(false);
            } else {
                $this->getSession()->createSession($this->locale, $profile->id, $this->getIp());
                echo \vc\view\json\View::renderStatus(true);
            }
        }
    }
}
