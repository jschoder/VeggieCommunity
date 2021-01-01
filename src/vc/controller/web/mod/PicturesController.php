<?php
namespace vc\controller\web\mod;

class PicturesController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $pictureModel = $this->getDbModel('Picture');

        if (empty($this->siteParams)) {
            throw new \vc\exception\NotFoundException();
        } elseif ($this->siteParams[0] == 'unchecked') {
            $shortTitle = 'Picture Check';
            $targetPath = 'unchecked/';
            $actions = array(
                'clear' => 'Remove picture from the checklist',
                'warn' => 'Warn the user he/she might have violated the copyright',
                'delete' => 'Deletes the picture from the database and sends them an email',
                'skip' => 'Keep the picture to look at later'
            );
            $defaultAction = 'clear';

            $pictureChecklistModel = $this->getDbModel('PictureChecklist');
            $pictureIds = $pictureChecklistModel->getFieldList('id', array());

            // Limit pictures to 500
            $pictureIds = array_slice($pictureIds, 0, 500);

            if (empty($pictureIds)) {
                $pictures = array();
            } else {
                $pictures = $pictureModel->loadObjects(array('id' => $pictureIds));
            }
        } elseif ($this->siteParams[0] == 'prewarned') {
            $shortTitle = 'Prewarned pictures';
            $targetPath = 'prewarned/';
            $actions = array(
                'confirm' => 'Marks the image as confirmed',
                'delete' => 'Deletes the picture from the database and sends them an email',
                'skip' => 'Keep the picture to look at later'
            );
            $defaultAction = 'skip';

            $pictureWarningModel = $this->getDbModel('PictureWarning');
            $pictureIds = $pictureWarningModel->getFieldList(
                'picture_id',
                array(
                    'own_pic_confirmed_at IS NULL',
                    'deleted_at IS NULL'
                ),
                array(
                    'INNER JOIN vc_picture ON vc_picture.id = vc_picture_warning.picture_id',
                    'INNER JOIN vc_profile ON vc_profile.id = vc_picture_warning.profile_id ' .
                                              'AND vc_profile.active > 0'
                )
            );

            if (empty($pictureIds)) {
                $pictures = array();
            } else {
                $pictures = $pictureModel->loadObjects(
                    array('id' => $pictureIds),
                    array(),
                    'id ASC'
                );
            }
        } elseif ($this->siteParams[0] == 'user' && count($this->siteParams) == 2) {
            $shortTitle = 'User Pictures ' . $this->siteParams[1];
            $targetPath = 'user/' . $this->siteParams[1] . '/';
            $actions = array(
                'warn' => 'Warn the user he/she might have violated the copyright',
                'delete' => 'Deletes the picture from the database and sends them an email',
                'skip' => 'Keep the picture to look at later'
            );
            $defaultAction = 'skip';
            $pictures = $pictureModel->getAllProfilePictures(intval($this->siteParams[1]));
        } else {
            throw new \vc\exception\NotFoundException;
        }

        $profileIds = array();
        foreach ($pictures as $picture) {
            $profileIds[] = $picture->profileid;
        }
        if (empty($profileIds)) {
            $profileActiveMap = array();
        } else {
            $profileModel = $this->getDbModel('Profile');
            $profileActiveMap = $profileModel->getFieldMap('id', 'active', array('id' => $profileIds));
        }

        $this->setTitle($shortTitle);
        $this->getView()->set('shortTitle', $shortTitle);
        $this->getView()->set('targetPath', $targetPath);
        $this->getView()->set('actions', $actions);
        $this->getView()->set('defaultAction', $defaultAction);
        $this->getView()->set('pictures', $pictures);
        $this->getView()->set('profileActiveMap', $profileActiveMap);
        echo $this->getView()->render('mod/pictures', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        $formValues = $_POST;

        $profileModel = $this->getDbModel('Profile');
        $pictureModel = $this->getDbModel('Picture');
        $pictureChecklistModel = $this->getDbModel('PictureChecklist');
        $pictureWarningModel = $this->getDbModel('PictureWarning');
        $ticketModel = $this->getDbModel('Ticket');
        $ticketMessageModel = $this->getDbModel('TicketMessage');
        $systemMessageModel = $this->getDbModel('SystemMessage');

        $cacheModel = $this->getModel('Cache');
        $mailComponent = $this->getComponent('Mail');

        $clearPictures = array();
        $warnPictures = array();
        $confirmPictures = array();
        $deletePictures = array();

        foreach ($formValues['action'] as $id => $action) {
            if ($action == 'clear') {
                $clearPictures[] = $id;
            } elseif ($action == 'warn') {
                $warnPictures[] = $id;
            } elseif ($action == 'confirm') {
                $confirmPictures[] = $id;
            } elseif ($action == 'delete') {
                $deletePictures[] = $id;
            }
        }

        if (!empty($clearPictures)) {
            $pictureChecklistModel->delete(array('id' => $clearPictures));
        }

        if (!empty($warnPictures)) {
            $pictures = $pictureModel->loadObjects(array('id' => $warnPictures));

            $usersToNotify = array();
            foreach ($pictures as $picture) {
                $usersToNotify[] = $picture->profileid;
            }
            $usersToNotify = array_unique($usersToNotify);

            $profiles = $profileModel->loadObjects(array('id' => $usersToNotify));
            $notifyTicket = array();
            foreach ($profiles as $profile) {
                // Render the message to user
                $userSettings = $cacheModel->getSettings($profile->id);
                if ($userSettings->getValue(\vc\object\Settings::USER_LANGUAGE) == 'de') {
                    $subject = 'Eigene Bilder?';
                } else {
                    $subject = 'Did you use your own pictures?';
                }
                $body = $mailComponent->renderMail(
                    $profile->nickname,
                    $profile->email,
                    $userSettings->getValue(\vc\object\Settings::USER_LANGUAGE),
                    'pic-warn'
                );

                // Create the ticket with the support message
                $ticket = new \vc\object\Ticket();
                $ticket->lng = $userSettings->getValue(\vc\object\Settings::USER_LANGUAGE);
                $ticket->profileId = $profile->id;
                $ticket->nickname = $profile->nickname;
                $ticket->email = $profile->email;
                $ticket->category = \vc\config\EntityTypes::PROFILE_PICTURE;
                $ticket->subject = $subject;
                $ticket->status = \vc\object\Ticket::STATUS_CLOSED;
                $objectSaved = $ticketModel->insertObject(
                    $this->getSession()->getProfile(),
                    $ticket
                );
                if ($objectSaved !== false) {
                    $ticketMessage = new \vc\object\TicketMessage();
                    $ticketMessage->ticketId = $objectSaved;
                    $ticketMessage->body = $body;
                    $ticketMessageModel->insertObject(
                        $this->getSession()->getProfile(),
                        $ticketMessage
                    );
                    $notifyTicket[$profile->id] = $objectSaved;
                }

                // Send the ticket via email to the user
                $ticketHashId = $ticketModel->getField('hash_id', 'id', $objectSaved);
                $systemMessageModel->add(
                    $profile->email,
                    $subject . ' [#' . $ticketHashId. ']',
                    $body,
                    array(),
                    \vc\object\SystemMessage::MAIL_CONFIG_SUPPORT
                );
            }

            foreach ($pictures as $picture) {
                // Add the system warning for the pictures
                $pictureWarning = new \vc\object\PictureWarning();
                $pictureWarning->pictureId = $picture->id;
                $pictureWarning->profileId = $picture->profileid;
                if (array_key_exists($picture->profileid, $notifyTicket)) {
                    $pictureWarning->ticketId = $notifyTicket[$picture->profileid];
                } else {
                    \vc\lib\ErrorHandler::error(
                        'Can\'t find ticket for pictureWarning',
                        __FILE__,
                        __LINE__,
                        array(
                            'ProfileId' => $picture->profileid,
                            'Tickets' => var_export($notifyTicket, true)
                        )
                    );
                    $pictureWarning->ticketId = 0;
                }
                $pictureWarningModel->insertObject($this->getSession()->getProfile(), $pictureWarning);
            }

            // Remove the pictures from the list
            $pictureChecklistModel->delete(array('id' => $warnPictures));
        }

        if (!empty($confirmPictures)) {
            $pictureWarningModel->update(
                array(
                    'picture_id' => $confirmPictures
                ),
                array(
                    'own_pic_confirmed_at' => date('Y-m-d H:i:s'),
                    'closed_by' => $this->getSession()->getUserId()
                ),
                false
            );
        }

        if (!empty($deletePictures)) {
            $ticketIds = $pictureWarningModel->getFieldList('ticket_id', array('picture_id' => $deletePictures));
            $ticketIds = array_unique($ticketIds);

            $tickets = $ticketModel->loadObjects(array('id' => $ticketIds));

            foreach ($tickets as $ticket) {
                $profile = $profileModel->loadObject(array('id' => $ticket->profileId));
            
                // Render the message to user
                $userSettings = $cacheModel->getSettings($ticket->profileId);
                if ($userSettings->getValue(\vc\object\Settings::USER_LANGUAGE) == 'de') {
                    $subject = 'Eigene Bilder?';
                } else {
                    $subject = 'Did you use your own pictures?';
                }
                $body = $mailComponent->renderMail(
                    $profile->nickname,
                    $profile->email,
                    $userSettings->getValue(\vc\object\Settings::USER_LANGUAGE),
                    'pic-delete'
                );

                $ticketMessage = new \vc\object\TicketMessage();
                $ticketMessage->ticketId = $ticket->id;
                $ticketMessage->body = $body;
                $ticketMessageModel->insertObject(
                    $this->getSession()->getProfile(),
                    $ticketMessage
                );

                // Send the ticket via email to the user
                $systemMessageModel->add(
                    $profileModel->getField('email', 'id', $ticket->profileId),
                    $subject . ' [#' . $ticket->hashId. ']',
                    $body,
                    \vc\object\SystemMessage::MAIL_CONFIG_SUPPORT
                );
            }

            $pictureWarningModel->update(
                array(
                    'picture_id' => $deletePictures
                ),
                array(
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'closed_by' => $this->getSession()->getUserId()
                ),
                false
            );
            
            $pictureModel->delete(
                array(
                    'id' => $deletePictures
                )
            );
        }

        throw new \vc\exception\RedirectException(
            $this->path . $this->site . '/' . implode('/', $this->siteParams) . '/'
        );
    }
}
