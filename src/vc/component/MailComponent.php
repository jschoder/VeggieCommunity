<?php
namespace vc\component;

class MailComponent extends AbstractComponent
{
    public function createPM($locale, $ip, $recipient, $body, $sourcemailid)
    {
        $sessionModel = $this->getModel('Session');
        $cacheModel = $this->getModel('Cache');
        $pmModel = $this->getDbModel('Pm');

        $settings = $cacheModel->getSettings($recipient->id);
        $currentUser = $sessionModel->getProfile();
        if ($pmModel->isUserHardBlacklisted($ip, $sessionModel->getUserId(), $body, $recipient->id)) {
            $recipientStatus = \vc\object\Mail::RECIPIENT_STATUS_UNSENT;
        } elseif (strtotime($currentUser->firstEntry) + 86400 > time() &&
                  !$pmModel->isUserWhitelisted($sessionModel->getProfile(), $recipient->id)) {
            // Block mails for profiles younger than 48 hours
            $recipientStatus = \vc\object\Mail::RECIPIENT_STATUS_UNSENT;
        } else {
            if ($settings->getValue(\vc\object\Settings::NEW_MAIL_NOTIFICATION)) {
                $currentUser = $sessionModel->getProfile();
                $sent = $this->sendMailToUser(
                    $locale,
                    $recipient->id,
                    'compose.mail.subject',
                    'message',
                    array(
                        'userId' => $currentUser->id,
                        'username' => $currentUser->nickname,
                        'userLink' => 'user/view/' . $sessionModel->getUserId() . '/',
                        'pmLink' => 'pm/#' . $sessionModel->getUserId(),
                        'message' => $body
                    ),
                    array(
                        'user-' . $sessionModel->getUserId() => array('user', $sessionModel->getUserId())
                    )
                );

                if ($sent) {
                    $recipientStatus = \vc\object\Mail::RECIPIENT_STATUS_NEW;
                } else {
                    \vc\lib\ErrorHandler::error(
                        'Can\'t add mail to send queue',
                        __FILE__,
                        __LINE__,
                        array('locale' => $locale,
                              'recipient' => $recipient->id,
                              'body' => $body,
                              'sourcemailid' => $sourcemailid)
                    );
                    $recipientStatus = \vc\object\Mail::RECIPIENT_STATUS_UNSENT;
                }
            } else {
                // If the user doesn't want a notificiation via e-mail the
                // recipientstatus will automatically be set to sent.
                $recipientStatus = \vc\object\Mail::RECIPIENT_STATUS_NEW;
            }
        }

        $success = $pmModel->create(
            $recipient->id,
            $recipientStatus,
            $sessionModel->getUserId(),
            $body,
            $sourcemailid
        );
        return $success;
    }

    public function sendMailToUser(
        $currentLocale,
        $recipientId,
        $subjectKey,
        $templateName,
        $values = array(),
        $attachments = array(),
        $mailConfig = \vc\object\SystemMessage::MAIL_CONFIG_NOTIFY,
        $pushInstantly = false,
        $requireActiveUser = true
    ) {
        $cacheModel = $this->getModel('Cache');
        $profileModel = $this->getDbModel('Profile');
        if ($requireActiveUser) {
            $profileFilter = array(
                'id' => $recipientId,
                'active >=' => 0
            );
        } else {
            $profileFilter = array(
                'id' => $recipientId
            );
        }
        $profile = $profileModel->loadObject($profileFilter);


        if ($profile === null) {
            \vc\lib\ErrorHandler::error(
                'Can\'t send mail to inactive user',
                __FILE__,
                __LINE__,
                array(
                    'recipientId' => $recipientId
                )
            );
            return false;
        }

        $settings = $cacheModel->getSettings($recipientId);
        $settingsLocale = $settings->getValue(\vc\object\Settings::USER_LANGUAGE);
        // Switching the locale over to the one configured in users settings
        if ($currentLocale !== $settingsLocale) {
            $i14nComponent = $this->getComponent('I14n');
            $i14nComponent->loadLocale($settingsLocale);
        }

        $success = $this->sendMail(
            $profile->nickname,
            $profile->email,
            $subjectKey,
            $settingsLocale,
            $templateName,
            $values,
            $attachments,
            $mailConfig,
            $pushInstantly
        );

        // Reseting the locale to whatever the current user has
        if ($currentLocale !== $settingsLocale) {
            $i14nComponent = $this->getComponent('I14n');
            $i14nComponent->loadLocale($currentLocale);
        }

        return $success;
    }

