/**
 * InAppLinks handler!
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
angular
.module('starter')
.service('InAppLinks', function ($rootScope, $state, $injector, $ocLazyLoad, Customer, Codescan, Dialog, Pages) {
    var service = {};

    /**
     * Parsing inAppLink params from an event message!
     * @param event
     */
    service.parseEvent = function (event) {
        // Not an inAppLink
        if (event.data.indexOf('state-go') !== 0) {
            return false;
        }

        var parts = event.data.split('=');
        var action = parts[0];
        var params = {};
        if (parts.length >= 2) {
            action = parts[0];
            params = parts[1].replace(/(^\?)/, '').split(',').map(function (n) {
                return n = n.split(':'), this[n[0].trim()] = n[1], this
            }.bind({}))[0];
            params.action = action;
        }

        params.offline = (params.offline !== undefined) ? (params.offline === 'true') : false;

        return params;
    };

    /**
     * Parsing inAppLink params from an element message!
     * @param element
     */
    service.parseElement = function (element) {
        var params = element.attributes['data-params'].value;
        params = params
            .replace(/(^\?)/,'')
            .split(",")
            .map(function(n){return n = n.split(":"),this[n[0].trim()] = n[1],this}.bind({}))[0];
        params.state = element.attributes['data-state'].value;
        params.offline = (typeof element.attributes["data-offline"] !== "undefined") ?
            (element.attributes["data-offline"].value === "true") : false;

        return params;
    };

    service.action = function (params) {
        // Special in-app link for my account!
        if (params.state === 'my-account') {
            Customer.loginModal();
        } else if (params.state === 'codescan') {
            Codescan.scanGeneric();
        } else if (!params.offline && $rootScope.isOffline) {
            $rootScope.isNotAvailableOffline();
        } else {
            // It's a feature!
            if (params.hasOwnProperty('value_id')) {
                var feature = Pages.getValueId(params.value_id);

                // Feature is disabled, just skip!
                if (feature && !feature.is_active) {
                    Dialog.alert('Error', 'This feature is no longer available.', 'OK', 2350);
                    return;
                }

                // Handles openCallback first
                if (feature.open_callback_class !== null) {
                    try {
                        $ocLazyLoad
                            .load(feature.lazy_load.split(','))
                            .then(function () {
                                $injector.get(feature.open_callback_class).openCallback(feature);
                            });
                    } catch (e) {
                        Dialog.alert('Error', 'This feature is no longer available.', 'OK', 2350);
                    }
                    return;
                }
            }

            $state.go(params.state, params);
        }
    };

    service.handlerGlobal = function (event) {
        var params = service.parseEvent(event);

        if (params === false) {
            // Stop here
            return;
        }

        service.action(params);
    };

    service.handlerLink = function (element) {
        var params = service.parseElement(element);

        angular.element(element).bind('click', function (e) {
            e.preventDefault();

            service.action(params);
        });
    };

    service.listen = function () {
        // Event to catch state-go from source code!
        var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
        var eventer = window[eventMethod];
        var messageEvent = (eventMethod === 'attachEvent') ? 'onmessage' : 'message';

        // Listen to message from child window
        eventer(messageEvent, service.handlerGlobal, false);
    };

    return service;
});
