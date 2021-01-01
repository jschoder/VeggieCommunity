var vc = vc || {};

vc.websocket = {
    key: null,
    socket: null,
    connected: false,
    retry: 0,
    listeners: [],
    
    attach: function (contextType, contextId, callback, fallbackTime) {
        if (vc.websocket.socket == null) {
            if ('%WEBSOCKET_ACTIVE%' == 'true' && 'WebSocket' in window) {
                vc.websocket.init();
            } else {
                vc.websocket.socket = false;
            }
        }

        if (vc.websocket.socket == false) {
            window.setInterval(function() {
                callback();
            }, fallbackTime);

        } else {
            if (vc.websocket.connected) {
                vc.websocket.socket.send({
                    'contextType': contextType,
                    'contextId': contextId,
                    'key': vc.websocket.key
                });
            }
            
            vc.websocket.listeners.push({
                'contextType': contextType,
                'contextId': contextId,
                'callback': callback,
                'fallbackTime': fallbackTime
            });
        }
    },
    
    
    init: function() {
        var host = "wss://%WEBSOCKET_SERVER%:%WEBSOCKET_PORT%/worker";
        try {
            vc.websocket.socket = new WebSocket(host);
            vc.websocket.socket.onopen    = function() {
                console.log('Websocket opened');
                vc.websocket.connected = true;
                vc.websocket.retry = 0;
                $.each(
                    vc.websocket.listeners, 
                    function(index, listener) {
                        var socketRequest = {
                            'contextType': listener.contextType,
                            'contextId': listener.contextId,
                            'key': vc.websocket.key
                        };
                        vc.websocket.socket.send(JSON.stringify(socketRequest));
                    }
                );
            };
            vc.websocket.socket.onmessage = function(msg) {
                console.log('Websocket message');
                console.log(msg);
                try {
                    var json = JSON.parse(msg.data);
                    $.each(
                        vc.websocket.listeners, 
                        function(index, listener) {
                            if (listener.contextType == json[0] &&
                                listener.contextId == json[1]) {
                                listener.callback();
                            }
                        }
                    );
                } catch (e) {
                }
            };
            vc.websocket.socket.onclose   = function(e) {
                console.log('Websocket closed');
                console.log(e);
                if (vc.websocket.connected && vc.websocket.retry < 3) {
                    vc.websocket.retry++;
                    window.setTimeout(vc.websocket.init, vc.websocket.retry * 1000);
                } else {
                    vc.websocket.socket = false;
                    vc.websocket.connected = false;
                    vc.websocket.initFallbacks();
                    
                }
            };
        }
        catch(ex) {
            vc.websocket.socket = false;
        }
        $(window).unload(vc.websocket.quit);
    },
    
    initFallbacks: function() {
        $.each(
            vc.websocket.listeners, 
            function(index, listener) {
                window.setInterval(function() {
                    listener.callback();
                }, listener.fallbackTime);
            }
        );
    },
    
    quit: function() {
        if (vc.websocket.socket != null && vc.websocket.socket != false) {
            vc.websocket.connected = false;
            vc.websocket.listeners = [];
            vc.websocket.socket.close();
        }
    }
};