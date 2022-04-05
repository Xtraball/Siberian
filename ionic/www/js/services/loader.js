/**
 * Modal
 *
 * @author Xtraball SAS
 */
angular
    .module('starter')
    .service('Loader', function ($ionicLoading, $translate, $state, $timeout, $rootScope, Dialog) {

    var service = {
        is_open: false,
        promise: null,
        timeout: null,
        keep_timeout: false,
        timeout_count: 0,
        absolute_timeout: null,
        last_config: {
            text: null,
            config: null,
            replace: null
        }
    };

    /**
     * Calls the timeout
     */
    service.callTimeout = function () {
        service.timeout = $timeout(function () {
            service.timeout_count = service.timeout_count + 1;
            service.keep_timeout = true;

            var buttons = ['Go back home', 'Continue'];
            if (service.timeout_count >= 2) {
                service.keep_timeout = false;
                buttons = ['Go back home'];
            }

            service.hide();

            Dialog.confirm(
                'Feature timeout',
                'It seems the feature your are trying to load is taking too much time!<br />Would you like to continue?',
                buttons)
                .then(function (result) {
                    if (result || (service.timeout_count >= 2)) {
                        service.keep_timeout = false;
                        service.hide();
                        $state.go('home');
                    } else {
                        /** Calls only twice. */
                        service.show();
                        service.callTimeout();
                    }
                });
        }, 1000);
    };

    /**
     *
     * @param text
     * @param config
     * @param replace
     * @returns {null}
     */
    service.show = function (text, config, replace) {

        // Saving last config
        service.last_config = {
            text: text,
            config: config,
            replace: replace
        };

        if (replace === undefined) {
            replace = false;
        }

        if (!service.is_open) {
            service.is_open = true;

            var template = "<ion-spinner class=\"spinner-custom\"></ion-spinner>";
            if (config && config.hasOwnProperty('template')) {
                template = config.template;
            } else {
                if (text !== undefined) {
                    if (!replace) {
                        template = "<ion-spinner class=\"spinner-custom\"></ion-spinner><br /><span>" + $translate.instant(text) + "</span>";
                    } else {
                        template = $translate.instant(text);
                    }
                }
            }

            var localConfig = angular.extend({
                callbackFn: null,
                callbackLabel: $translate.instant('CANCEL', 'application'),
                withTimeout: true,
                template: template
            }, config);

            // If we have a callback function we automatically adds the button to call it
            if (typeof localConfig.callbackFn === 'function') {
                $rootScope.__loader_cbfn = function () {
                    try {
                        localConfig.callbackFn();
                    } catch (e) {
                        // We must ensure it's not breaking our code
                        console.log(e);
                    }
                };
                localConfig.template +=
                    '<br />' +
                    '<br />' +
                    '<button style="pointer-events: all !important; margin: 0;" ' +
                    '        class="button button-assertive button-assertive-custom button-block" ' +
                    '        ng-click="$root.__loader_cbfn()">' + localConfig.callbackLabel + '</button>'
            }

            service.promise = $ionicLoading.show(localConfig);

            // Adds an option to prevent hard timeout on specific cases
            if (localConfig.withTimeout === true) {
                service.timeout_count = 0;
                if (service.keep_timeout === true) {
                    service.callTimeout();
                }

                service.startAbsoluteTimeout();
            }
        }

        return service.promise;
    };

    /**
     *
     */
    service.startAbsoluteTimeout = function () {
        service.absolute_timeout = $timeout(function () {
            service.hide();
        }, 30000);
    };

    /**
     *
     */
    service.clearAbsoluteTimeout = function () {
        if (service.absolute_timeout !== null) {
            $timeout.cancel(service.absolute_timeout);
            service.absolute_timeout = null;
        }
    };

    /**
     *
     * @returns {boolean}
     */
    service.isOpen = function () {
        return service.is_open;
    };

    /**
     *
     */
    service.reOpenLast = function () {
        service.show(service.last_config.text, service.last_config.config, service.last_config.replace);
    };

    /**
     *
     * @returns {*}
     */
    service.hide = function () {
        service.is_open = false;

        if ((service.keep_timeout === false) && (service.timeout !== null)) {
            $timeout.cancel(service.timeout);
            service.timeout = null;
            service.timeout_count = 0;
        }

        service.clearAbsoluteTimeout();

        return $ionicLoading.hide();
    };

    return service;
});
