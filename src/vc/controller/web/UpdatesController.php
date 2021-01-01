<?php
namespace vc\controller\web;

class UpdatesController extends \vc\controller\web\AbstractWebController
{
    protected function logPageView()
    {
        return false;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession() ||
            empty($_GET['entityTypes']) ||
            empty($_GET['after'])) {
            $this->echoEmptyUpdates();
            return;
        }

        $entityTypes = $_GET['entityTypes'];
        if (count($entityTypes) == 2 &&
                  $entityTypes[0] == \vc\config\EntityTypes::FORUM_THREAD &&
                  $entityTypes[1] == \vc\config\EntityTypes::FORUM_COMMENT) {
            $this->loadForumUpdates();
        } else {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GET_REQUEST,
                array(
                    'type' => 'Invalid EntityTypes',
                    'get' => $_GET
                )
            );
            return;
        }
    }

    private function echoEmptyUpdates()
    {
        $return = array();
        $return['threads'] = array(
            'add' => array(),
            'edit' => array(),
            'remove' => array(),
        );
        $return['comments'] = array(
            'add' => array(),
            'edit' => array(),
            'remove' => array(),
        );
        $return['lastUpdate'] = 0;
        echo \vc\view\json\View::render($return);
    }

    private function loadForumUpdates()
    {
        $queryEntityTypes = array(
            \vc\config\EntityTypes::FORUM_THREAD,
            \vc\config\EntityTypes::FORUM_COMMENT
        );
        $currentUser = $this->getSession()->getProfile();
        $contextType = intval($_GET['contextType']);
        $after = intval($_GET['after']);
        $queryWhere = array(
            'entity_type' => $queryEntityTypes,
            'last_update >' => date('Y-m-d H:i:s', $after)
        );
        $queryJoins = array();

        if ($contextType == \vc\config\EntityTypes::GROUP_FORUM) {
            $groupForumModel = $this->getDbModel('GroupForum');
            $forumObject = $groupForumModel->loadObject(array('hash_id' => $_GET['contextId']));
            if ($forumObject === null) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM,
                    array(
                        'contextId' => $_GET['contextId']
                    )
                );
                $this->echoEmptyUpdates();
                return;
            }
            $groupModel = $this->getDbModel('Group');
            $groupObject = $groupModel->loadObject(array('id' => $forumObject->groupId));
            $forumService = new \vc\model\service\forum\GroupForumService(
                $this->modelFactory,
                $this->path,
                $groupObject,
                $forumObject,
                $this->getSession()->getUserId(),
                $this->getSession()->isAdmin()
            );
            $queryWhere['context_type'] = $contextType;
            $queryWhere['context_id'] = $forumService->getContextId();
        } elseif ($contextType == \vc\config\EntityTypes::EVENT) {
            $eventModel = $this->getDbModel('Event');
            $eventObject = $eventModel->loadObject(array('hash_id' => $_GET['contextId']));
            if ($eventObject === null) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_EVENT,
                    array(
                        'contextId' => $_GET['contextId']
                    )
                );
                $this->echoEmptyUpdates();
                return;
            }

            $eventParticipantModel = $this->getDbModel('EventParticipant');
            $eventParticipant = $eventParticipantModel->loadObject(
                array(
                    'profile_id' => $this->getSession()->getUserId(),
                    'event_id' => $eventObject->id
                )
            );
            $forumService = new \vc\model\service\forum\EventForumService(
                $this->modelFactory,
                $this->path,
                $eventObject,
                $this->getSession()->getUserId(),
                $eventParticipant,
                $this->getSession()->isAdmin()
            );
            $queryWhere['context_type'] = $contextType;
            $queryWhere['context_id'] = $forumService->getContextId();
        } elseif ($contextType == \vc\config\EntityTypes::PROFILE) {
            if (!$this->getSession()->hasActiveSession()) {
                $this->echoEmptyUpdates();
                return;
            }

            $forumService = new \vc\model\service\forum\ProfileForumService(
                $this->modelFactory,
                $this->path,
                $this->getSession()->getUserId()
            );

            // Join both the threads and the comments with vc_thread_feed in order to select all entries related to
            // either one. Also include all own threads which won't be added to vc_thread_feed
            $queryJoins['LEFT JOIN vc_feed_thread thread_feed ON vc_update.entity_type = ' .
                \vc\config\EntityTypes::FORUM_THREAD .
                ' AND thread_feed.user_id = ? AND thread_feed.thread_id = vc_update.entity_id']
                = array($this->getSession()->getUserId());
            $queryJoins[] = 'LEFT JOIN vc_forum_thread_comment ON vc_update.entity_type = ' .
                \vc\config\EntityTypes::FORUM_COMMENT .
                ' AND vc_forum_thread_comment.id = vc_update.entity_id';
            $queryJoins['LEFT JOIN vc_feed_thread comment_feed ' .
                'ON comment_feed.user_id = ? AND comment_feed.thread_id = vc_forum_thread_comment.thread_id']
                = array($this->getSession()->getUserId());
            $queryWhere['context_type'] = $contextType;
            $queryWhere['(context_id = ? OR thread_feed.thread_id IS NOT NULL OR comment_feed.thread_id IS NOT NULL)']
                    = array($this->getSession()->getUserId());
        } else {
            \vc\lib\ErrorHandler::error(
                'Invalid ContextType: ' . $contextType,
                __FILE__,
                __LINE__
            );
            return;
        }

        $pictureModel = $this->getDbModel('Picture');
        $ownPictureArray = $pictureModel->readPictures($currentUser->id, array($currentUser));
        $ownPicture = $ownPictureArray[$currentUser->id];

        $updateModel = $this->getDbModel('Update');
        $updates = $updateModel->loadObjects($queryWhere, $queryJoins, 'last_update ASC');

        $threadAddUpdates = array();
        $threadEditUpdates = array();
        $threadRemoveUpdates = array();
        $threadCommentAddUpdates = array();
        $threadCommentEditUpdates = array();
        $threadCommentRemoveUpdates = array();

        foreach ($updates as $update) {
            switch ($update->entityType) {
                case \vc\config\EntityTypes::PM:
                    break;
                case \vc\config\EntityTypes::FORUM_THREAD:
                    switch ($update->action) {
                        case \vc\object\Update::ACTION_ADD:
                            $threadAddUpdates[$update->entityId] = $update;
                            break;
                        case \vc\object\Update::ACTION_EDIT:
                            $threadEditUpdates[$update->entityId] = $update;
                            break;
                        case \vc\object\Update::ACTION_REMOVE:
                            $threadRemoveUpdates[$update->entityId] = $update;
                            break;
                        default:
                            \vc\lib\ErrorHandler::error(
                                'Illegal UpdateAction: ' . $update->action,
                                __FILE__,
                                __LINE__,
                                array('update' => var_export($update, true))
                            );
                    }
                    break;
                case \vc\config\EntityTypes::FORUM_COMMENT:
                    switch ($update->action) {
                        case \vc\object\Update::ACTION_ADD:
                            $threadCommentAddUpdates[$update->entityId] = $update;
                            break;
                        case \vc\object\Update::ACTION_EDIT:
                            $threadCommentEditUpdates[$update->entityId] = $update;
                            break;
                        case \vc\object\Update::ACTION_REMOVE:
                            $threadCommentRemoveUpdates[$update->entityId] = $update;
                            break;
                        default:
                            \vc\lib\ErrorHandler::error(
                                'Illegal UpdateAction: ' . $update->action,
                                __FILE__,
                                __LINE__,
                                array('update' => var_export($update, true))
                            );
                    }
                    break;
            }
        }

        $return = array();
        $lastUpdate = $after;

        if (count($threadCommentAddUpdates) > 0 ||
            count($threadCommentEditUpdates) > 0 ||
            count($threadCommentRemoveUpdates) > 0 ||
            count($threadAddUpdates) > 0 ||
            count($threadEditUpdates) > 0 ||
            count($threadRemoveUpdates) > 0) {
            $profileIds = array();
            if (count($threadAddUpdates) > 0 ||
                count($threadEditUpdates) > 0 ||
                count($threadRemoveUpdates) > 0) {
                $threadModel = $this->getDbModel('ForumThread');
                $threadObjects = $threadModel->loadObjects(
                    array('id' => array_unique(array_merge(
                        array_keys($threadAddUpdates),
                        array_keys($threadEditUpdates),
                        array_keys($threadRemoveUpdates)
                    )))
                );
                foreach ($threadObjects as $i => $threadObject) {
                    $profileIds[] = $threadObject->createdBy;

                    if ($threadObject->has(\vc\object\ForumThread::ADDITIONAL_ACTIVITY_PROFILE_ID)) {
                        $profileIds[] = $threadObject->additional[
                            \vc\object\ForumThread::ADDITIONAL_ACTIVITY_PROFILE_ID
                        ];
                    }
                }
            } else {
                $threadObjects = array();
            }
            if (count($threadCommentAddUpdates) > 0 ||
                count($threadCommentEditUpdates) > 0 ||
                count($threadCommentRemoveUpdates) > 0) {
                $threadCommentModel = $this->getDbModel('ForumThreadComment');
                $threadCommentObjects = $threadCommentModel->loadObjects(
                    array('id' => array_unique(array_merge(
                        array_keys($threadCommentAddUpdates),
                        array_keys($threadCommentEditUpdates),
                        array_keys($threadCommentRemoveUpdates)
                    )))
                );
                foreach ($threadCommentObjects as $i => $threadCommentObject) {
                    $profileIds[] = $threadCommentObject->createdBy;
                }
            } else {
                $threadCommentObjects = array();
            }

            $profileIds = array_unique($profileIds);

            $profileModel = $this->getDbModel('Profile');
            $profiles = $profileModel->getProfiles($this->locale, $profileIds);
            $profilesIndexed = array();
            foreach ($profiles as $profile) {
                $profilesIndexed[$profile->id] = $profile;
            }

            $pictureModel = $this->getDbModel('Picture');
            $pictures = $pictureModel->readPictures(
                $this->getSession()->getUserId(),
                $profiles
            );

            $likeModel = $this->getDbModel('Like');
            $likes = $likeModel->getForumLikes(
                array_merge(array_keys($threadAddUpdates), array_keys($threadEditUpdates)),
                array_merge(array_keys($threadCommentAddUpdates), array_keys($threadCommentEditUpdates))
            );

            if (count($threadObjects) > 0) {
                $return['threads'] = array(
                    'add' => array(),
                    'edit' => array(),
                    'remove' => array(),
                );
                foreach ($threadObjects as $threadObject) {
                    if (array_key_exists($threadObject->id, $threadRemoveUpdates)) {
                        $updateObject = $threadRemoveUpdates[$threadObject->id];
                        $return['threads']['remove'][] = $threadObject->hashId;
                        $lastUpdate = max($lastUpdate, strtotime($updateObject->lastUpdate));
                    } else {
                        if (array_key_exists($threadObject->id, $threadAddUpdates)) {
                            $updateObject = $threadAddUpdates[$threadObject->id];
                            $actions = $forumService->getThreadActions($threadObject);
                            $rendered = $this->getView()->element(
                                'forum/thread',
                                array(
                                    'path' => $this->path,
                                    'imagesPath' => $this->imagesPath,
                                    'displayAuthors' => true, // Only group members can access this
                                    'thread' => $threadObject,
                                    'currentUser' => $this->getSession()->getProfile(),
                                    'isLikable' => true, // Only group members can access this
                                    'canPostComment' => true, // Only group members can access this
                                    'ownPicture' => $ownPicture,
                                    'profiles' => $profilesIndexed,
                                    'pictures' => $pictures,
                                    'actions' => $actions,
                                    'likes' => $likes,
                                    'flags' => $forumService->getFlags()
                                )
                            );
                            $return['threads']['add'][$threadObject->hashId] = $rendered;
                            $lastUpdate = max($lastUpdate, strtotime($updateObject->lastUpdate));
                        } else { // if (array_key_exists($threadObject->id, $threadEditUpdates)) {
                            $updateObject = $threadEditUpdates[$threadObject->id];
                            $actions = $forumService->getThreadActions($threadObject);
                            $rendered = $this->getView()->element(
                                'forum/threadArticle',
                                array(
                                    'path' => $this->path,
                                    'imagesPath' => $this->imagesPath,
                                    'displayAuthors' => true, // Only group members can access this
                                    'thread' => $threadObject,
                                    'profiles' => $profilesIndexed,
                                    'isLikable' => true, // Only group members can access this
                                    'canPostComment' => true, // Only group members can access this
                                    'actions' => $actions,
                                    'likes' => $likes,
                                    'flags' => $forumService->getFlags()
                                )
                            );
                            $return['threads']['edit'][$threadObject->hashId] = $rendered;
                            $lastUpdate = max($lastUpdate, strtotime($updateObject->lastUpdate));
                        }
                    }
                }
            }

            if (count($threadCommentObjects) > 0) {
                $return['comments'] = array(
                    'add' => array(),
                    'edit' => array(),
                    'remove' => array(),
                );
                $threadModel = $this->getDbModel('ForumThread');
                foreach ($threadCommentObjects as $threadCommentObject) {
                    if (array_key_exists($threadCommentObject->id, $threadCommentRemoveUpdates)) {
                        $updateObject = $threadCommentRemoveUpdates[$threadCommentObject->id];
                        $return['comments']['remove'][] = $threadCommentObject->hashId;
                        $lastUpdate = max($lastUpdate, strtotime($updateObject->lastUpdate));
                    } else {
                        $threadHashId = $threadModel->getHashIdById($threadCommentObject->threadId);
                        $actions = $forumService->getCommentActions($threadCommentObject);
                        $rendered = $this->getView()->element(
                            'forum/comment',
                            array(
                                'path' => $this->path,
                                'imagesPath' => $this->imagesPath,
                                'displayAuthors' => true, // Only group members can access this
                                'threadHashId' => $threadHashId,
                                'threadComment' => $threadCommentObject,
                                'currentUser' => $this->getSession()->getProfile(),
                                'isLikable' => true, // Only group members can access this
                                'profiles' => $profilesIndexed,
                                'pictures' => $pictures,
                                'actions' => $actions,
                                'likes' => $likes,
                                'flags' => $forumService->getFlags()
                            )
                        );
                        if (array_key_exists($threadCommentObject->id, $threadCommentAddUpdates)) {
                            $updateObject = $threadCommentAddUpdates[$threadCommentObject->id];
                            $return['comments']['add'][$threadHashId] = $rendered;
                            $lastUpdate = max($lastUpdate, strtotime($updateObject->lastUpdate));
                        } else { // if (array_key_exists($threadObject->id, $threadEditUpdates)) {
                            $updateObject = $threadCommentEditUpdates[$threadCommentObject->id];
                            $return['comments']['edit'][$threadCommentObject->hashId] = $rendered;
                            $lastUpdate = max($lastUpdate, strtotime($updateObject->lastUpdate));
                        }
                    }
                }
            }
        }
        $return['lastUpdate'] = $lastUpdate;
        echo \vc\view\json\View::render($return);
    }
}
