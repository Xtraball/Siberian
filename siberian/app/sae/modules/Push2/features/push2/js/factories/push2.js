/**
 * Push v2
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 1.0.0
 */
angular
    .module('starter')
    .factory('Push2', function (Application, Pages, Push2Service, $timeout, $stateParams, $pwaRequest) {

        var factory = {
            storage: [],
            lastValueId: null
        };

        factory.onStart = function () {
            Application.loaded.then(function () {
                // App runtime!
                var push2 = _.find(Pages.getActivePages(), {
                    code: 'push2'
                });

                // Module is not in the App!
                if (!push2) {
                    return;
                }

                factory
                    .setValueId(push2.value_id)
                    .init()
                    .then(function (success) {
                        // Do something!
                        try {
                            $timeout(function () {
                                Push2Service.configure(Application.application.osAppId, Application.application.pushIconcolor);
                                Push2Service.register();
                            }, 500);
                        } catch (e) {
                            console.error('An error occured while registering device for Push.', e.message);
                        }

                    }).catch(function (error) {
                        console.log('push2 error', error);
                    });
            });
        };

        factory.registerPlayer = function (player) {
            return $pwaRequest.post('push2/mobile/register-player', {
                data: {
                    device_uid: $session.getDeviceUid(),
                    player_id: player
                },
                cache: false
            });
        };

        return factory;
    });
