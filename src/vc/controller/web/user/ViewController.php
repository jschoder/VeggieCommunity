<?php
namespace vc\controller\web\user;

class ViewController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $profileModel = $this->getDbModel('Profile');

        if ($this->getSession()->hasActiveSession() && count($this->siteParams)> 0) {
            $id = intval($this->siteParams[0]);
            if (!empty($id) && is_numeric($id)) {
                // Visitor counter
                if (!$this->getSession()->hasActiveSession() || $id != $this->getSession()->getUserId()) {
                    $profileModel->addVisit(
                        $id,
                        $this->getIp()
                    );
                }

                // Last visitor
                if ($this->getSession()->hasActiveSession() &&
                   $this->getSession()->getSetting(\vc\object\Settings::VISIBLE_LAST_VISITOR) &&
                   $id != $this->getSession()->getUserId()) {
                    if (!in_array(intval($id), $this->getBlocked())) {
                        $profileModel->addLastVisitor(
                            $id,
                            $this->getSession()->getUserId()
                        );
                    }
                }
            }
        }

        if (count($this->siteParams) == 0) {
            throw new \vc\exception\NotFoundException('Empty siteparam');
        }

        $id = $this->siteParams[0];
        if (empty($id) || !is_numeric($id)) {
            $this->getView()->setHeader('robots', 'noindex, follow');
            $this->getView()->set('message', gettext('profile.missing'));
            echo $this->getView()->render('user/view.empty', true);
            return;
        }

        if ($id == $this->getSession()->getUserId()) {
            $this->getView()->set('activeMenuitem', 'mysite');
        } else {
            $this->getView()->set('activeMenuitem', 'users');
        }

        $profiles = $profileModel->getProfiles($this->locale, array($id), !$this->getSession()->isAdmin());
        if (count($profiles) == 1) {
            $profile = $profiles[0];
            if ($profile->lastUpdate > $profile->lastLogin) {
                header('Last-Modified: ' . prepareTextDate(strtotime($profile->lastUpdate)));
            } else {
                header('Last-Modified: ' . prepareTextDate(strtotime($profile->lastLogin)));
            }

            if (!$this->getSession()->isAdmin()) {
                if ($this->getSession()->hasActiveSession()) {
                    if (in_array($profile->id, $this->getBlocked())) {
                        // Block the user via Cookie for one year
                        setcookie('lck' . $profile->id, 'true', time()+60*60*24*365, '/');
                        echo $this->getView()->render('user/view.blocked', true);
                        return;
                    } else {
                        // unset the lock if user is not blocked
                        setcookie('lck' . $profile->id, 'true', time()-1000, '/');
                    }

                } elseif (isset($_COOKIE['lck' . $profile->id])) {
                    echo $this->getView()->render('user/view.blocked', true);
                    return;
                }
            }

            \vc\view\FieldHelper::getInstance()->setProfileOutput($profile);

            // Filling the keywords
            $keywords = array();
            $keywords[] = $profile->nickname;
            $keywords[] = prepareHTML(
                \vc\config\Fields::getNutritionCaption(
                    $profile->nutrition,
                    $profile->nutritionFreetext,
                    $profile->gender
                )
            );
            $keywords[] = $profile->city;

            $defaultTab = 'info';
            if (count($this->siteParams) > 1 &&
               in_array($this->siteParams[1], array('album', 'groups', 'contact', 'friends', 'mod'))) {
                $defaultTab = $this->siteParams[1];
            }
            $this->getView()->set('defaultTab', $defaultTab);

            // Loading the pictures
            $pictureModel = $this->getDbModel('Picture');
            $pictures = $pictureModel->getFilteredProfilePicture($profile, $this->getSession()->getUserId());

            $this->getView()->set('defaultPicture', $pictures['default']);
            $this->getView()->set('profilePictures', $pictures['album']);
            $this->getView()->set('profilePicturesHidden', $pictures['hidden']);

            $siteTitle = prepareHTML($profile->nickname, false);
            if ($profile->age > 0 && $profile->hideAge !== true) {
                $siteTitle .= ' (' . $profile->age . ') ';
            }
            $nutrition_caption = \vc\config\Fields::getNutritionCaption(
                $profile->nutrition,
                $profile->nutritionFreetext,
                $profile->gender
            );
            if (!empty($nutrition_caption)) {
                $siteTitle .= ', ' . prepareHTML($nutrition_caption);
            }
            $siteTitle .= ', ' . prepareHTML($profile->getHtmlLocation(), false);
            $this->setTitle($siteTitle);

            $questionaires = $profileModel->getQuestionaires($profile->id);
            $this->getView()->set('questionaires', $questionaires);

            $profileHobbyModel = $this->getDbModel('ProfileHobby');
            $profileHobbies = $profileHobbyModel->getFieldList('hobbyid', array('profileid' => $profile->id));
            $this->getView()->set('profileHobbies', $profileHobbies);

            $cacheModel = $this->getModel('Cache');
            $hobbies = $cacheModel->getHobbies($this->locale);
            // Filter out the ones aren't used by this user
            foreach ($hobbies as &$groupValues) {
                foreach ($groupValues['hobbies'] as $hobbyId => $hobbyName) {
                    if (!in_array($hobbyId, $profileHobbies)) {
                        unset($groupValues['hobbies'][$hobbyId]);
                    }
                }
            }
            $this->getView()->set('hobbies', $hobbies);

            if ($this->getSession()->hasActiveSession()) {
                $pmThreadModel = $this->getDbModel('PmThread');
                $threadReceivedSent = $pmThreadModel->getThreadReceiveSent(
                    $this->getSession()->getUserId(),
                    $profile->id
                );
            } else {
                $threadReceivedSent = null;
            }
            $this->getView()->set('threadReceivedSent', $threadReceivedSent);

            $profile->homepage = $this->getValidLink($profile->homepage);
            $profile->favlink1 = $this->getValidLink($profile->favlink1);
            $profile->favlink2 = $this->getValidLink($profile->favlink2);
            $profile->favlink3 = $this->getValidLink($profile->favlink3);

            if ($this->getSession()->hasActiveSession()) {
                $matchModel = $this->getDbModel('Match');
                $this->getView()->set(
                    'matchPercentage',
                    $matchModel->getPercentage($this->getSession()->getUserId(), $profile->id)
                );
                $groupModel = $this->getDbModel('Group');
                $groups = $groupModel->getVisibleGroupsByUser($profile->id, $this->getSession()->getUserId());
                $this->getView()->set('sharedGroups', $groups['shared']);
                $this->getView()->set('publicGroups', $groups['public']);
            } else {
                $this->getView()->set('matchPercentage', null);
                $this->getView()->set('sharedGroups', array());
                $this->getView()->set('publicGroups', array());
            }


            $friendModel = $this->getDbModel('Friend');
            $friends = $friendModel->getFriends($profile->id, \vc\object\Friend::FILTER_CONFIRMED, true);
            $profileIDs = array();
            foreach ($friends as $friend) {
                $profileIDs[] = $friend->friendid;
            }
            $friendProfiles = $profileModel->getSmallProfiles($this->locale, $profileIDs);
            $friendPictures = $pictureModel->readPictures(
                $this->getSession()->getUserId(),
                $friendProfiles
            );

            $headerDescription = null;
            foreach ($questionaires as $questionaireGroup) {
                if ($headerDescription === null && !empty($questionaireGroup)) {
                    $headerDescription = reset($questionaireGroup);
                }
            }
            if ($headerDescription !== null) {
                $this->getView()->setHeader(
                    'description',
                    str_replace(array("\n", "\r"), array(' ', ''), $headerDescription)
                );
            }

            $settings = $cacheModel->getSettings($profile->id);
            if ($defaultTab == 'album' || $defaultTab == 'groups' || $defaultTab == 'friends') {
                $this->getView()->setHeader('robots', 'noindex, follow');
            } else {
                if ($settings->getValue(\vc\object\Settings::SEARCHENGINE)) {
                    $this->getView()->setHeader('robots', 'index, follow');
                } else {
                    $this->getView()->setHeader('robots', 'noindex, follow');
                }
            }
            if ($pictures['default'] instanceof \vc\object\SavedPicture) {
                $this->getView()->setHeader(
                    'image',
                    'https://www.veggiecommunity.org/user/picture/crop/200/200/' . $pictures['default']->filename
                );
            } elseif ($pictures['default'] instanceof \vc\object\DefaultPicture) {
                switch ($pictures['default']->gender) {
                    case 2:
                        $filename = ($pictures['default']->hiddenImage ? 'hidden': 'default') . '-thumb-m.png';
                        break;
                    case 4:
                        $filename = ($pictures['default']->hiddenImage ? 'hidden': 'default') . '-thumb-f.png';
                        break;
                    case 6:
                        $filename = ($pictures['default']->hiddenImage ? 'hidden': 'default') . '-thumb-o.png';
                        break;
                    default:
                        $filename = ($pictures['default']->hiddenImage ? 'hidden': 'default') . '-thumb-a.png';
                }
                $this->getView()->setHeader('image', 'https://www.veggiecommunity.org' . $this->imagesPath . $filename);
            }
            $this->getView()->setHeader('ogType', 'profile');

            $this->getView()->setHeader('keywords', implode(',', $keywords));

            $this->getView()->set('friends', $friends);
            $this->getView()->set('friendProfiles', $friendProfiles);
            $this->getView()->set('friendPictures', $friendPictures);

            $this->getView()->set('actions', $this->getActions($profile));
            $this->getView()->set('extendedActions', $this->getExtendedActions($profile));
            $this->getView()->set('profile', $profile);
            $this->getView()->set('settings', $settings);

            // Redirecting empty tabs
            if ($defaultTab == 'album' && empty($pictures['album'])) {
                throw new \vc\exception\RedirectException($this->path . 'user/view/' . $profile->id . '/');
            } elseif ($defaultTab == 'friends' && count($friends) === 0) {
                throw new \vc\exception\RedirectException($this->path . 'user/view/' . $profile->id . '/');
            }

            if ($this->getSession()->isAdmin()) {
                $profileCommentLogModel = $this->getDbModel('ProfileCommentLog');
                $profileCommentLogs = $profileCommentLogModel->loadObjects(array('profile_id' => $profile->id));
                $this->getView()->set('profileCommentLogs', $profileCommentLogs);

                $forumThreadModel = $this->getDbModel('ForumThread');
                $this->getView()->set(
                    'forumThreadCount',
                    $forumThreadModel->getCount(array('created_by' => $profile->id))
                );
                $forumThreadCommentModel = $this->getDbModel('ForumThreadComment');
                $this->getView()->set(
                    'forumThreadCommentCount',
                    $forumThreadCommentModel->getCount(array('created_by' => $profile->id))
                );

                $activationTokenModel = $this->getDbModel('ActivationToken');
                $activationTokens = $activationTokenModel->loadObjects(
                    array('profile_id' => $profile->id),
                    array(),
                    'created_at DESC'
                );
                $this->getView()->set('activationTokens', $activationTokens);

                $pmModel = $this->getDbModel('Pm');
                $recipientStatusCounts = $pmModel->getRecipientStatusCount($profile->id);
                $this->getView()->set('recipientStatusCounts', $recipientStatusCounts);

                $blockedModel = $this->getDbModel('Blocked');
                $blockedUserList = $blockedModel->getFieldList('profile_id', array('blocked_id' => $profile->id));
                $this->getView()->set('blockedUserList', $blockedUserList);
                $blockingUserList = $blockedModel->getFieldList('blocked_id', array('profile_id' => $profile->id));
                $this->getView()->set('blockingUserList', $blockingUserList);
                // JOE
                $relations = $cacheModel->getProfileRelations($profile->id);
                $this->getView()->set('relationBlockedList', $relations[\vc\model\CacheModel::RELATIONS_BLOCKED]);

                $pictureWarningModel = $this->getDbModel('PictureWarning');
                $pictureWarningStates = $pictureWarningModel->getStates($profile->id);
                $this->getView()->set('pictureWarningStates', $pictureWarningStates);

                $geoIpModel = $this->getDbModel('GeoIp');
                $userIpLogModel = $this->getDbModel('UserIpLog');
                $recentLogins = array();
                foreach ($userIpLogModel->getRecentLogins($profile->id) as $access => $ip) {
                    $recentLogins[] = array(
                        $access,
                        $ip,
                        $geoIpModel->getIso2ByIp($ip)
                    );
                }
                $this->getView()->set('recentLogins', $recentLogins);

                $deleteReason = $profileModel->getField('delete_reason', 'id', $profile->id);
                $this->getView()->set('deleteReason', $deleteReason);
            }
            echo $this->getView()->render('user/view', true);
        } else {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_DELETED_PROFILE,
                array('id' => $id)
            );

            $profileActive =  $profileModel->getField('active', 'id', $id);
            if ($profileActive === -1) {
                $this->getView()->set('message', gettext('profile.deleted.self'));
            } elseif ($profileActive === -21) {
                $this->getView()->set('message', gettext('profile.deleted.spam'));
            } elseif ($profileActive >= -60 && $profileActive <= -50) {
                $this->getView()->set('message', gettext('profile.deleted.termsViolation'));
            } else {
                $this->getView()->set('message', gettext('profile.missing'));
            }
            $this->getView()->setHeader('robots', 'noindex, follow');
            echo $this->getView()->render('user/view.empty', true);
        }
    }

    private function getActions($profile)
    {
        $actions = array();

        if ($profile->id == $this->getSession()->getUserId()) {
            $action = new \vc\object\Action();
            $action->setClass('edit')
                   ->setHref($this->path . 'user/edit/')
                   ->setTitle(gettext('face.ownprofile.edit'))
                   ->setCaption(gettext('face.ownprofile.edit'));
            $actions[] = $action;

            if ($this->design !== 'lemongras') {
                $action = new \vc\object\Action();
                $action->setClass('pictures')
                       ->setHref($this->path . 'user/pictures/')
                       ->setTitle(gettext('face.ownpics.edit'))
                       ->setCaption(gettext('face.ownpics.edit'));
                $actions[] = $action;

                $action = new \vc\object\Action();
                $action->setClass('matching')
                       ->setHref($this->path . 'user/matching/')
                       ->setTitle(gettext('face.ownprofile.editmatching'))
                       ->setCaption(gettext('face.ownprofile.editmatching'));
                $actions[] = $action;

                if (!$profile->realMarker) {
                    $action = new \vc\object\Action();
                    $action->setClass('real')
                           ->setHref($this->path . 'account/real/')
                           ->setTitle(gettext('menu.real'))
                           ->setCaption(gettext('menu.real'));
                    $actions[] = $action;
                }

                $action = new \vc\object\Action();
                $action->setClass('plus')
                       ->setHref($this->path . 'plus/')
                       ->setTitle(gettext('menu.plus'))
                       ->setCaption(gettext('menu.plus'));
                $actions[] = $action;
            }
        } else {
            $action = new \vc\object\Action();
            $action->setClass('pm')
                   ->setHref($this->path . 'pm/#' . $profile->id)
                   ->setTitle(gettext('profile.compose.title'))
                   ->setCaption(gettext('profile.compose'));
            $actions[] = $action;

            if (in_array($profile->id, $this->getOwnFriendsConfirmed())) {
                $action = new \vc\object\Action();
                $action->setClass('deleteFriend')
                       ->setHref('#')
                       ->setData('user-id', $profile->id)
                       ->setTitle(gettext('mysite.friends.deletefriend') . ' ' . gettext('profile.friend.title'))
                       ->setCaption(gettext('mysite.friends.deletefriend'));
                $actions[] = $action;
            } elseif (in_array($profile->id, $this->getOwnFriendsToConfirm())) {
                $action = new \vc\object\Action();
                $action->setClass('confirmFriend')
                       ->setHref('#')
                       ->setData('user-id', $profile->id)
                       ->setTitle(gettext('mailbox.friendinbox.confirm') . ' ' . gettext('profile.friend.title'))
                       ->setCaption(gettext('mailbox.friendinbox.confirm'))
                       ->setImportant();
                $actions[] = $action;
                $action = new \vc\object\Action();
                $action->setClass('denyFriend')
                       ->setHref('#')
                       ->setData('user-id', $profile->id)
                       ->setTitle(gettext('mailbox.friendinbox.deny') . ' ' . gettext('profile.friend.title'))
                       ->setCaption(gettext('mailbox.friendinbox.deny'))
                       ->setImportant();
                $actions[] = $action;
            } elseif (in_array($profile->id, $this->getOwnFriendsWaitForConfirm())) {
                $action = new \vc\object\Action();
                $action->setClass('cancelFriend')
                       ->setHref('#')
                       ->setData('user-id', $profile->id)
                       ->setTitle(gettext('profile.friend.cancel') . ' ' . gettext('profile.friend.title'))
                       ->setCaption(gettext('profile.friend.cancel'));
                $actions[] = $action;
            } else {
                $action = new \vc\object\Action();
                $action->setClass('addFriend')
                       ->setHref('#')
                       ->setData('user-id', $profile->id)
                       ->setTitle(gettext('profile.addfriend') . ' ' . gettext('profile.friend.title'))
                       ->setCaption(gettext('profile.addfriend'));
                $actions[] = $action;
            }

            if (in_array($profile->id, $this->getOwnFavorites())) {
                $action = new \vc\object\Action();
                $action->setClass('deleteFavorite')
                       ->setHref('#')
                       ->setData('user-id', $profile->id)
                       ->setTitle(gettext('profile.favorite.delete'))
                       ->setCaption(gettext('mysite.friends.deletefavorite'));
                $actions[] = $action;
            } else {
                $action = new \vc\object\Action();
                $action->setClass('addFavorite')
                       ->setHref('#')
                       ->setData('user-id', $profile->id)
                       ->setTitle(gettext('profile.favorite.title'))
                       ->setCaption(gettext('profile.addfavorite'));
                $actions[] = $action;
            }
        }

        return $actions;
    }

    private function getExtendedActions($profile)
    {
        $actions = array();

        $action = new \vc\object\Action();
        $action->setClass('share secondary')
               ->setHref($this->path . 'tellafriend/profile/' . $profile->id . '/')
               ->setTitle(gettext('profile.tellafriend.title'))
               ->setCaption(gettext('profile.tellafriend'));
        $actions[] = $action;

        if ($this->getSession()->hasActiveSession() && $this->getSession()->getUserId() != $profile->id) {
            $action = new \vc\object\Action();
            $action->setClass('block secondary')
                   ->setOnclick('blockUser(' . $profile->id .');return false;')
                   ->setTitle(gettext('profile.blockprofile.title'))
                   ->setCaption(gettext('profile.blockprofile'));
            $actions[] = $action;

            $action = new \vc\object\Action();
            $action->setClass('flag secondary')
                   ->setHref($this->path . 'help/support/reportuser/' . $profile->id . '/')
                   ->setTitle(gettext('profile.reportprofile.title'))
                   ->setCaption(gettext('profile.reportprofile'));
            $actions[] = $action;
        }

        if ($this->getSession()->isAdmin()) {
            $action = new \vc\object\Action();
            $action->setClass('mod')
                   ->setHref($this->path . 'mod/switch/' . $profile->id . '/')
                   ->setCaption(gettext('profile.mod.switch'));
            $actions[] = $action;

            $action = new \vc\object\Action();
            $action->setClass('pictures mod')
                   ->setHref($this->path . 'mod/pictures/user/' . $profile->id . '/')
                   ->setCaption('Mod Pictures');
            $actions[] = $action;

            $action = new \vc\object\Action();
            $action->setClass('pm mod')
                   ->setHref($this->path . 'mod/pm/' . $profile->id . '/')
                   ->setCaption('Mod Mails');
            $actions[] = $action;
            
            $action = new \vc\object\Action();
            $action->setClass('chat mod')
                   ->setHref($this->path . 'mod/chat/user/' . $profile->id . '/')
                   ->setCaption('Mod Chat');
            $actions[] = $action;
        }

        return $actions;
    }

    private function getValidLink($link)
    {
        if (empty($link) || $link=='') {
            return $link;
        } else {
            if (strpos($link, 'https://') !== 0 && strpos($link, 'http://') !== 0 && strpos($link, ' ') === false) {
                return 'http://' . $link;
            } else {
                return $link;
            }
        }
    }
}
