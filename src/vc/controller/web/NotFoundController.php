<?php
namespace vc\controller\web;

class NotFoundController extends AbstractWebController
{
    private $message;

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
        header('HTTP/1.0 404 Not Found');
        $this->getView()->setHeader('robots', 'noindex, nofollow');
        echo $this->getView()->render('system/not_found', true);
    }
}
