<?php
namespace vc\model\db;

class ForumThreadCommentDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_forum_thread_comment';
    const OBJECT_CLASS = '\\vc\object\\ForumThreadComment';

    public function edit($hashId, $userId, $newBody)
    {
        return $this->update(
            array(
                'hash_id' => $hashId,
                'created_by' => $userId
            ),
            array(
                'body' => $newBody,
                'updated_at' => date('Y-m-d H:i:s')
            )
        );
    }

    public function deleteComment($hashId, $userId)
    {
        $threadCommentObject = $this->loadObject(array('hash_id' => $hashId));
        if ($threadCommentObject === null) {
            return false;
        } else {
            if ($threadCommentObject->createdBy != $userId) {
                $threadModel = $this->getDbModel('ForumThread');
                $threadObject = $threadModel->loadObject(array('id' => $threadCommentObject->threadId));
                if ($threadObject !== null &&
                    $threadObject->contextType == \vc\config\EntityTypes::GROUP_FORUM) {
                    $groupForumModel = $this->getDbModel('GroupForum');
                    $groupId = $groupForumModel->getField('group_id', 'id', $threadObject->contextId);
                    $groupRoleModel = $this->getDbModel('GroupRole');
                    $groupRole = $groupRoleModel->getRole($groupId, $userId);
                    if (empty($groupRole)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            $query = 'UPDATE vc_forum_thread_comment ' .
                     'SET deleted_by = ?, deleted_at = ? ' .
                     'WHERE id = ?';
            $statement = $this->getDb()->prepare($query);
            $deleteDate = date('Y-m-d H:i:s');
            $paramsBinded = $statement->bind_param('isi', $userId, $deleteDate, $threadCommentObject->id);
            if (!$paramsBinded) {
                \vc\lib\ErrorHandler::error(
                    'Error binding parameters for thread comment delete',
                    __FILE__,
                    __LINE__,
                    array(
                        'query' => $query,
                        'userId' => $userId,
                        'deleteDate' => $deleteDate,
                        'hashId' => $hashId
                    )
                );
            }
            $executed = $statement->execute();
            $statement->close();
            if (!$executed) {
                \vc\lib\ErrorHandler::error(
                    'Error while deleting thread comment: ' . $statement->errno . ' / ' . $statement->error,
                    __FILE__,
                    __LINE__,
                    array('hashId' => $hashId,
                          'userId' => $userId)
                );
                return false;
            }
            return true;
        }
    }
}
