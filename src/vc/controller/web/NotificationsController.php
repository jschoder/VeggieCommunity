<?php
namespace vc\controller\web;

class NotificationsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo '<li class="failed">' . gettext('menu.reload.sessionexpired') . '</li>';
            return;
        }

        if (empty($this->siteParams)) {
            throw new \vc\exception\NotFoundException();
        } elseif ($this->siteParams[0] == 'pm') {
            $notifications = $this->getPmNotifications();
        } elseif ($this->siteParams[0] == 'friends') {
            $notifications = $this->getFriendsNotifications();
        } elseif ($this->siteParams[0] == 'events') {
            $notifications = $this->getEventsNotifications();
        } elseif ($this->siteParams[0] == 'groups') {
            $notifications = $this->getGroupsNotifications();
        } else {
            throw new \vc\exception\NotFoundException();
        }

        usort(
            $notifications,
            function ($notification1, $notification2) {
                return $notification2[0] - $notification1[0];
            }
        );

        foreach ($notifications as $notification) {
            echo $notification[1];
        }

        if ($this->siteParams[0] == 'pm') {
            echo $this->getView()->element(
                'notification/pm/footer',
                array(
                    'path' => $this->path,
                    'notifications' => $notifications
                )
            );
        } elseif ($this->siteParams[0] == 'friends') {
            echo $this->getView()->element(
                'notification/friends/footer',
                array(
                    'path' => $this->path,
                    'notifications' => $notifications
                )
            );
        } elseif ($this->siteParams[0] == 'events') {
            echo $this->getView()->element(
                'notification/events/footer',
                array(
                    'path' => $this->path,
                    'notifications' => $notifications
                )
            );
        } elseif ($this->siteParams[0] == 'groups') {
            echo $this->getView()->element(
                'notification/groups/footer',
                array(
                    'path' => $this->path,
                    'notifications' => $notifications
                )
            );
        }
    }

    private function getPmNotifications()
    {
        $currentUser = $this->getSession()->getProfile();
        $notifications = array();

        $pmThreadModel = $this->getDbModel('PmThread');
        $pmThreads = $pmThreadModel->getThreads($this->getSession()->getUserId(), 4);

        foreach ($pmThreads as $pmThread) {
            $rendered = $this->getView()->element(
                'notification/pm/inbox',
                array(
                    'path' => $this->path,
                    'imagesPath' => $this->imagesPath,
                    'currentUser' => $currentUser,
                    'contact' => $pmThread['contact'],
                    'message' => $pmThread['lastMessage'],
                    'isNew' => $pmThread['isNew']
                )
            );
            $notifications[] = array(
                $pmThread['created'],
                $rendered
            );
        }
        return $notifications;
    }

    private function getFriendsNotifications()
    {
        $notifications = array();

        $ownFriendsToConfirm = $this->getOwnFriendsToConfirm();
        if (!empty($ownFriendsToConfirm)) {
            $profileModel = $this->getDbModel('Profile');
            $unconfirmedProfiles = $profileModel->getSmallProfiles(
                $this->locale,
                $ownFriendsToConfirm,
                'last_update DESC'
            );

            $pictureModel = $this->getDbModel('Picture');
            $unconfirmedPictures = $pictureModel->readPictures(
                $this->getSession()->getUserId(),
                $unconfirmedProfiles
            );

            foreach ($unconfirmedProfiles as $profile) {
                $rendered = $this->getView()->element(
                    'notification/friends/inbox',
                    array(
                        'path' => $this->path,
                        'imagesPath' => $this->imagesPath,
                        'profile' => $profile,
                        'picture' => $unconfirmedPictures[$profile->id],
                        'currentUser' => $this->getSession()->getProfile()
                    )
                );
                $notifications[] = array(
                    0,
                    $rendered
                );
            }
        }
        return $notifications;
    }

    private function getEventsNotifications()
    {
        $notifications = array();

        return $notifications;
    }

    private function getGroupsNotifications()
    {
        $currentUser = $this->getSession()->getProfile();

        $groupNotificationModel = $this->getDbModel('GroupNotification');
        $groupNotifications = $groupNotificationModel->loadObjects(
            array(
                'profile_id' => $currentUser->id,
                '(seen_at IS NULL OR seen_at > ?)' => date('Y-m-d H:i:s', time() - (3600 * \vc\config\Globals::NOTIFICATION_TIME_FRAME))
            )
        );

        $notifications = array();
        foreach ($groupNotifications as $notification) {
            switch ($notification->notificationType) {
                case \vc\object\GroupNotification::TYPE_GROUP_MEMBER_REQUESTS:
                    $groupModel = $this->getDbModel('Group');
                    $group = $groupModel->loadObject(array(
                        'id' => $notification->entityId
                    ));
                    if ($group === null) {
                        \vc\lib\ErrorHandler::error(
                            'Missing group',
                            __FILE__,
                            __LINE__,
                            array(
                                'profileId' => $notification->profileId,
                                'notificationType' => $notification->notificationType,
                                'entityId' => $notification->entityId,
                            )
                        );
                    } else {
                        $rendered = $this->getView()->element(
                            'notification/groups/simple',
                            array(
                                'path' => $this->path,
                                'imagesPath' => $this->imagesPath,
                                'currentUser' => $currentUser,
                                'seenAt' => $notification->seenAt,
                                'link' => $this->path . 'groups/info/' . $group->hashId . '/',
                                'groupImage' => $group->image,
                                'message' => sprintf(
                                    gettext('group.notification.memberRequest'),
                                    prepareHTML($group->name)
                                ),
                                'lastUpdate' => $notification->lastUpdate
                            )
                        );
                        $notifications[] = array(
                            strtotime($notification->lastUpdate),
                            $rendered
                        );
                    }
                    break;
                case \vc\object\GroupNotification::TYPE_GROUP_INVITATION:
                    $groupModel = $this->getDbModel('Group');
                    $group = $groupModel->loadObject(array(
                        'id' => $notification->entityId
                    ));
                    $profileModel = $this->getDbModel('Profile');
                    $profileNickname = $profileModel->getField('nickname', 'id', $notification->userId);

                    if ($group === null) {
                        \vc\lib\ErrorHandler::error(
                            'Missing group',
                            __FILE__,
                            __LINE__,
                            array(
                                'profileId' => $notification->profileId,
                                'notificationType' => $notification->notificationType,
                                'entityId' => $notification->entityId,
                            )
                        );
                    } else {
                        $rendered = $this->getView()->element(
                            'notification/groups/simple',
                            array(
                                'path' => $this->path,
                                'imagesPath' => $this->imagesPath,
                                'currentUser' => $currentUser,
                                'seenAt' => $notification->seenAt,
                                'link' => $this->path . 'groups/info/' . $group->hashId . '/',
                                'groupImage' => $group->image,
                                'message' => sprintf(
                                    gettext('group.notification.groupInvitation'),
                                    prepareHTML($profileNickname),
                                    prepareHTML($group->name)
                                ),
                                'lastUpdate' => $notification->lastUpdate
                            )
                        );
                        $notifications[] = array(
                            strtotime($notification->lastUpdate),
                            $rendered
                        );
                    }
                    break;
                case \vc\object\GroupNotification::TYPE_GROUP_MEMBER_CONFIRMED:
                    $groupModel = $this->getDbModel('Group');
                    $group = $groupModel->loadObject(array(
                        'id' => $notification->entityId
                    ));
                    if ($group === null) {
                        \vc\lib\ErrorHandler::error(
                            'Missing group: ' . $notification->entityId,
                            __FILE__,
                            __LINE__,
                            array(
                                'profileId' => $notification->profileId,
                                'notificationType' => $notification->notificationType,
                                'entityId' => $notification->entityId,
                            )
                        );
                    } else {
                        $rendered = $this->getView()->element(
                            'notification/groups/simple',
                            array(
                                'path' => $this->path,
                                'imagesPath' => $this->imagesPath,
                                'currentUser' => $currentUser,
                                'seenAt' => $notification->seenAt,
                                'link' => $this->path . 'groups/info/' . $group->hashId . '/',
                                'groupImage' => $group->image,
                                'message' => sprintf(
                                    gettext('group.notification.memberConfirmed'),
                                    prepareHTML($group->name)
                                ),
                                'lastUpdate' => $notification->lastUpdate
                            )
                        );
                        $notifications[] = array(
                            strtotime($notification->lastUpdate),
                            $rendered
                        );
                    }
                    break;
                case \vc\object\GroupNotification::TYPE_FORUM_NEW_THREAD:
                    $forumThreadModel = $this->getDbModel('ForumThread');
                    $groupForumModel = $this->getDbModel('GroupForum');
                    $groupModel = $this->getDbModel('Group');
                    $profileModel = $this->getDbModel('Profile');
                    $pictureModel = $this->getDbModel('Picture');

                    $profile = $profileModel->loadObject(array('id' => $notification->userId));
                    $groupForum = $groupForumModel->loadObject(array('id' => $notification->entityId));
                    if ($profile === null) {
                        \vc\lib\ErrorHandler::error(
                            'Missing profile: ' . $notification->userId,
                            __FILE__,
                            __LINE__,
                            array(
                                'profileId' => $notification->profileId,
                                'notificationType' => $notification->notificationType,
                                'entityId' => $notification->entityId,
                            )
                        );
                    } else if ($groupForum === null) {
                        \vc\lib\ErrorHandler::error(
                            'Missing group forum: ' . $notification->entityId,
                            __FILE__,
                            __LINE__,
                            array(
                                'profileId' => $notification->profileId,
                                'notificationType' => $notification->notificationType,
                                'entityId' => $notification->entityId,
                            )
                        );
                    } else {
                        $group = $groupModel->loadObject(array(
                            'id' => $groupForum->groupId
                        ));
                        if ($group === null) {
                            \vc\lib\ErrorHandler::error(
                                'Missing group: ' . $groupForum->groupId,
                                __FILE__,
                                __LINE__,
                                array(
                                    'profileId' => $notification->profileId,
                                    'notificationType' => $notification->notificationType,
                                    'entityId' => $notification->entityId,
                                )
                            );
                        } else {
                            $pictures = $pictureModel->readPictures(
                                $this->getSession()->getUserId(),
                                array($profile)
                            );
                            $rendered = $this->getView()->element(
                                'notification/groups/user',
                                array(
                                    'path' => $this->path,
                                    'imagesPath' => $this->imagesPath,
                                    'currentUser' => $currentUser,
                                    'seenAt' => $notification->seenAt,
                                    'link' => $this->path . 'groups/forum/' . $group->hashId . '/' . $groupForum->hashId . '/',
                                    'picture' => empty($pictures[$profile->id]) ? null : $pictures[$profile->id],
                                    'message' => sprintf(
                                        gettext('group.notification.forum'),
                                        prepareHTML($profile->nickname),
                                        prepareHTML($group->name)
                                    ),
                                    'lastUpdate' => $notification->lastUpdate
                                )
                            );
                            $notifications[] = array(
                                strtotime($notification->lastUpdate),
                                $rendered
                            );
                        }
                    }
                    break;
                case \vc\object\GroupNotification::TYPE_FORUM_NEW_COMMENT:
                    $forumThreadModel = $this->getDbModel('ForumThread');
                    $groupForumModel = $this->getDbModel('GroupForum');
                    $groupModel = $this->getDbModel('Group');
                    $profileModel = $this->getDbModel('Profile');
                    $pictureModel = $this->getDbModel('Picture');
                    $forumThread = $forumThreadModel->loadObject(array('id' => $notification->entityId));
                    if ($forumThread === null) {
                        \vc\lib\ErrorHandler::error(
                            'Missing forum thread: ' . $notification->entityId,
                            __FILE__,
                            __LINE__,
                            array(
                                'profileId' => $notification->profileId,
                                'notificationType' => $notification->notificationType,
                                'entityId' => $notification->entityId,
                            )
                        );
                    } else {
                        $profile = $profileModel->loadObject(array('id' => $notification->userId));
                        $groupForum = $groupForumModel->loadObject(array('id' => $forumThread->contextId));
                        if ($profile === null) {
                            \vc\lib\ErrorHandler::error(
                                'Missing profile: ' . $forumThread->createdBy,
                                __FILE__,
                                __LINE__,
                                array(
                                    'profileId' => $notification->profileId,
                                    'notificationType' => $notification->notificationType,
                                    'entityId' => $notification->entityId,
                                )
                            );
                        } else if ($groupForum === null) {
                            \vc\lib\ErrorHandler::error(
                                'Missing group forum: ' . $forumThread->contextId,
                                __FILE__,
                                __LINE__,
                                array(
                                    'profileId' => $notification->profileId,
                                    'notificationType' => $notification->notificationType,
                                    'entityId' => $notification->entityId,
                                )
                            );
                        } else {
                            $group = $groupModel->loadObject(array(
                                'id' => $groupForum->groupId
                            ));
                            if ($group === null) {
                                \vc\lib\ErrorHandler::error(
                                    'Missing group: ' . $groupForum->groupId,
                                    __FILE__,
                                    __LINE__,
                                    array(
                                        'profileId' => $notification->profileId,
                                        'notificationType' => $notification->notificationType,
                                        'entityId' => $notification->entityId,
                                    )
                                );
                            } else {
                                $pictures = $pictureModel->readPictures(
                                    $this->getSession()->getUserId(),
                                    array($profile)
                                );

                                if ($currentUser->id == $forumThread->createdBy) {
                                    $message = gettext('group.notification.thread.your');
                                } else if ($profile->id == $forumThread->createdBy) {
                                    $message = gettext('group.notification.thread.herhis');
                                } else {
                                    $message = gettext('group.notification.thread.other');
                                }
                                $rendered = $this->getView()->element(
                                    'notification/groups/simple',
                                    array(
                                        'path' => $this->path,
                                        'imagesPath' => $this->imagesPath,
                                        'currentUser' => $currentUser,
                                        'seenAt' => $notification->seenAt,
                                        'link' => $this->path . 'groups/forum/' . $group->hashId . '/' .
                                                  $groupForum->hashId .'/#' . $forumThread->hashId,
                                        'picture' => empty($pictures[$profile->id]) ? null : $pictures[$profile->id],
                                        'message' =>
                                        sprintf(
                                            $message,
                                            prepareHTML($profile->nickname),
                                            prepareHTML($group->name)
                                        ),
                                        'lastUpdate' => $notification->lastUpdate
                                    )
                                );
                                $notifications[] = array(
                                    strtotime($notification->lastUpdate),
                                    $rendered
                                );
                            }
                        }
                    }
                    break;
                case \vc\object\GroupNotification::TYPE_GROUP_CREATION_ACCEPTED:
                    $groupModel = $this->getDbModel('Group');
                    $group = $groupModel->loadObject(array(
                        'id' => $notification->entityId
                    ));
                    if ($group === null) {
                        \vc\lib\ErrorHandler::error(
                            'Missing group: ' . $notification->entityId,
                            __FILE__,
                            __LINE__,
                            array(
                                'profileId' => $notification->profileId,
                                'notificationType' => $notification->notificationType,
                                'entityId' => $notification->entityId,
                            )
                        );
                    } else {
                        $rendered = $this->getView()->element(
                            'notification/groups/simple',
                            array(
                                'path' => $this->path,
                                'imagesPath' => $this->imagesPath,
                                'currentUser' => $currentUser,
                                'seenAt' => $notification->seenAt,
                                'link' => $this->path . 'groups/info/' . $group->hashId . '/',
                                'groupImage' => $group->image,
                                'message' => sprintf(
                                    gettext('group.notification.groupCreation.accepted'),
                                    prepareHTML($group->name)
                                ),
                                'lastUpdate' => $notification->lastUpdate
                            )
                        );
                        $notifications[] = array(
                            strtotime($notification->lastUpdate),
                            $rendered
                        );
                    }
                    break;
                default:
                    \vc\lib\ErrorHandler::error(
                        'Invalid notificationType ' . $notification->notificationType,
                        __FILE__,
                        __LINE__
                    );
            }
        }

        $groupNotificationModel->update(
            array(
                'profile_id' => $this->getSession()->getUserId(),
                'seen_at' => null
            ),
            array(
                'seen_at' => date('Y-m-d H:i:s')
            ),
            false
        );

        return $notifications;
    }
}
