<?php
namespace vc\model\service\module\block;

abstract class AbstractBlockModule extends \vc\model\service\module\AbstractModule
{
    protected function getCacheName()
    {
        return 'block';
    }

    protected function getCacheKey()
    {
        $key = str_replace(
            array(
                'vc\\model\\service\\module\\block\\',
                'BlockModule',
                '\\'
            ),
            array(
                '',
                '',
                '_',
            ),
            get_class($this)
        ) . '-' . $this->getLocale();


        if ($this->isUserSpecific()) {
            return $key . '-' . $this->getUserId();
        } elseif ($this->isDifferentForRegistered()) {
            if (empty($this->getUserId())) {
                return $key . '-a'; // anonym
            } else {
                return $key . '-r'; // registered
            }
        } else {
            return $key;
        }
    }

    protected function isUserSpecific()
    {
        return false;
    }

    protected function isDifferentForRegistered()
    {
        return false;
    }

    protected function getCacheExpires()
    {
        return 3600;
    }

    public function setBlockModuleService($blockModuleService)
    {
        $this->blockModuleService = $blockModuleService;
    }

    /**
     * @return \vc\model\db\AbstractModel
     */
    public function getModel($model)
    {
        return $this->blockModuleService->getModel($model);
    }

    /**
     * @return \vc\model\db\AbstractDbModel
     */
    public function getDbModel($model)
    {
        return $this->blockModuleService->getDbModel($model);
    }
    /**
     * @return \vc\component\AbstractComponent
     */
    public function getComponent($model)
    {
        return $this->blockModuleService->getComponent($model);
    }
}
