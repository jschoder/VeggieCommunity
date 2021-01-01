<?php
namespace vc\controller\web\like;

class ListController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo '<li class="failed">' . gettext('like.noactivesession') . '</li>';
            return;
        }

        if (count($this->siteParams) < 3) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GET_REQUEST,
                array(
                    'siteParams' => $this->siteParams
                )
            );
            echo '<li class="failed">' . gettext('like.failed') . '</li>';
            return;
        }

        $entityType = intval($this->siteParams[0]);
        $entityHashId = $this->siteParams[1];
        if ($this->siteParams[2] == 'dislikes') {
            $upDown = -1;
        } else {
            $upDown = 1;
        }

        if ($entityType !== \vc\config\EntityTypes::FORUM_THREAD &&
            $entityType !== \vc\config\EntityTypes::FORUM_COMMENT) {
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_GET_REQUEST,
                    array(
                        'siteParams' => $this->siteParams,
                        'entityType' => $entityType,
                    )
                );
            echo '<li class="failed">' . gettext('like.failed') . '</li>';
            return;
        }

        $likeModel = $this->getDbModel('Like');
        $profiles = $likeModel->getProfiles($entityType, $entityHashId, $upDown);

        $this->getView()->set('profiles', $profiles);
        echo $this->getView()->render('like/list');
    }
}
