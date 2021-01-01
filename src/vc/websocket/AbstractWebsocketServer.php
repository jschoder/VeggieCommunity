<?php
namespace vc\websocket;

abstract class AbstractWebsocketServer
{
    // redefine this if you want a custom user class.  The custom user class should inherit from WebSocketUser.
    protected $userClass = '\vc\websocket\WebsocketUser';
    protected $maxBufferSize;
    protected $master;
    protected $useTls;
    protected $sockets = array();
    protected $socketWriteBuffers = array();
    protected $users = array();
    protected $userIdOfSocket = array();
    protected $heldMessages = array();
    protected $socketsPendingCrypto = array();
    protected $headerOriginRequired = false;
    protected $headerSecWebSocketProtocolRequired = false;
    protected $headerSecWebSocketExtensionsRequired = false;
    private $runServer = true;

    public function __construct($addr, $port, $useTls = false, $cert = null, $certPass = null,
        $certSelfSigned = false, $bufferLength = 2048)
    {
        $this->maxBufferSize = $bufferLength;
        $this->useTls = $useTls;

        $this->socket_listen($addr, $port, $cert, $certPass, $certSelfSigned);

        $this->sockets['m'] = $this->master;
        $this->log(
            \vc\object\WebsocketServerLog::LOG_LEVEL_INFO,
            "Server started\nListening on: $addr:$port\nMaster socket: " . $this->master
        );
    }

    abstract protected function process($user, $message); // Called immediately when the data is recieved.

    abstract protected function connected($user);        // Called after the handshake response is sent to the client.

    abstract protected function closed($user);           // Called after the connection is closed.

    protected function connecting($user)
    {
        // Override to handle a connecting user, after the instance of the User is created, but before
        // the handshake has completed.
    }

    protected function send($user, $message)
    {
        if ($user->handshake) {
            $message = $this->frame($message, $user);
            $result = $this->socket_write($user->socket, $message, strlen($message));
        } else {
            // User has not yet performed their handshake.  Store for sending later.
            $holdingMessage = array('user' => $user, 'message' => $message);
            $this->heldMessages[] = $holdingMessage;
        }
    }

