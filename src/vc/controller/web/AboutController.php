<?php
namespace vc\controller\web;

class AboutController extends AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('menu.about'));
        $this->getView()->setHeader('robots', 'noindex, follow');
        echo $this->getView()->render('about', true);
    }
}
