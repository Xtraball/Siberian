/**
 * Push v2
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 1.0.0
 */
angular
    .module('starter')
    .factory('Push2', function (Application, $stateParams, $pwaRequest) {

        var factory = {
            storage: [],
            lastValueId: null
        };

        factory.onStart = function () {
            // Configuring PushService & skip if this is a preview.
            try {
                $timeout(function () {
                    PushService.configure(load.application.osAppId, load.application.pushIconcolor);
                    PushService.register();
                }, 500);
            } catch (e) {
                console.error('An error occured while registering device for Push.', e.message);
            }

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
                    }).catch(function (error) {
                        console.log('push2 error', error);
                    });
            });
        };

        return factory;
    });
