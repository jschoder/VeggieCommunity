<?php
namespace vc\websocket;

class WebsocketUser
{
    public $socket;
    public $ip;
    public $id;
    public $headers = array();
    public $handshake = false;
    public $handshakeBuffer = false;

    public $handlingPartialPacket = false;
    public $partialBuffer = "";

    public $sendingContinuous = false;
    public $partialMessage = "";

    public $hasSentClose = false;

    public function __construct($id, $socket, $ip)
    {
        $this->id = $id;
        $this->socket = $socket;
        $this->ip = $ip;
    }
}
