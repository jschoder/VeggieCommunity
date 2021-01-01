<?php
namespace vc\controller\web;

class TellAFriendController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if (count($this->siteParams) > 1 &&
            $this->siteParams[0] == 'profile' &&
           is_numeric($this->siteParams[1])) {
            $defaultSubject = gettext("tellafriend.subject.defaultprofile");

            $profileModel = $this->getDbModel('Profile');
            $profiles = $profileModel->getProfiles($this->locale, array(intval($this->siteParams[1])));
            if (count($profiles) > 0) {
                $profile = $profiles[0];
                $defaultMessage = gettext("tellafriend.message.defaultprofile");
                switch ($profile->gender) {
                    case 2:
                        $heSheGettextKey = 'tellafriend.message.defaultprofile.he/she.m';
                        $hisHerGettextKey = 'tellafriend.message.defaultprofile.his/her.m';
                        break;
                    case 4:
                        $heSheGettextKey = 'tellafriend.message.defaultprofile.he/she.f';
                        $hisHerGettextKey = 'tellafriend.message.defaultprofile.his/her.f';
                        break;
                    default:
                        $heSheGettextKey = 'tellafriend.message.defaultprofile.he/she.a';
                        $hisHerGettextKey = 'tellafriend.message.defaultprofile.his/her.a';
                }

                $defaultMessage = str_replace("%HE/SHE%", gettext($heSheGettextKey), $defaultMessage);
                $defaultMessage = str_replace(
                    "%NUTRITION%",
                    prepareHTML(\vc\config\Fields::getNutritionCaption(
                        $profile->nutrition,
                        $profile->nutritionFreetext,
                        $profile->gender
                    )),
                    $defaultMessage
                );
                $location = array();
                if ($profile->city != "") {
                    $location[] = $profile->city;
                }
                if ($profile->region != "") {
                    $location[] = $profile->region;
                }
                if (count($location) == 0) {
                    $location[] = $profile->countryname;
                }
                $defaultMessage = str_replace(
                    "%LOCATION%",
                    implode(", ", $location),
                    $defaultMessage
                );
                $defaultMessage = str_replace("%HIS/HER%", gettext($hisHerGettextKey), $defaultMessage);
                $defaultMessage = str_replace(
                    "%PROFILEID%",
                    $profile->id,
                    $defaultMessage
                );
            } else {
                $defaultMessage = '';
            }
        } else {
            $defaultSubject = gettext("tellafriend.subject.default");
            $defaultMessage = gettext("tellafriend.message.default");
        }

        $currentUser = $this->getSession()->getProfile();
        $this->view(
            $currentUser->nickname,
            $currentUser->email,
            $defaultSubject,
            $defaultMessage
        );
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        if ($this->isSuspicionBlocked()) {
            throw new \vc\exception\RedirectException($this->path . 'locked/');
        }

        $errorMessage = null;

        if (empty($_POST["sendername"])) {
            $errorMessage = gettext("tellafriend.sendername.empty");
        } elseif (empty($_POST["senderemail"])) {
            $errorMessage = gettext("tellafriend.senderemail.empty");
        } elseif (!isEMailValid($_POST["senderemail"])) {
            $errorMessage = gettext("tellafriend.senderemail.invalid");
        } elseif (empty($_POST["subject"])) {
            $errorMessage = gettext("tellafriend.subject.empty");
        } elseif (empty($_POST["reciever"][0]) &&
                  empty($_POST["reciever"][1]) &&
                  empty($_POST["reciever"][2]) &&
                  empty($_POST["reciever"][3]) &&
                  empty($_POST["reciever"][4]) &&
                  empty($_POST["reciever"][5])) {
            $errorMessage = gettext("tellafriend.reciever.empty");
        } elseif ((!empty($_POST["reciever"][0]) && !isEMailValid($_POST["reciever"][0])) ||
                  (!empty($_POST["reciever"][1]) && !isEMailValid($_POST["reciever"][1])) ||
                  (!empty($_POST["reciever"][2]) && !isEMailValid($_POST["reciever"][2])) ||
                  (!empty($_POST["reciever"][3]) && !isEMailValid($_POST["reciever"][3])) ||
                  (!empty($_POST["reciever"][4]) && !isEMailValid($_POST["reciever"][4])) ||
                  (!empty($_POST["reciever"][5]) && !isEMailValid($_POST["reciever"][5]))) {
            $errorMessage = gettext("tellafriend.reciever.invalid");
        } elseif (empty($_POST["message"])) {
            $errorMessage = gettext("tellafriend.message.empty");
        }

        if ($errorMessage !== null) {
            $this->getView()->set('errorMessage', $errorMessage);

            $currentUser = $this->getSession()->getProfile();
            $defaultSenderName = $currentUser->nickname;
            $defaultSenderEmail = $currentUser->email;
            $defaultSubject = array_key_exists("subject", $_POST) ? $_POST['subject'] : '';
            $defaultMessage = array_key_exists("message", $_POST) ? $_POST['message'] : '';
            $defaultReciever1 = '';
            $defaultReciever2 = '';
            $defaultReciever3 = '';
            $defaultReciever4 = '';
            $defaultReciever5 = '';
            $defaultReciever6 = '';
            if (array_key_exists("reciever", $_POST) &&
               is_array($_POST['reciever'])) {
                if (count($_POST["reciever"]) > 0) {
                    $defaultReciever1 = $_POST["reciever"][0];
                }
                if (count($_POST["reciever"]) > 1) {
                    $defaultReciever2 = $_POST["reciever"][1];
                }
                if (count($_POST["reciever"]) > 2) {
                    $defaultReciever3 = $_POST["reciever"][2];
                }
                if (count($_POST["reciever"]) > 3) {
                    $defaultReciever4 = $_POST["reciever"][3];
                }
                if (count($_POST["reciever"]) > 4) {
                    $defaultReciever5 = $_POST["reciever"][4];
                }
                if (count($_POST["reciever"]) > 5) {
                    $defaultReciever6 = $_POST["reciever"][5];
                }
            }
            $this->view(
                $defaultSenderName,
                $defaultSenderEmail,
                $defaultSubject,
                $defaultMessage,
                $defaultReciever1,
                $defaultReciever2,
                $defaultReciever3,
                $defaultReciever4,
                $defaultReciever5,
                $defaultReciever6
            );
        } else {
            // Save for marketing-purpose and fighting spam
            $reciever1 = "";
            $reciever2 = "";
            $reciever3 = "";
            $reciever4 = "";
            $reciever5 = "";
            $reciever6 = "";
            if (count($_POST["reciever"]) > 0) {
                $reciever1 = $_POST["reciever"][0];
            }
            if (count($_POST["reciever"]) > 1) {
                $reciever2 = $_POST["reciever"][1];
            }
            if (count($_POST["reciever"]) > 2) {
                $reciever3 = $_POST["reciever"][2];
            }
            if (count($_POST["reciever"]) > 3) {
                $reciever4 = $_POST["reciever"][3];
            }
            if (count($_POST["reciever"]) > 4) {
                $reciever5 = $_POST["reciever"][4];
            }
            if (count($_POST["reciever"]) > 5) {
                $reciever6 = $_POST["reciever"][5];
            }

            $toldafriendModel = $this->getDbModel('Toldafriend');
            $toldafriendModel->add(
                $_POST['senderemail'],
                $reciever1,
                $reciever2,
                $reciever3,
                $reciever4,
                $reciever5,
                $reciever6,
                $_POST['subject'],
                $_POST['message'],
                $this->getSession()->getUserId()
            );

            $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, gettext("tellafriend.sent.success"));
            throw new \vc\exception\RedirectException($this->path . "tellafriend/?notification=" . $notification);
        }
    }

    private function view(
        $defaultSenderName,
        $defaultSenderEmail,
        $defaultSubject,
        $defaultMessage,
        $defaultReciever1 = '',
        $defaultReciever2 = '',
        $defaultReciever3 = '',
        $defaultReciever4 = '',
        $defaultReciever5 = '',
        $defaultReciever6 = ''
    ) {
        $this->setTitle(gettext("menu.tellafriend"));
        $this->getView()->set('activeMenuitem', 'tellafriend');

        $this->getView()->setHeader('robots', 'noindex, follow');

        $this->getView()->set('defaultSenderName', $defaultSenderName);
        $this->getView()->set('defaultSenderEmail', $defaultSenderEmail);
        $this->getView()->set('defaultSubject', $defaultSubject);
        $this->getView()->set('defaultMessage', $defaultMessage);

        $this->getView()->set('defaultReciever1', $defaultReciever1);
        $this->getView()->set('defaultReciever2', $defaultReciever2);
        $this->getView()->set('defaultReciever3', $defaultReciever3);
        $this->getView()->set('defaultReciever4', $defaultReciever4);
        $this->getView()->set('defaultReciever5', $defaultReciever5);
        $this->getView()->set('defaultReciever6', $defaultReciever6);

        echo $this->getView()->render('tellafriend', true);
    }
}
