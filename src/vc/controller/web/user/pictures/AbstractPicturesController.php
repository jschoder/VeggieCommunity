<?php
namespace vc\controller\web\user\pictures;

abstract class AbstractPicturesController extends \vc\controller\web\AbstractWebController
{

    protected function testFilehash($path)
    {
        $filehash = hash_file('sha256', $path);
        $bannedPictureModel = $this->getDbModel('BannedPicture');
        $blockedPics = $bannedPictureModel->getDeleteReasonsByFilehash($filehash);
        if (!empty($blockedPics)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_SPAM_BLOCKED_PIC,
                array(
                    'filehash' => $filehash
                )
            );

            $modMessage = 'User just uploaded blocked picture:' . "\n";
            foreach ($blockedPics as $profileId => $deleteReason) {
                $modMessage .= $profileId . ': ' . $deleteReason;
            }

            $modMessageModel = $this->getDbModel('ModMessage');
            $modMessageModel->addMessage(
                $this->getSession()->getUserId(),
                $this->getIp(),
                $modMessage
            );
        }
    }
}