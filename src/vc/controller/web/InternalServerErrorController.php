<?php
namespace vc\controller\web;

class InternalServerErrorController extends AbstractWebController
{
    public function dispatch()
    {
        try {
            $this->preGetHook();
            $this->handleGet(new \vc\controller\Request($_GET));
        } catch (\Exception $exception) {
            \vc\lib\ErrorHandler::error(
                get_class($exception) . ' :: ' . $exception->getMessage(),
                __FILE__,
                __LINE__
            );
        }
    }

    public function handleGet(\vc\controller\Request $request)
    {
        header('HTTP/1.0 500 Internal Server Error');
        $this->getView()->setHeader('robots', 'noindex, nofollow');
        $this->getView()->set('noLocalePath', '500/');
        echo $this->getView()->render('system/internal_server_error', true);
    }
}
