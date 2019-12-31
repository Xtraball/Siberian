/**
 * CabrideSocket service websocket
 */
angular.module('starter')
    .service('CabrideSocket', function ($injector, $log, $rootScope, $session) {
        var service = {
            hello: {
                event: 'hello'
            },
            websocket: null
        };

        /**
         * Wrapper for JSON.stringify
         * @param object
         */
        service.toMsg = function (object) {
            return JSON.stringify(object);
        };

        service.sayHello = function () {
            service.sendMsg(service.hello);
        };

        /**
         * Simple alias to send raw objects
         * @param object
         */
        service.sendMsg = function (object) {
            switch (service.socket.readyState) {
                default:
                case 0:
                case 2:
                    // Do nothing
                    break;
                case 3:
                    // Tries to reconnect!
                    var factory = $injector.get("Cabride");
                    factory.resetPromises();

                    service.initSocket(factory);
                    break;
                case 1:
                    // Ok send message
                    service.socket.send(service.toMsg(object));
                    break;
            }
        };

        service.onError = function (event) {
            $log.error('cabride socket onError', event);
        };

        service.onClose = function (event) {
            $log.error('cabride socket onClose', event);
        };

        /**
         * Connection handler/init
         * @param factory
         */
        service.initSocket = function (factory) {
            if (typeof factory.onMessage !== 'function') {
                $log.error('onMessage is required and must be a function!');
                return;
            }

            service.socket = new WebSocket(factory.settings.wssUrl);

            factory.socket = service.socket;

            service.socket.onopen = function (event) {
                // Sends Hello to identify as active and protocol is working!
                service.sayHello();

                factory
                .helloPromise.promise
                .then(function (helloResponse) {
                    factory.uuid = helloResponse.uuid;
                    factory.setIsAlive();
                    factory
                    .joinLobby($session.getId(), APP_KEY)
                    .then(function () {
                        factory.initPromise.resolve();
                        factory.startUpdatePosition();
                    }).catch(function (error) {
                        factory.initPromise.reject(error);
                    }).finally(function () {
                        $log.info('cabride joinLobby finally');
                    });
                }).catch(function (error) {
                    factory.initPromise.reject(error);
                }).finally(function () {
                    $log.info('cabride helloPromise finally');
                });
            };

            service.socket.onclose = service.onClose;
            service.socket.onerror = service.onError;
            service.socket.onmessage = function (event) {
                factory.onMessage(JSON.parse(event.data));
            }
        };

        return service;
    });