    protected function tick()
    {
        // Core maintenance processes, such as retrying failed messages.
        foreach ($this->heldMessages as $key => $hm) {
            $user = $this->getUserBySocket($hm['user']->socket);
            if ($user !== null) {
                if ($currentUser->handshake) {
                    unset($this->heldMessages[$key]);
                    $this->send($currentUser, $hm['message']);
                }
            }
            else {
                // If they're no longer in the list of connected users, drop the message.
                unset($this->heldMessages[$key]);
            }
        }

        if ($this->useTls) {
            foreach ($this->socketsPendingCrypto as $index => $socket) {
                $cryptoStatus = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_SERVER);
                if ($cryptoStatus === 0) {
                    continue;
                }

                if ($cryptoStatus === true) {
                    $this->connect($socket);
                    $this->log(
                        \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG,
                        "Client connected. " . $socket
                    );
                }
                else if($cryptoStatus === false) {
                    $this->log(
                        \vc\object\WebsocketServerLog::LOG_LEVEL_WARN,
                        "Client disconnected. Crypto handshake failed: " . $socket
                    );
                    $this->disconnect($socket, true);
                }
                unset($this->socketsPendingCrypto[$index]);
            }
        }
    }

    /**
     * Main processing loop
     */
    public function run()
    {
        while ($this->runServer) {
            if (empty($this->sockets)) {
                $this->sockets['m'] = $this->master;
            }

            $read = $this->sockets;
            $write = array();
            foreach($this->sockets as $socket) {
               if (!empty($this->socketWriteBuffers[(int)$socket])) {
                $write[] = $socket;
               }
            }
            $except = null;

            $this->tick();
            $this->socket_select($read, $write, $except, 0, 10000);
            foreach ($read as $socket) {
                if ($socket === $this->master) {
                    $client = $this->socket_accept($socket);
                    if ($client === false) {
                        $this->log(
                            \vc\object\WebsocketServerLog::LOG_LEVEL_WARN,
                            "Failed: socket_accept()"
                        );
                        continue;
                    } else {
                        if ($this->useTls) {
                            $this->socketsPendingCrypto[] = $client;
                        }
                        else {
                            $this->connect($client);
                            $this->log(
                                \vc\object\WebsocketServerLog::LOG_LEVEL_DEBUG,
                                "Client connected. " . $client
                            );
                        }
                    }
                } else {
                    $numBytes = $this->socket_recv($socket, $buffer, $this->maxBufferSize);
                    if ($numBytes === false) {
                        // client already disconnected, do nothing
                    }
                    else if ($numBytes === 0) {
                        $this->log(
                            \vc\object\WebsocketServerLog::LOG_LEVEL_WARN,
                            "Client disconnected. TCP connection lost: " . $socket
                        );
                        $this->disconnect($socket, true);
                    } else {
                        $user = $this->getUserBySocket($socket);
                        if (!$user->handshake) {
                            if (!empty($user->handshakeBuffer)) {
                                $buffer = $user->handshakeBuffer.$buffer;
                            }
                            $tmp = str_replace("\r", '', $buffer);
                            if (strpos($tmp, "\n\n") === false) {
                                // If the client has not finished sending the header,
                                // then wait before sending our upgrade response.
                                $user->handshakeBuffer = $buffer;
                                continue;
                            }
                            $user->handshakeBuffer = false;
                            $this->doHandshake($user, $buffer);
                        } else {
                            //split packet into frame and send it to deframe
                            $this->splitPacket($numBytes, $buffer, $user);
                        }
                    }
                }
            }
            foreach ($write as $socket) {
                if (!empty($this->socketWriteBuffers[(int)$socket])) {
                    $message = $this->socketWriteBuffers[(int)$socket];
                    $length = strlen($this->socketWriteBuffers[(int)$socket]);

                    if ($this->useTls) {
                        $numWrittenBytes = fwrite($socket, $message, $length);
                    }
                    else {
                        $numWrittenBytes = socket_write($socket, $message, $length);
                    }

                    if ($numWrittenBytes !== false && $numWrittenBytes > 0) {
                        if($numWrittenBytes >= $length) {
                            unset($this->socketWriteBuffers[(int)$socket]);
                        }
                        else {
                            $this->socketWriteBuffers[(int)$socket] = substr($this->socketWriteBuffers[(int)$socket], $numWrittenBytes);
                        }
                    }
                }
            }
        }
    }

    protected function connect($socket)
    {
        $this->socket_getpeername($socket, $address);
        $user = new $this->userClass(uniqid('u'), $socket, $address);
        $this->users[$user->id] = $user;
        $this->sockets[$user->id] = $socket;
        $this->userIdOfSocket[(int)$socket] = $user->id;
        $this->connecting($user);
    }

    protected function disconnect($socket, $triggerClosed = true, $sockErrNo = null)
    {
        $disconnectedUser = $this->getUserBySocket($socket);

        if ($disconnectedUser !== null) {
            unset($this->users[$disconnectedUser->id]);
            unset($this->sockets[$disconnectedUser->id]);
            unset($this->userIdOfSocket[(int)$socket]);

            if (!$this->useTls && !is_null($sockErrNo)) {
                socket_clear_error($socket);
            }

            if ($triggerClosed) {
                unset($this->socketWriteBuffers[(int)$socket]);
                $this->closed($disconnectedUser);
                $this->socket_close($disconnectedUser->socket);
            } else {
                $message = $this->frame('', $disconnectedUser, 'close');
                $this->socket_write($disconnectedUser->socket, $message, strlen($message));
            }
        }
    }

    protected function doHandshake($user, $buffer)
    {
        $magicGUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
        $headers = array();
        $lines = explode("\n", $buffer);
        foreach ($lines as $line) {
            if (strpos($line, ":") !== false) {
                $header = explode(":", $line, 2);
                $headers[strtolower(trim($header[0]))] = trim($header[1]);
            } elseif (stripos($line, "get ") !== false) {
                preg_match("/GET (.*) HTTP/i", $buffer, $reqResource);
                $headers['get'] = trim($reqResource[1]);
            }
        }
        if (isset($headers['get'])) {
            $user->requestedResource = $headers['get'];
        } else {
            // todo: fail the connection
            $handshakeResponse = "HTTP/1.1 405 Method Not Allowed\r\n\r\n";
        }
        if (!isset($headers['host']) || !$this->checkHost($headers['host'])) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($headers['upgrade']) || strtolower($headers['upgrade']) != 'websocket') {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($headers['connection']) || strpos(strtolower($headers['connection']), 'upgrade') === false) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($headers['sec-websocket-key'])) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($headers['sec-websocket-version']) || strtolower($headers['sec-websocket-version']) != 13) {
            $handshakeResponse = "HTTP/1.1 426 Upgrade Required\r\nSec-WebSocketVersion: 13";
        }
        if (($this->headerOriginRequired && !isset($headers['origin']) ) ||
            ($this->headerOriginRequired && !$this->checkOrigin($headers['origin']))) {
            $handshakeResponse = "HTTP/1.1 403 Forbidden";
        }
        if (($this->headerSecWebSocketProtocolRequired &&
             !isset($headers['sec-websocket-protocol'])) ||
            ($this->headerSecWebSocketProtocolRequired &&
             !$this->checkWebsocProtocol($headers['sec-websocket-protocol']))) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (($this->headerSecWebSocketExtensionsRequired &&
             !isset($headers['sec-websocket-extensions'])) ||
            ($this->headerSecWebSocketExtensionsRequired &&
             !$this->checkWebsocExtensions($headers['sec-websocket-extensions']))) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }

        // Done verifying the _required_ headers and optionally required headers.

        if (isset($handshakeResponse)) {
            $this->socket_write($user->socket, $handshakeResponse, strlen($handshakeResponse));
            $this->disconnect($user->socket);
            return;
        }

        $user->headers = $headers;
        $user->handshake = $buffer;

        $webSocketKeyHash = sha1($headers['sec-websocket-key'] . $magicGUID);

        $rawToken = "";
        for ($i = 0; $i < 20; $i++) {
            $rawToken .= chr(hexdec(substr($webSocketKeyHash, $i * 2, 2)));
        }
        $handshakeToken = base64_encode($rawToken) . "\r\n";

        $subProtocol = (isset($headers['sec-websocket-protocol']))
            ? $this->processProtocol($headers['sec-websocket-protocol'])
            : "";
        $extensions = (isset($headers['sec-websocket-extensions']))
            ? $this->processExtensions($headers['sec-websocket-extensions'])
            : "";

        $handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\n" .
                             "Upgrade: websocket\r\n" .
                             "Connection: Upgrade\r\n" .
                             "Sec-WebSocket-Accept: $handshakeToken$subProtocol$extensions\r\n";
        $this->socket_write($user->socket, $handshakeResponse, strlen($handshakeResponse));
        $this->connected($user);
    }

    protected function checkHost($hostName)
    {
        return true; // Override and return false if the host is not one that you would expect.
        // Ex: You only want to accept hosts from the my-domain.com domain,
        // but you receive a host from malicious-site.com instead.
    }

    protected function checkOrigin($origin)
    {
        return true; // Override and return false if the origin is not one that you would expect.
    }

    protected function checkWebsocProtocol($protocol)
    {
        return true; // Override and return false if a protocol is not found that you would expect.
    }

    protected function checkWebsocExtensions($extensions)
    {
        return true; // Override and return false if an extension is not found that you would expect.
    }

    protected function processProtocol($protocol)
    {
        // return either "Sec-WebSocket-Protocol: SelectedProtocolFromClientList\r\n" or return an empty string.
        // The carriage return/newline combo must appear at the end of a non-empty string, and must not
        // appear at the beginning of the string nor in an otherwise empty string, or it will be considered part of
        // the response body, which will trigger an error in the client as it will not be formatted correctly.
        return "";
    }

    protected function processExtensions($extensions)
    {
        return ""; // return either "Sec-WebSocket-Extensions: SelectedExtensions\r\n" or return an empty string.
    }

    protected function getUserBySocket($socket)
    {
        if (isset($this->userIdOfSocket[(int)$socket])) {
            $userId = $this->userIdOfSocket[(int)$socket];
            if (isset($this->users[$userId])) {
                return $this->users[$userId];
            }
        }
        return null;
    }

    abstract protected function log($type, $message);

    protected function frame($message, $user, $messageType = 'text', $messageContinues = false)
    {
        switch ($messageType) {
            case 'continuous':
                $b1 = 0;
                break;
            case 'text':
                $b1 = ($user->sendingContinuous) ? 0 : 1;
                break;
            case 'binary':
                $b1 = ($user->sendingContinuous) ? 0 : 2;
                break;
            case 'close':
                $b1 = 8;
                break;
            case 'ping':
                $b1 = 9;
                break;
            case 'pong':
                $b1 = 10;
                break;
        }
        if ($messageContinues) {
            $user->sendingContinuous = true;
        } else {
            $b1 += 128;
            $user->sendingContinuous = false;
        }

        $length = strlen($message);
        $lengthField = "";
        if ($length < 126) {
            $b2 = $length;
        } elseif ($length <= 65536) {
            $b2 = 126;
            $hexLength = dechex($length);

            if (strlen($hexLength) % 2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;

            for ($i = $n; $i >= 0; $i = $i - 2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while (strlen($lengthField) < 2) {
                $lengthField = chr(0) . $lengthField;
            }
        } else {
            $b2 = 127;
            $hexLength = dechex($length);
            if (strlen($hexLength) % 2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;

            for ($i = $n; $i >= 0; $i = $i - 2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while (strlen($lengthField) < 8) {
                $lengthField = chr(0) . $lengthField;
            }
        }

        return chr($b1) . chr($b2) . $lengthField . $message;
    }

    //check packet if he have more than one frame and process each frame individually
    protected function splitPacket($length, $packet, $user)
    {
        //add PartialPacket and calculate the new $length
        if ($user->handlingPartialPacket) {
            $packet = $user->partialBuffer . $packet;
            $user->handlingPartialPacket = false;
            $length = strlen($packet);
        }
        $fullpacket = $packet;
        $frame_pos = 0;
        $frame_id = 1;

        while ($frame_pos < $length) {
            $headers = $this->extractHeaders($packet);
            $headers_size = $this->calcoffset($headers);
            $framesize = $headers['length'] + $headers_size;

            //split frame from packet and process it
            $frame = substr($fullpacket, $frame_pos, $framesize);

            if (($message = $this->deframe($frame, $user, $headers)) !== false) {
                if ($user->hasSentClose) {
                    $this->disconnect($user->socket);
                } else {
                    if ((preg_match('//u', $message)) || ($headers['opcode'] == 2)) {
                        $this->process($user, $message);
                    } else {
                        $this->log(
                            \vc\object\WebsocketServerLog::LOG_LEVEL_WARN,
                            "not UTF-8"
                        );
                    }
                }
            }
            //get the new position also modify packet data
            $frame_pos+=$framesize;
            $packet = substr($fullpacket, $frame_pos);
            $frame_id++;
        }
    }

    protected function calcoffset($headers)
    {
        $offset = 2;
        if ($headers['hasmask']) {
            $offset += 4;
        }
        if ($headers['length'] > 65535) {
            $offset += 8;
        } elseif ($headers['length'] > 125) {
            $offset += 2;
        }
        return $offset;
    }

    protected function deframe($message, &$user)
    {
        //echo $this->strtohex($message);
        $headers = $this->extractHeaders($message);
        $pongReply = false;
        $willClose = false;
        switch ($headers['opcode']) {
            case 0:
            case 1:
            case 2:
                break;
            case 8:
                // todo: close the connection
                $user->hasSentClose = true;
                return "";
            case 9:
                $pongReply = true;
                break;
            case 10:
                break;
            default:
                // todo: fail connection
                //$this->disconnect($user);
                $willClose = true;
                break;
        }

        /* Deal by splitPacket() as now deframe() do only one frame at a time.
          if ($user->handlingPartialPacket) {
          $message = $user->partialBuffer . $message;
          $user->handlingPartialPacket = false;
          return $this->deframe($message, $user);
          }
         */

        if ($this->checkRSVBits($headers, $user)) {
            return false;
        }

        if ($willClose) {
            // todo: fail the connection
            return false;
        }

        $payload = $user->partialMessage . $this->extractPayload($message, $headers);

        if ($pongReply) {
            $reply = $this->frame($payload, $user, 'pong');
            $this->socket_write($user->socket, $reply, strlen($reply));
            return false;
        }
        if ($headers['length'] > strlen($this->applyMask($headers, $payload))) {
            $user->handlingPartialPacket = true;
            $user->partialBuffer = $message;
            return false;
        }

        $payload = $this->applyMask($headers, $payload);

        if ($headers['fin']) {
            $user->partialMessage = "";
            return $payload;
        }
        $user->partialMessage = $payload;
        return false;
    }

    protected function extractHeaders($message)
    {
        $header = array('fin' => $message[0] & chr(128),
            'rsv1' => $message[0] & chr(64),
            'rsv2' => $message[0] & chr(32),
            'rsv3' => $message[0] & chr(16),
            'opcode' => ord($message[0]) & 15,
            'hasmask' => 0,
            'length' => 0,
            'mask' => "");

        if (!isset($message[1])) {
            return;
        }

        $header['hasmask'] = $message[1] & chr(128);
        $header['length'] = (ord($message[1]) >= 128) ? ord($message[1]) - 128 : ord($message[1]);

        if ($header['length'] == 126) {
            if ($header['hasmask']) {
                $header['mask'] = $message[4] . $message[5] . $message[6] . $message[7];
            }
            $header['length'] = ord($message[2]) * 256 + ord($message[3]);
        } elseif ($header['length'] == 127) {
            if ($header['hasmask']) {
                $header['mask'] = $message[10] . $message[11] . $message[12] . $message[13];
            }
            $header['length'] = ord($message[2]) * 65536 * 65536 * 65536 * 256 +
                                ord($message[3]) * 65536 * 65536 * 65536 +
                                ord($message[4]) * 65536 * 65536 * 256 +
                                ord($message[5]) * 65536 * 65536 +
                                ord($message[6]) * 65536 * 256 +
                                ord($message[7]) * 65536 +
                                ord($message[8]) * 256 +
                                ord($message[9]);
        } elseif ($header['hasmask']) {
            $header['mask'] = $message[2] . $message[3] . $message[4] . $message[5];
        }
        return $header;
    }

    protected function extractPayload($message, $headers)
    {
        $offset = 2;
        if ($headers['hasmask']) {
            $offset += 4;
        }
        if ($headers['length'] > 65535) {
            $offset += 8;
        } elseif ($headers['length'] > 125) {
            $offset += 2;
        }
        return substr($message, $offset);
    }

    protected function applyMask($headers, $payload)
    {
        $effectiveMask = "";
        if ($headers['hasmask']) {
            $mask = $headers['mask'];
        } else {
            return $payload;
        }

        while (strlen($effectiveMask) < strlen($payload)) {
            $effectiveMask .= $mask;
        }
        while (strlen($effectiveMask) > strlen($payload)) {
            $effectiveMask = substr($effectiveMask, 0, -1);
        }
        return $effectiveMask ^ $payload;
    }

    // override this method if you are using an extension where the RSV bits are used.
    protected function checkRSVBits($headers, $user)
    {
        if (ord($headers['rsv1']) + ord($headers['rsv2']) + ord($headers['rsv3']) > 0) {
            //$this->disconnect($user); // todo: fail connection
            return true;
        }
        return false;
    }

    protected function strtohex($str)
    {
        $strout = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $strout .= (ord($str[$i]) < 16) ? "0" . dechex(ord($str[$i])) : dechex(ord($str[$i]));
            $strout .= " ";
            if ($i % 32 == 7) {
                $strout .= ": ";
            }
            if ($i % 32 == 15) {
                $strout .= ": ";
            }
            if ($i % 32 == 23) {
                $strout .= ": ";
            }
            if ($i % 32 == 31) {
                $strout .= "\n";
            }
        }
        return $strout . "\n";
    }

    protected function stop()
    {
        $this->runServer = false;
    }

    protected function socket_listen($addr, $port, $cert = null, $certPass = null, $certSelfSigned = false)
    {
        if ($this->useTls) {
            $options = array('ssl'=>array(
                'local_cert' => $cert,
                'passphrase' => $certPass,
                'ciphers' => 'HIGH',
                'verify_peer' => false,
            ));

            if ($certSelfSigned) {
		        $options['ssl']['allow_self_signed'] = true;
            }

            $ctx = stream_context_create($options);
            $this->master = stream_socket_server('tcp://'.$addr.':'.$port, $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $ctx);

            if (!$this->master) {
                die($errstr.' ('.$errno.')');
            }

             stream_set_blocking($this->master, false);

        } else {
            $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Failed: socket_create()");
            socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die("Failed: socket_option()");
            socket_bind($this->master, $addr, $port) or die("Failed: socket_bind()");
            socket_listen($this->master, 20) or die("Failed: socket_listen()");
            socket_set_nonblock($this->master);
        }
    }

    protected function socket_select(&$read , &$write , &$except, $tv_sec, $tv_usec)
    {
        if ($this->useTls) {
            stream_select($read, $write, $except, $tv_sec, $tv_usec);
        }
        else {
            socket_select($read, $write, $except, $tv_sec, $tv_usec);
        }
    }

    protected function socket_accept($socket)
    {
        if ($this->useTls) {
            $client = stream_socket_accept($socket);
            if ($client !== false) {
                stream_set_blocking($client, false);
            }

        }
        else {
            $client = socket_accept($socket);
            if ($client !== false) {
                socket_set_nonblock($client);
            }
        }

        unset($this->socketWriteBuffers[(int)$client]);

        return $client;
    }

    protected function socket_recv($socket, &$buffer, $maxBufferSize)
    {
        if ($this->useTls) {
           $tmpBuffer = stream_get_contents($socket, $this->maxBufferSize);
           if ($tmpBuffer === false) {
               return 0;
           }
           $buffer = $tmpBuffer;
           return strlen($buffer);
        }
        else {
            $numBytes = socket_recv($socket, $buffer, $this->maxBufferSize, 0);
            if ($numBytes === false) {
                $sockErrNo = socket_last_error($socket);
                switch ($sockErrNo) {
                    // ENETRESET    -- Network dropped connection because of reset
                    case 102:
                    // ECONNABORTED -- Software caused connection abort
                    case 103:
                    // ECONNRESET   -- Connection reset by peer
                    case 104:
                    // ESHUTDOWN    -- Cannot send after transport endpoint shutdown -- probably more of an
                    // error on our part, if we're trying to write after the socket is closed.  Probably not
                    // a critical error, though.
                    case 108:
                    // ETIMEDOUT    -- Connection timed out
                    case 110:
                    // ECONNREFUSED -- Connection refused -- We shouldn't see this one, since we're
                    // listening... Still not a critical error.
                    case 111:
                    // EHOSTDOWN    -- Host is down -- Again, we shouldn't see this, and again, not critical
                    // because it's just one connection and we still want to listen to/for others.
                    case 112:
                    // EHOSTUNREACH -- No route to host
                    case 113:
                    // EREMOTEIO    -- Rempte I/O error -- Their hard drive just blew up.
                    case 121:
                    // ECANCELED    -- Operation canceled
                    case 125:
                        $this->log(
                            \vc\object\WebsocketServerLog::LOG_LEVEL_WARN,
                            "Unusual disconnect on socket (" . $sockErrNo . ") " . $socket
                        );
                        // disconnect before clearing error, in case someone with their own implementation
                        // wants to check for error conditions on the socket.
                        $this->disconnect($socket, true, $sockErrNo);
                        break;
                    default:
                        $this->log(
                            \vc\object\WebsocketServerLog::LOG_LEVEL_ERROR,
                            'Socket error: ' . socket_strerror($sockErrNo)
                        );
                }
            }
            return $numBytes;
        }
    }

    protected function socket_write($socket, $message, $length)
    {
        if (isset($this->socketWriteBuffers[(int)$socket])) {
            $this->socketWriteBuffers[(int)$socket] .= substr($message, 0, $length);
        }
        else {
            $this->socketWriteBuffers[(int)$socket] = substr($message, 0, $length);
        }
    }

    protected function socket_getpeername($socket, &$address)
    {
        if ($this->useTls) {
            $address = stream_socket_get_name($socket, true);
        }
        else {
            socket_getpeername($socket, $address);
        }
    }

    protected function socket_close($socket)
    {
        if ($this->useTls) {
            stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
        }
        else {
            socket_close($socket);
        }
    }
}