    public function sendMail(
        $recipientName,
        $recipientEmail,
        $subjectKey,
        $templateLocale,
        $templateName,
        $values = array(),
        $attachments = array(),
        $mailConfig = \vc\object\SystemMessage::MAIL_CONFIG_NOTIFY,
        $pushInstantly = false
    ) {
        $subject = gettext($subjectKey);

        $body = $this->renderMail(
            $recipientName,
            $recipientEmail,
            $templateLocale,
            $templateName,
            $values
        );

        if (empty($body)) {
            return false;
        } else {
            if ($pushInstantly) {
                $pushed = $this->pushMail($recipientEmail, $subject, $body, $attachments, $mailConfig);

                if ($pushed) {
                    return true;
                } else {
                    $systemMessageModel = $this->getDbModel('SystemMessage');
                    return $systemMessageModel->add(
                        $recipientEmail,
                        $subject,
                        $body,
                        $attachments,
                        $mailConfig
                    );
                }
            } else {
                $systemMessageModel = $this->getDbModel('SystemMessage');
                return $systemMessageModel->add(
                    $recipientEmail,
                    $subject,
                    $body,
                    $attachments,
                    $mailConfig
                );
            }
        }
    }

    public function renderMail(
            $recipientName,
            $recipientEmail,
            $templateLocale,
            $templateName,
            $values = array())
    {
        $view = new \vc\view\mail\View();

        $mailContent = $view->render($templateName, $templateLocale, $values);

        $signOff = true;
        $unsubscribeInfo = false;
        if ($templateName === 'message') {
            $signOff = false;
        }
        if ($templateName === 'friendchanged' ||
            $templateName === 'friendrequest' ||
            $templateName === 'message' ) {
            $unsubscribeInfo = true;
        }

        $body = $view->render(
            'template',
            null,
            array(
                'locale' => $templateLocale,
                'recipientName' => $recipientName,
                'recipientEmail' => $recipientEmail,
                'mailContent' => $mailContent,
                'signOff' => $signOff,
                'unsubscribeInfo' => $unsubscribeInfo
            )
        );

        return $body;
    }

