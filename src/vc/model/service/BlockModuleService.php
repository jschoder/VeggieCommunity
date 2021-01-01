<?php
namespace vc\model\service;

class BlockModuleService extends AbstractModuleService
{
    /**
     * @param \vc\controller\web\AbstractWebController $controller
     * @param \vc\view\html\View $view
     * @param String $site
     * @return string
     */
    public function getBlocks($controller, $view, $site)
    {
        $sessionModel = $this->getModel('Session');
        $cacheModel = $this->getModel('Cache');

        $userId = $sessionModel->getUserId();
        $blocks = array();
        if ($site == 'events/calendar' ||
            $site == 'pm' ||
            strpos($site, 'mod/') === 0 ) {
            // No blocks
            return '';
        } elseif ($site == 'start') {
            $blocks[] = new \vc\model\service\module\block\user\NewBlockModule($controller->getUsersOnline());
            $blocks[] = new \vc\model\service\module\block\group\PopularBlockModule();
            $blocks[] = new \vc\model\service\module\block\event\UpcomingBlockModule();
        } elseif ($site == 'mysite') {
            $blocks[] = new \vc\model\service\module\block\user\SuggestionBlockModule(
                $controller->getUsersOnline(),
                $controller->getBlocked()
            );
            $blocks[] = new \vc\model\service\module\block\TipBlockModule();
            $blocks[] = new \vc\model\service\module\block\user\NewBlockModule($controller->getUsersOnline());
            $blocks[] = new \vc\model\service\module\block\ChartBlockModule();
        } elseif ($site === 'groups' || strpos($site, 'groups/') === 0) {
            $blocks[] = new \vc\model\service\module\block\group\NewBlockModule();
            $blocks[] = new \vc\model\service\module\block\group\PopularBlockModule();
            $blocks[] = new \vc\model\service\module\block\user\NewBlockModule($controller->getUsersOnline());
        } elseif ($site === 'events' || strpos($site, 'events/') === 0) {
            $blocks[] = new \vc\model\service\module\block\event\UpcomingBlockModule();
            $blocks[] = new \vc\model\service\module\block\user\NewBlockModule($controller->getUsersOnline());
            $blocks[] = new \vc\model\service\module\block\user\UpdatedBlockModule($controller->getUsersOnline());
        } else {
            // Default blocks
            $blocks[] = new \vc\model\service\module\block\user\NewBlockModule($controller->getUsersOnline());
            $blocks[] = new \vc\model\service\module\block\user\UpdatedBlockModule($controller->getUsersOnline());
            $blocks[] = new \vc\model\service\module\block\user\OnlineBlockModule($controller->getUsersOnline());
            $blocks[] = new \vc\model\service\module\block\ChartBlockModule();
        }

        $content = '';
        foreach ($blocks as $block) {
            $block->setView($view);
            $block->setBlockModuleService($this);
            $block->setLocale($controller->getLocale());
            $block->setUserId($userId);

            $content .= $block->getContent(
                $cacheModel,
                $controller->getPath(),
                $controller->getImagesPath()
            );
        }
        return $content;
    }
}