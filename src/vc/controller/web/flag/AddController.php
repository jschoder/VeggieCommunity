<?php
namespace vc\controller\web\flag;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('flag.noactivesession'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['entityType']) ||
            empty($formValues['entityId'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('flag.failed'));
            return;
        }

        $entityType = $formValues['entityType'];
        $entityId = null;
        $aggregateId = null;
        switch ($entityType) {
            case \vc\config\EntityTypes::FORUM_THREAD:
                $forumThreadModel = $this->getDbModel('ForumThread');
                $forumThreadObject = $forumThreadModel->loadObject(array('hash_id' => $formValues['entityId']));
                if (empty($forumThreadObject)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM_THREAD,
                        array(
                            'formValues' => $formValues
                        )
                    );
                    echo \vc\view\json\View::renderStatus(false, gettext('flag.failed'));
                    return;
                }

                // You can't flag auto created feed postings
                if (in_array($forumThreadObject->threadType, \vc\object\ForumThread::$unflaggableThreadTypes)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                        array(
                            'formValues' => $formValues
                        )
                    );
                    echo \vc\view\json\View::renderStatus(false, gettext('flag.failed'));
                    return;
                }

                $entityId = $forumThreadObject->id;
                $aggregateType = $forumThreadObject->contextType;
                $aggregateId = $forumThreadObject->contextId;
                break;

            case \vc\config\EntityTypes::FORUM_COMMENT:
                $threadCommentModel = $this->getDbModel('ForumThreadComment');
                $threadCommentObject = $threadCommentModel->loadObject(array('hash_id' => $formValues['entityId']));
                if (empty($threadCommentObject)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_INVALID_FORUM_COMMENT,
                        array(
                            'formValues' => $formValues
                        )
                    );
                    echo \vc\view\json\View::renderStatus(false, gettext('flag.failed'));
                    return;
                }

                $forumThreadModel = $this->getDbModel('ForumThread');
                $forumThreadObject = $forumThreadModel->loadObject(array('id' => $threadCommentObject->threadId));
                if (empty($forumThreadObject)) {
                    \vc\lib\ErrorHandler::error(
                        'Can\'t find thread for comment ' . $threadCommentObject->threadId,
                        __FILE__,
                        __LINE__
                    );
                    echo \vc\view\json\View::renderStatus(false, gettext('flag.failed'));
                    return;
                }

                $entityId = $threadCommentObject->id;
                $aggregateType = $forumThreadObject->contextType;
                $aggregateId = $forumThreadObject->contextId;
                break;

            default:
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                    array(
                        'type' => 'Invalid entityType',
                        'formValues' => $formValues
                    )
                );
                echo \vc\view\json\View::renderStatus(false, gettext('flag.failed'));
                return;
        }

        $flagObject = new \vc\object\Flag();
        $flagObject->entityType = $entityType;
        $flagObject->entityId = $entityId;
        $flagObject->aggregateType = $aggregateType;
        $flagObject->aggregateId = $aggregateId;

        $flagModel = $this->getDbModel('Flag');
        $objectId = $flagModel->insertObject($this->getSession()->getProfile(), $flagObject);

        if ($objectId !== false) {
            echo \vc\view\json\View::renderStatus(false, gettext('flag.success'));
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('flag.failed'));
        }
    }
}
