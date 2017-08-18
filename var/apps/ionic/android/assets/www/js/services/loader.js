/*global
    App, IS_NATIVE_APP, angular
 */

/**
 * Modal
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Loader', function ($ionicLoading, $translate, $state, $timeout, Dialog) {
    var service = {
        is_open : false,
        last_config : '',
        promise : null,
        timeout : null,
        keep_timeout : false,
        timeout_count : 0
    };

    /**
     * Calls the timeout
     */
    service.callTimeout = function () {
        service.timeout = $timeout(function () {
            service.timeout_count = service.timeout_count + 1;
            service.keep_timeout = true;

            var buttons = ["Go back home", "Continue"];
            if (service.timeout_count >= 2) {
                service.keep_timeout = false;
                buttons = ["Go back home"];
            }

            service.hide();

            Dialog.confirm(
                "Feature timeout",
                "It seems the feature your are trying to load is taking too much time!<br />Would you like to continue?",
                buttons)
                .then(function (result) {
                    if (result || (service.timeout_count >= 2)) {
                        service.hide();
                        $state.go("home");
                    } else {
                        /** Calls only twice. */
                        service.show();
                        service.callTimeout();
                    }
                });
        }, 10000);
    };

    /**
     *
     * @param text
     * @param config
     * @param replace
     * @returns {null}
     */
    service.show = function (text, config, replace) {
        if (replace === undefined) {
            replace = false;
        }

        if (!service.is_open) {
            service.is_open = true;

            var template = "<ion-spinner class=\"spinner-custom\"></ion-spinner>";
            if (text !== undefined) {
                if (!replace) {
                    template = "<ion-spinner class=\"spinner-custom\"></ion-spinner><br /><span>" + $translate.instant(text) + "</span>";
                } else {
                    template = $translate.instant(text);
                }
            }

            if (service.last_config === null) {
                service.last_config = angular.extend({
                    template: template
                }, config);
            }

            service.promise = $ionicLoading.show(service.last_config);

            service.timeout_count = 0;
            if (service.keep_timeout === true) {
                service.callTimeout();
            }
        }

        return service.promise;
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
            service.last_config = null;
        }

        return $ionicLoading.hide();
    };

    return service;
});