    public function pushMail(
        $recipientEmail,
        $subject,
        $body,
        $attachments = array(),
        $mailConfig = \vc\object\SystemMessage::MAIL_CONFIG_NOTIFY
    ) {
        if ($this->getServer() == 'local') {
            // Overwritting recipient
            $subject = 'FAKE-MAIL [' . $subject . '] -> going to ' . $recipientEmail;
            $recipientEmail = 'myself@jschoder.de';
        }

        // Import the library once
        require_once(APP_LIB . '/phpmailer/class.phpmailer.php');
        require_once(APP_LIB . '/phpmailer/class.smtp.php');

        $mailer = new \PHPMailer();

        if (!array_key_exists($this->getServer(), \vc\config\Globals::$mail[$mailConfig])) {
            throw new \vc\exception\FatalSystemException(
                'Unknown mail-config: ' . $mailConfig . ' / ' . $this->getServer()
            );
        }

        $mailer->From = \vc\config\Globals::$mail[$mailConfig][$this->getServer()]['from'];
        $mailer->Host = \vc\config\Globals::$mail[$mailConfig][$this->getServer()]['host'];
        $mailer->Username = \vc\config\Globals::$mail[$mailConfig][$this->getServer()]['username'];
        $mailer->Password = \vc\config\Globals::$mail[$mailConfig][$this->getServer()]['password'];
        $mailer->CharSet = 'UTF-8';
        $mailer->FromName = '';
        $mailer->isSMTP();
        $mailer->SMTPSecure = 'tls';
        $mailer->Port = 25;
        if (!empty(\vc\config\Globals::$mail[$mailConfig][$this->getServer()]['hostname'])) {
            $mailer->Hostname = \vc\config\Globals::$mail[$mailConfig][$this->getServer()]['hostname'];
        }

        if (strpos($body, '<!DOCTYPE ') === 0) {
            $mailer->isHTML(true);
            if (!empty($attachments)) {
                foreach ($attachments as $fileCid => $attachment) {
                    if (is_array($attachment) && count($attachment) > 1) {
                        if ($attachment[0] === 'user') {
                            $pictureModel = $this->getDbModel('Picture');
                            $defaultPicture = $pictureModel->loadObject(array(
                                'profileid' => $attachment[1],
                                'defaultpic' => 1
                            ));

                            if (empty($defaultPicture)) {
                                $profileModel = $this->getDbModel('Profile');
                                $gender = $profileModel->getField('gender', 'id', $attachment[1]);
                                switch ($gender) {
                                    case 2:
                                        $filename = 'default-thumb-m.png';
                                        break;
                                    case 4:
                                        $filename = 'default-thumb-f.png';
                                        break;
                                    case 6:
                                        $filename = 'default-thumb-o.png';
                                        break;
                                    default:
                                        $filename = 'default-thumb-a.png';
                                }
                                $mailer->addEmbeddedImage(APP_ROOT . '/web/img/matcha/' . $filename, $fileCid);
                            } else {
                                $cropPicture = PROFILE_PIC_DIR . '/74x74/' . $defaultPicture->filename;
                                if (!file_exists($cropPicture)) {
                                    $pictureSaveComponent = $this->getComponent('PictureSave');
                                    $pictureSaveComponent->createCropPicture(
                                        PROFILE_PIC_DIR,
                                        $defaultPicture->filename,
                                        $cropPicture,
                                        74,
                                        74
                                    );
                                }
                                $mailer->addEmbeddedImage($cropPicture, $fileCid);
                            }
                        } else {
                            \vc\lib\ErrorHandler::error(
                                'Can\'t handle attachment array',
                                __FILE__,
                                __LINE__,
                                array(
                                    'subject' => $subject,
                                    'body' => $body,
                                    'attachments' => $attachments,
                                    'mailConfig' => $mailConfig
                                )
                            );
                        }
                    } else {
                        \vc\lib\ErrorHandler::error(
                            'Invalid attachment',
                            __FILE__,
                            __LINE__,
                            array(
                                'subject' => $subject,
                                'body' => $body,
                                'attachments' => $attachments,
                                'mailConfig' => $mailConfig
                            )
                        );
                    }
                }
            }
            $mailer->addEmbeddedImage(APP_ROOT . '/web/img/matcha/logo.png', 'vc-logo-png');
        }

        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AddAddress($recipientEmail, '');

        if ($mailer->Send()) {
            return true;
        } else {
            if (trim($mailer->ErrorInfo) == 'SMTP Error: The following recipients failed: ' . $recipientEmail . ': Unroutable address') {
                return true;
            } else {
                \vc\lib\ErrorHandler::warning(
                    'Can\'t send mail: ' . $mailer->ErrorInfo,
                    __FILE__,
                    __LINE__,
                    array('recipient' => $recipientEmail,
                        'subject' => $subject,
                        'body' => $body,
                        'mailConfig' => $mailConfig,
                        'mailerSend' => (trim($mailer->ErrorInfo) == 'SMTP Error: The following recipients failed: ' . $recipientEmail . ': Unroutable address'),
                        'errorInfoA' => trim($mailer->ErrorInfo),
                        'errorInfoB' => 'SMTP Error: The following recipients failed: ' . $recipientEmail . ': Unroutable address'


                        )
                );
                return false;
            }
        }
    }
}
