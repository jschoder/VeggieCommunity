<?php
namespace vc\model\service\module;

abstract class AbstractModule
{
    private $view;

    private $locale;

    private $userId;

    protected function element($elementPath, $params = array())
    {
        return $this->view->element($elementPath, $params);
    }

    final public function getContent(\vc\model\CacheModel $cacheComponent, $path, $imagesPath)
    {
        $cacheKey = $this->getCacheKey();
        if ($cacheKey === null) {
            return $this->render($path, $imagesPath);
        } else {
            $cacheName = $this->getCacheName();
            $content = $cacheComponent->getModuleCache(
                $cacheName,
                $cacheKey
            );
            if ($content === null) {
                $content = $this->render($path, $imagesPath);
                $cacheComponent->setModuleCache(
                    $cacheName,
                    $cacheKey,
                    $content,
                    $this->getCacheExpires()
                );
            }
            return $content;
        }
    }

    abstract protected function getCacheName();

    abstract protected function getCacheKey();

    abstract protected function getCacheExpires();

    abstract protected function render($path, $imagesPath);

    public function setView($view)
    {
        $this->view = $view;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    protected function getUserId()
    {
        return $this->userId;
    }

    protected function getLocale()
    {
        return $this->locale;
    }
}
