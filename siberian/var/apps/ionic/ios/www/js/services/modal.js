/*global
    App, IS_NATIVE_APP, angular
 */

/**
 * Modal
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Modal', function ($rootScope, $ionicModal, $timeout, $q) {
    var service = {
        is_open                     : false,
        stack                       : [],
        current_modal               : null,
        modal_hidden_subscriber     : null
    };

    /** Listening from $rootScope to prevent external $ionicModal not proxied */
    $rootScope.$on('modal.shown', function () {
        service.is_open = true;

        /** Listening for modal.hidden dynamically */
        service.modal_hidden_subscriber = $rootScope.$on('modal.hidden', function() {
            /** Un-subscribe from modal.hidden RIGHT NOW, otherwise we will create a loop with the automated clean-up */
            service.modal_hidden_subscriber();

            /** Clean-up modal */
            service.current_modal.remove();
            service.current_modal = null;

            /** Unstack next one */
            service.is_open = false;
            service.unStack();
        });
    });

    /**
     * Un stack popups on event
     */
    service.unStack = function () {
        if (service.stack.length >= 1) {
            $timeout(function () {
                var modal = service.stack.shift();

                switch (modal.type) {
                    case 'fromTemplateUrl':
                            service.renderFromTemplateUrl(modal.data);
                        break;
                    case 'fromTemplate':
                            service.renderFromTemplate(modal.data);
                        break;
                }
            }, 250);
        } else {
            service.current_modal = null;

            $timeout(function () {
                return;
            }, 250);
        }
    };

    /**
     *
     * @param templateUrl
     * @param config
     * @returns {*|promise}
     */
    service.fromTemplateUrl = function (templateUrl, config) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'fromTemplateUrl',
            data: {
                templateUrl: templateUrl,
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderFromTemplateUrl = function (data) {
        return $ionicModal
            .fromTemplateUrl(data.templateUrl, data.config)
            .then(function (modal) {
                service.current_modal = modal;
                data.promise.resolve(modal);
            });
    };

    /**
     *
     * @param template
     * @param config
     * @returns {*|promise}
     */
    service.fromTemplate = function (template, config) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'fromTemplate',
            data: {
                template: template,
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderFromTemplate = function (data) {
        return $ionicModal
            .fromTemplate(data.template, data.config)
            .then(function (modal) {
                service.current_modal = modal;
                data.promise.resolve(modal);
            });
    };

    return service;
});
