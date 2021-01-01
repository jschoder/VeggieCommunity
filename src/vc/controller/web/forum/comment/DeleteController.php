<?php
namespace vc\controller\web\forum\comment;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.noactivesession'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['id'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.delete.failed'));
            return;
        }

        $threadCommentModel = $this->getDbModel('ForumThreadComment');
        $threadCommentObject = $threadCommentModel->loadObject(array('hash_id' => $formValues['id']));

        $deleted = $threadCommentModel->deleteComment($formValues['id'], $this->getSession()->getUserId());
        if ($deleted) {
            $forumThreadModel = $this->getDbModel('ForumThread');
            $forumThreadModel->updateLastUpdateTimestamp($threadCommentObject->threadId);

            if ($threadCommentObject !== null) {
                $threadModel = $this->getDbModel('ForumThread');
                $threadObject = $threadModel->loadObject(array('id' => $threadCommentObject->threadId));
                $updateModel = $this->getDbModel('Update');
                $updateModel->add(
                    $threadCommentObject->id,
                    \vc\config\EntityTypes::FORUM_COMMENT,
                    \vc\object\Update::ACTION_REMOVE,
                    $threadObject->contextType,
                    $threadObject->contextId
                );
            }
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.delete.failed'));
        }
    }
}
