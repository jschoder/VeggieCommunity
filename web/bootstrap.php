<?php
$scriptStart = microtime(true);

require_once('../bootstrap.php');

if (!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '151.80.70.177') {
    echo 'Site not available';
    exit;
}

// Dispatching the call
try {
    $controllerFactory = new \vc\controller\ControllerFactory();
    $controller = $controllerFactory->getController();
    $controller->dispatch();
} catch(vc\exception\RedirectException $exception) {
    if($exception->isPermanently()) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header('Location: ' . $exception->getMessage());
} catch(Exception $exception) {
    \vc\lib\ErrorHandler::error(
        get_class($exception) . ': ' . $exception->getMessage(),
        __FILE__,
        __LINE__
    );
}
