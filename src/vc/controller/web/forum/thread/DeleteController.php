<?php
namespace vc\controller\web\forum\thread;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.noactivesession'));
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
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.delete.failed'));
            return;
        }

        $threadModel = $this->getDbModel('ForumThread');
        $threadObject = $threadModel->loadObject(array('hash_id' => $formValues['id']));

        $deleted = $threadModel->deleteThread($formValues['id'], $this->getSession()->getUserId());
        if ($deleted) {
            if ($threadObject !== null) {
                $this->getEventService()->preDelete(
                    \vc\config\EntityTypes::FORUM_THREAD,
                    $threadObject->id,
                    $this->getSession()->getUserId()
                );
            }
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('forum.thread.delete.failed'));
        }
    }
}
