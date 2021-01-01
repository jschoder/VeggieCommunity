<?php
namespace vc\controller\api;

abstract class AbstractApiController extends \vc\controller\AbstractController
{
    public function setup($db, $modelFactory, $componentFactory, $server, $path, $site, $siteParams, $locale)
    {
        parent::setup($db, $modelFactory, $componentFactory, $server, $path, $site, $siteParams, $locale);
    }

    public function dispatch()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->handleGet(new \vc\controller\Request($_GET));
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePost(new \vc\controller\Request($_POST));
            } else {
                header('HTTP/1.0 405 Method Not Allowed');
            }
        } catch (\vc\exception\NotFoundException $exception) {
            header('HTTP/1.0 404 Not Found');
        } catch (\vc\exception\DBConnectionFailedException $exception) {
            header('HTTP/1.1 500 Internal Server Error');
        } catch (\Exception $exception) {
            \vc\lib\ErrorHandler::error(
                get_class($exception) . ': ' . $exception->getMessage(),
                __FILE__,
                __LINE__,
                array('stacktrace' => var_export($exception->getTraceAsString(), true))
            );
            header('HTTP/1.1 500 Internal Server Error');
        }

        if ($this->logPageView()) {
            $this->addPageViewLog(0);
        }
    }

    public function handleGet(\vc\controller\Request $request)
    {
        // Use as a flag to reduce spam
        if (!empty($_POST)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_GET_REQUEST,
                array(
                    'GET' => $_GET,
                    'POST' => $_POST
                )
            );
        }
        header('HTTP/1.0 405 Method Not Allowed');
    }

    public function handlePost(\vc\controller\Request $request)
    {
        // Use as a flag to reduce spam
        if (!empty($_GET)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'GET' => $_GET,
                    'POST' => $_POST
                )
            );
        }
        header('HTTP/1.0 405 Method Not Allowed');
    }
}
