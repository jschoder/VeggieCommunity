<?php
namespace vc\shell\websocket;

class RunWebsocketServerShell extends \vc\shell\AbstractShell
{
    public function run()
    {
        try {
            $lock = new \vc\shell\Lock(TMP_DIR . '/websocket.lock');
            if ($lock->create()) {
                $echo = new \vc\websocket\WebsocketServer($this);
                $echo->run();
            } else {
                echo 'Websocket still running. Won\'t attempt starting another one.';
            }
        } catch (Exception $e) {
            echo 'Start failed :: ';
            echo $e->getMessage();
        }
    }
}
