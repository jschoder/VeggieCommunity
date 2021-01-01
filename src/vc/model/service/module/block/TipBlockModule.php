<?php
namespace vc\model\service\module\block;

class TipBlockModule extends \vc\model\service\module\block\AbstractBlockModule
{
    protected function render($path, $imagesPath)
    {
        $tipModel = $this->getDbModel('Tip');
        $tips = $tipModel->getTips($this->getLocale());

        return $this->element(
            'block/tip',
            array(
                'path' => $path,
                'imagesPath' => $imagesPath,
                'locale' => $this->getLocale(),
                'tips' => $tips
            )
        );
    }

    protected function getCacheExpires()
    {
        return 86400;
    }
}
