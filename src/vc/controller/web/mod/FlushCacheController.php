<?php
namespace vc\controller\web\mod;

class FlushCacheController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $deLocale = APP_LOCALE . '/de_DE/LC_MESSAGES/lang' . \vc\config\Globals::VERSION . '.mo';
        if (file_exists($deLocale)) {
            unlink($deLocale);
        }

        $enLocale = APP_LOCALE . '/en_US/LC_MESSAGES/lang' . \vc\config\Globals::VERSION . '.mo';
        if (file_exists($enLocale)) {
            unlink($enLocale);
        }

        $notification = $this->setNotification(self::NOTIFICATION_SUCCESS, 'Cache is flushed.');
        throw new \vc\exception\RedirectException($this->path . 'mysite/?notification=' . $notification);
    }
}
