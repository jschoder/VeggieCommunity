<?php
namespace vc\controller\web\mod\user;

class CommentController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $profileId = $request->getInt('profileid');
        $comment = trim($request->getText('comment'));
        if (!empty($profileId) && !empty($comment)) {
            $profileCommentLog = new \vc\object\ProfileCommentLog();
            $profileCommentLog->profileId = $profileId;
            $profileCommentLog->comment = $comment;

            $profileCommentLogModel = $this->getDbModel('ProfileCommentLog');
            $profileCommentLogModel->insertObject(
                $this->getSession()->getProfile(),
                $profileCommentLog
            );
        }
        throw new \vc\exception\RedirectException(
            $this->path . 'user/view/' . $profileId . '/mod/'
        );
    }
}
