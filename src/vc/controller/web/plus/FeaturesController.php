<?php
namespace vc\controller\web\plus;

class FeaturesController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('plus.features.title'));
        echo $this->getView()->render('plus/features', true);
    }
}
