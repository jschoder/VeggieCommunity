<?php
namespace vc\controller\web\plus;

class BookController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('plus.book.title'));
        $this->getView()->set('plusPackages', \vc\object\Plus::$packages);
        $this->getView()->set(
            'paypalActive',
            \vc\config\Globals::$apps[$this->getServer()]['paypal']['active']
        );
        $this->getView()->set(
            'paysafecardActive',
            \vc\config\Globals::$apps[$this->getServer()]['paysafecard']['active']
        );
        $this->getView()->set(
            'sofortActive',
            \vc\config\Globals::$apps[$this->getServer()]['sofort']['active']
        );
        echo $this->getView()->render('plus/book', true);
    }
}
