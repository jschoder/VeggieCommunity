<?php
namespace vc\controller\web\forum\comment;

class EditController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.noactivesession'));
            return;
        }

        $formValues = array_merge($_GET);
        if (empty($formValues['id'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.edit.failed'));
            return;
        }

        $threadCommentModel = $this->getDbModel('ForumThreadComment');
        $threadCommentObject = $threadCommentModel->loadObject(
            array(
                'hash_id' => $formValues['id'],
                'created_by' => $this->getSession()->getUserId(),
                'deleted_at IS NULL'
            )
        );
        if ($threadCommentObject === null) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.edit.failed'));
        } else {
            echo \vc\view\json\View::render(array('id' => $threadCommentObject->hashId,
                                                  'body' => $threadCommentObject->body));
        }
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.noactivesession'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['id']) ||
           empty($formValues['body'])) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.edit.failed'));
            return;
        }
        $threadCommentModel = $this->getDbModel('ForumThreadComment');
        $edited = $threadCommentModel->edit(
            $formValues['id'],
            $this->getSession()->getUserId(),
            $formValues['body']
        );
        if ($edited) {
            $threadCommentObject = $threadCommentModel->loadObject(
                array(
                    'hash_id' => $formValues['id'],
                    'created_by' => $this->getSession()->getUserId(),
                    'deleted_at IS NULL'
                )
            );
            if ($threadCommentObject === null) {
                echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.edit.failed'));
            } else {
                $threadModel = $this->getDbModel('ForumThread');
                $threadModel->updateLastUpdateTimestamp($threadCommentObject->threadId);
                $threadObject = $threadModel->loadObject(array('id' => $threadCommentObject->threadId));

                $updateModel = $this->getDbModel('Update');
                $updateModel->add(
                    $threadCommentObject->id,
                    \vc\config\EntityTypes::FORUM_COMMENT,
                    \vc\object\Update::ACTION_EDIT,
                    $threadObject->contextType,
                    $threadObject->contextId
                );
                echo \vc\view\json\View::renderStatus(true);
            }
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.edit.failed'));
        }
    }
}
