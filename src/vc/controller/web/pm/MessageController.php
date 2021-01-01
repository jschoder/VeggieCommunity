<?php
namespace vc\controller\web\pm;

class MessageController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.noactivesession'));
            return;
        }

        $profileId = $this->getSession()->getUserId();
        if (count($this->siteParams) > 0) {
            $contactId = intval($this->siteParams[0]);
        } else {
            throw new \vc\exception\NotFoundException();
        }

        $pmModel = $this->getDbModel('Pm');
        $pmModel->markAllMessagesRead($profileId, $contactId);

        $pmThreadModel = $this->getDbModel('PmThread');
        $pmThreadModel->updateThread($contactId, $profileId, null, false);

        $websocketMessageModel = $this->getDbModel('WebsocketMessage');
        $websocketMessageModel->trigger(\vc\config\EntityTypes::PM, $profileId);
        $websocketMessageModel->trigger(\vc\config\EntityTypes::STATUS, $profileId);

        $profileModel = $this->getDbModel('Profile');
        $smallProfiles = $profileModel->getProfiles($this->locale, array($profileId, $contactId), false);

        $before = array_key_exists('before', $_GET) ? intval($_GET['before']) : null;
        $after = array_key_exists('after', $_GET) ? intval($_GET['after']) : null;
        $textFilter = array();
        $fromFilter = null;
        $toFilter = null;
        if (!empty($_GET['f']) && $this->getSession()->getPlusLevel() >= \vc\object\Plus::PLUS_TYPE_STANDARD) {
            if (!empty($_GET['f']['text'])) {
                $textFilterParameter = trim($_GET['f']['text']);
                if (!empty($textFilterParameter)) {
                    $textFilter = explode(' ', $textFilterParameter);
                }
            }
            if (!empty($_GET['f']['from'])) {
                $fromFilter = $_GET['f']['from'];
            }
            if (!empty($_GET['f']['to'])) {
                $toFilter = $_GET['f']['to'];
            }
        }

        $messages = $pmModel->getMessages(
            $this->getSession()->getUserId(),
            $contactId,
            $this->getSession()->getSetting(\vc\object\Settings::PM_FILTER_INCOMING),
            $before !== null && $after === null ? \vc\config\Globals::MESSAGE_COUNT_LOAD: null,
            $before,
            $after,
            $textFilter,
            $fromFilter,
            $toFilter
        );
        foreach ($messages as $i => $message) {
            $messages[$i]['subject'] = @iconv('UTF-8', 'UTF-8//IGNORE', prepareHTML($message['subject']));
            $messages[$i]['body'] = @iconv('UTF-8', 'UTF-8//IGNORE', prepareHTML($message['body'], true));
        }

        $return = array(
            'profiles' => $this->getMessageProfiles($smallProfiles),
            'messages' => $messages
        );
        if (empty($before)) {
            if ($smallProfiles[0]->id === $contactId) {
                $contact = $smallProfiles[0];
            } else {
                $contact = $smallProfiles[1];
            }

            $pmThreadModel = $this->getDbModel('PmThread');
            $return['blocked'] = $pmThreadModel->getContactUserBlock(
                $this->getBlocked(),
                $this->getSession()->getProfile(),
                $contact
            );

            $pmDraftModel = $this->getDbModel('PmDraft');
            $pmDrafts = $pmDraftModel->loadObjects(array(
                'sender_id' => $this->getSession()->getUserId(),
                'recipient_id' => $contactId
            ));
            $return['drafts'] = array();
            foreach ($pmDrafts as $pmDraft) {
                $return['drafts'][$pmDraft->id] = $pmDraft->body;
            }
        }
        if ($before !== null && $after === null) {
            $return['isLast'] = (count($messages) < \vc\config\Globals::MESSAGE_COUNT_LOAD);
        }
        echo \vc\view\json\View::render($return);
    }

    private function getMessageProfiles($smallProfiles)
    {
        $pictureModel = $this->getDbModel('Picture');
        $pictures = $pictureModel->readPictures($this->getSession()->getUserId(), $smallProfiles);

        $profiles = array();
        foreach ($smallProfiles as $profile) {
            if ($profile->active > 0 && array_key_exists($profile->id, $pictures)) {
                if ($pictures[$profile->id] instanceof \vc\object\DefaultPicture) {
                    $picture = null;
                } else {
                    $picture = $pictures[$profile->id]->filename;
                }
            } else {
                $picture = null;
            }

            switch ($profile->gender) {
                case 2:
                    $gender = 'm';
                    break;
                case 4:
                    $gender = 'f';
                    break;
                case 6:
                    $gender = 'o';
                    break;
                default:
                    $gender = 'a';
            }
            $profiles[$profile->id] = array('picture' => $picture,
                'nickname' => $profile->nickname,
                'gender' => $gender,
                'tooltip' => prepareHTML($profile->getToolTipText()),
                'isActive' => ($profile->active > 0),
                'isPlus' => ($profile->plusMarker > 0)
            );
        }
        return $profiles;

    }
}
