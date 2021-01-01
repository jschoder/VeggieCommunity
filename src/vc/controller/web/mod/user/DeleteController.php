<?php
namespace vc\controller\web\mod\user;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $formValues = $_POST;
        $profileId = intval($formValues['profileid']);

        // Reading the friends/favorites BEFORE deleting the profile
        $cacheModel = $this->getModel('Cache');
        $profileRelations = $cacheModel->getProfileRelations($profileId);
        $favoriteModel = $this->getDbModel('Favorite');
        // Not looking for favorites of the user, but users where this user is the favorite
        $beingFavoriteList = $favoriteModel->getFieldList('profileid', array('favoriteid' => $profileId));

        $profileModel = $this->getDbModel('Profile');
        $status = $request->getInt('status');
        if ($status === 1) {
            $profileModel->update(
                array(
                    'id '=> $profileId,
                ),
                array(
                    'active' => $status,
                    'delete_date' => null
                )
            );
        } else {
            $profileModel->deleteProfile(
                $profileId,
                $formValues['reason'],
                $status,
                array_key_exists('deletemessages', $formValues) && $formValues['deletemessages'] == 1
            );
        }

        // Reset Profile Cache for all friends, people who marked him/her as favorite
        foreach ($beingFavoriteList as $favoriteId) {
            $cacheModel->resetProfileRelations($favoriteId);
        }
        foreach ($profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_CONFIRMED] as $friendId) {
            $cacheModel->resetProfileRelations($friendId);
        }
        foreach ($profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_TO_CONFIRM] as $friendId) {
            $cacheModel->resetProfileRelations($friendId);
        }
        foreach ($profileRelations[\vc\model\CacheModel::RELATIONS_FRIENDS_WAIT_FOR_CONFIRM] as $friendId) {
            $cacheModel->resetProfileRelations($friendId);
        }

        if (array_key_exists('markimages', $formValues) && $formValues['markimages'] == 1) {
            $pictureModel = $this->getDbModel('Picture');
            $pictures = $pictureModel->getAllProfilePictures($profileId);
            $bannedPictureModel = $this->getDbModel('BannedPicture');
            foreach ($pictures as $picture) {
                $bannedPictureObject = new \vc\object\BannedPicture();
                $bannedPictureObject->profileId = $profileId;
                $bannedPictureObject->filehash = hash_file('sha256', PROFILE_PIC_DIR . '/full/' . $picture->filename);
                $bannedPictureModel->insertObject($this->getSession()->getProfile(), $bannedPictureObject);
            }
        }

        if (array_key_exists('deleteforum', $formValues) && $formValues['deleteforum'] == 1) {
            $forumThreadModel = $this->getDbModel('ForumThread');
            $forumThreadCommentModel = $this->getDbModel('ForumThreadComment');

            $forumThreadModel->update(
                array(
                    'created_by' => $profileId
                ),
                array(
                    'deleted_by' => $this->getSession()->getUserId(),
                    'deleted_at' => date('Y-m-d H:i:s')
                ),
                false
            );
            $forumThreadCommentModel->update(
                array(
                    'created_by' => $profileId
                ),
                array(
                    'deleted_by' => $this->getSession()->getUserId(),
                    'deleted_at' => date('Y-m-d H:i:s')
                ),
                false
            );
        }

        $notification = $this->setNotification(
            self::NOTIFICATION_SUCCESS,
            'User has been deleted'
        );
        throw new \vc\exception\RedirectException(
            $this->path . 'user/view/' . $profileId . '/mod/?notification=' . $notification
        );
    }
}
