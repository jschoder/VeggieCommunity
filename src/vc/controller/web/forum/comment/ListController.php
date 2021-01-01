<?php
namespace vc\controller\web\forum\comment;

class ListController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (empty($_GET['thread']) || empty($_GET['before'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GET_REQUEST,
                array(
                    'get' => $_GET
                )
            );
            return;
        }

        $threadId = $_GET['thread'];
        $before = $_GET['before'];

        $forumThreadModel = $this->getDbModel('ForumThread');
        $threadObject = $forumThreadModel->loadObject(array('hash_id' => $threadId));
        if ($threadObject === null) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM_THREAD,
                array(
                    'threadHashId' => $threadId
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        if ($threadObject->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
            $groupForumModel = $this->getDbModel('GroupForum');
            $forumObject = $groupForumModel->loadObject(array('id' => $threadObject->contextId));
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
        } elseif ($threadObject->contextType == \vc\config\EntityTypes::EVENT) {
            $eventModel = $this->getDbModel('Event');
            $eventObject = $eventModel->loadObject(array('id' => $threadObject->contextId));

            if ($this->getSession()->hasActiveSession()) {
                $eventParticipantModel = $this->getDbModel('EventParticipant');
                $eventParticipant = $eventParticipantModel->loadObject(
                    array(
                        'profile_id' => $this->getSession()->getUserId(),
                        'event_id' => $eventObject->id
                    )
                );
            } else {
                $eventParticipant = null;
            }

            $forumService = new \vc\model\service\forum\EventForumService(
                $this,
                $eventObject,
                $this->getSession()->getUserId(),
                $eventParticipant,
                $this->getSession()->isAdmin()
            );
        } elseif ($threadObject->contextType == \vc\config\EntityTypes::PROFILE) {
            $forumService = new \vc\model\service\forum\ProfileForumService(
                $this->modelFactory,
                $this->path,
                $this->getSession()->getUserId()
            );

        } else {

            \vc\lib\ErrorHandler::error(
                'Invalid ContextType: ' . $threadObject->contextType,
                __FILE__,
                __LINE__,
                array(
                    'threadId' => $threadObject->id
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        if (!$forumService->canRead()) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_CONTEXT_AS_NONMEMBER,
                array(
                    'contextType' => $threadObject->contextType,
                    'contextId' => $threadObject->contextId,
                    'threadId' => $threadId,
                    'before' => $before
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $displayAuthors = $forumService->doDisplayAuthors();

        $forumThreadCommentModel = $this->getDbModel('ForumThreadComment');
        $comments = $forumThreadCommentModel->loadObjects(
            array('thread_id' => $threadObject->id,
                  'created_at <' => date('Y-m-d H:i:s', intval($before)),
                  'deleted_at IS NULL'),
            array(),
            'created_at DESC',
            \vc\config\Globals::RELOAD_THREAD_COMMENT_COUNT + 1
        );

        if (count($comments) > \vc\config\Globals::RELOAD_THREAD_COMMENT_COUNT) {
            array_pop($comments);
            $moreCommentsAvailable = true;
        } else {
            $moreCommentsAvailable = false;
        }


        $profileIds = array();
        $threadCommentIds = array();
        foreach ($comments as $i => $comment) {
            $threadCommentIds[] = $comment->id;
            $profileIds[] = $comment->createdBy;
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
        $likes = $likeModel->getForumLikes(array(), $threadCommentIds);

        $flags = $forumService->getFlags();

        $renderedComments = array();
        foreach ($comments as $commentObject) {
            $actions = $forumService->getCommentActions($commentObject);
            $rendered = $this->getView()->element(
                'forum/comment',
                array(
                    'path' => $this->path,
                    'imagesPath' => $this->imagesPath,
                    'currentUser' => $this->getSession()->getProfile(),
                    'isLikable' => $forumService->isLikable(),
                    'profiles' => $profilesIndexed,
                    'pictures' => $pictures,
                    'threadHashId' => $threadObject->hashId,
                    'thread' => $threadObject,
                    'threadComment' => $commentObject,
                    'displayAuthors' => $displayAuthors,
                    'actions' => $actions,
                    'likes' => $likes,
                    'flags' => $flags
                )
            );
            $renderedComments[] = $rendered;
        }

        $return = array(
            'comments' => $renderedComments,
            'moreAvailable' => $moreCommentsAvailable
        );
        echo \vc\view\json\View::render($return);
    }
}
