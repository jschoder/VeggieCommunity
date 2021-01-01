<?php
namespace vc\controller\web\mod\watchlist;

class AddController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $profileId = $request->getInt('profileid');
        $undesirable = $request->getInt('undesirable', 0);
        if (!empty($profileId)) {
            $watchlistModel = $this->getDbModel('Watchlist');
            $added = $watchlistModel->add($profileId, $undesirable, $this->getSession()->getUserId());

            if ($added) {
                $profileCommentLog = new \vc\object\ProfileCommentLog();
                $profileCommentLog->profileId = $profileId;
                if ($undesirable === 1) {
                    $profileCommentLog->comment = 'Added to watchlist (undesirable)';
                } else {
                    $profileCommentLog->comment = 'Added to watchlist (not undesirable yet)';
                }
                $profileCommentLogModel = $this->getDbModel('ProfileCommentLog');
                $profileCommentLogModel->insertObject(
                    $this->getSession()->getProfile(),
                    $profileCommentLog
                );
            }
        }
        throw new \vc\exception\RedirectException(
            $this->path . 'user/view/' . $profileId . '/mod/'
        );
    }
}
