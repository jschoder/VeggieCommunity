<?php
namespace vc\controller\web\forum\thread;

class EditController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.noactivesession'));
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
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.edit.failed'));
            return;
        }

        $threadModel = $this->getDbModel('ForumThread');
        $threadObject = $threadModel->loadObject(
            array(
                'hash_id' => $formValues['id'],
                'created_by' => $this->getSession()->getUserId(),
                'deleted_at IS NULL',
            )
        );
        if ($threadObject === null) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.edit.failed'));
        } else {
            echo \vc\view\json\View::render(
                array(
                    'id' => $threadObject->hashId,
                    'subject' => $threadObject->subject,
                    'body' => empty($threadObject->body) ? '' : $threadObject->body,
                    'picture' => $threadObject->has(\vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME)
                        ? $threadObject->additional[\vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME]
                        : null
                )
            );
        }
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.noactivesession'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['id']) ||
            empty($formValues['body'])) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.edit.failed'));
            return;
        }

        $threadModel = $this->getDbModel('ForumThread');
        $edited = $threadModel->edit(
            $formValues['id'],
            $this->getSession()->getUserId(),
            $formValues['subject'],
            $formValues['body']
        );
        if ($edited) {
            $threadObject = $threadModel->loadObject(
                array(
                    'hash_id' => $formValues['id'],
                    'created_by' => $this->getSession()->getUserId(),
                    'deleted_at IS NULL'
                )
            );
            if ($threadObject === null) {
                echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.edit.failed') . ' 111');
            } else {
                $threadModel->updateLastUpdateTimestamp($threadObject->id);

                $updateModel = $this->getDbModel('Update');
                $updateModel->add(
                    $threadObject->id,
                    \vc\config\EntityTypes::FORUM_THREAD,
                    \vc\object\Update::ACTION_EDIT,
                    $threadObject->contextType,
                    $threadObject->contextId
                );
                echo \vc\view\json\View::renderStatus(true);
            }
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.comment.edit.failed') . ' 222');
        }
    }
}
