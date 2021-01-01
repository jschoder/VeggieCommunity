<?php
namespace vc\controller\web\news;

class DeleteController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mysite.news.failed'));
            return;
        }
        if (empty($this->siteParams)) {
            \vc\lib\ErrorHandler::warning(
                "The parameter 'newsid' is not set.",
                __FILE__,
                __LINE__
            );
            echo \vc\view\json\View::renderStatus(false, gettext('mysite.news.failed'));
            return;
        }
        
        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }
        
        $newsModel = $this->getDbModel('News');
        $deleted = $newsModel->deleteNews(intval($this->siteParams[0]), $this->getSession()->getUserId());
        if ($deleted) {
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false, gettext('mysite.news.failed'));
        }
    }
}
