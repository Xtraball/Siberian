/**
 * Modal
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Modal', function ($ionicModal, $timeout, $q) {
    var service = {
        stack: [],
        current_modal: null,
        allStack: []
    };

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
            }, 1);
        } else {
            service.current_modal = null;
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

        service.unStack();

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
            service.allStack.push(modal);
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

        service.unStack();

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
            service.allStack.push(modal);
            data.promise.resolve(modal);
        });
    };

    /**
     *
     */
    service.trashAll = function () {
        while(service.allStack.length) {
            try {
                service.allStack.shift().remove();
            } catch (e) {
                // Nope!
                console.log(e.message);
            }
        }
    };

    return service;
});
