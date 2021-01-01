<?php
namespace vc\model\service\module\block;

class ChartBlockModule extends \vc\model\service\module\block\AbstractBlockModule
{
    protected function render($path, $imagesPath)
    {
        return $this->element(
            'block/chart',
            array(
                'path' => $path,
                'imagesPath' => $imagesPath,
                'locale' => $this->getLocale()
            )
        );
    }

    protected function getCacheExpires()
    {
        return 86400;
    }
}
