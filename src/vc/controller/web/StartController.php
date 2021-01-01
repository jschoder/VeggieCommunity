<?php
namespace vc\controller\web;

class StartController extends AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if ($this->getSession()->hasActiveSession()) {
            throw new \vc\exception\RedirectException($this->path . 'mysite/');
        }

        $this->getView()->set(
            'rssUrl',
            'https://www.veggiecommunity.org' . $this->path . "user/result/rss/?locale%5B%5D=" . $this->locale
        );

        $this->getView()->set('wideContent', true);

        $forumThreadModel = $this->getDbModel('ForumThread');
        $news = $forumThreadModel->getNews($this->locale, 3);
        $this->getView()->set('news', $news);

        echo $this->getView()->render('start', true);
    }
}
