/**
 * Popover
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular.module("starter").service("Popover", function ($rootScope, $ionicPopover, $timeout, $q) {
    var service = {
        isOpen: false,
        stack: [],
        currentPopover: null,
        popoverHiddenSubscriber: null,
    };

    /** Listening from $rootScope to prevent external $ionicPopover not proxied */
    $rootScope.$on("popover.shown", function () {
        service.isOpen = true;

        /** Listening for popover.hidden dynamically */
        service.popoverHiddenSubscriber = $rootScope.$on("popover.hidden", function () {
            /** Un-subscribe from popover.hidden RIGHT NOW, otherwise we will create a loop with the automated clean-up */
            service.popoverHiddenSubscriber();

            /** Clean-up popover */
            service.currentPopover.remove();
            service.currentPopover = null;

            /** Unstack next one */
            service.isOpen = false;
            service.unStack();
        });
    });

    /**
     * Un stack popups on event
     */
    service.unStack = function () {
        if (service.stack.length >= 1) {
            $timeout(function () {
                var popover = service.stack.shift();

                switch (popover.type) {
                    case "fromTemplateUrl":
                        service.renderFromTemplateUrl(popover.data);
                        break;
                    case "fromTemplate":
                        service.renderFromTemplate(popover.data);
                        break;
                }
            }, 1);
        } else {
            service.currentPopover = null;

            $timeout(function () {
                return;
            }, 1);
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
            type: "fromTemplateUrl",
            data: {
                templateUrl: templateUrl,
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.isOpen) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderFromTemplateUrl = function (data) {
        return $ionicPopover
        .fromTemplateUrl(data.templateUrl, data.config)
        .then(function (popover) {
            service.currentPopover = popover;
            data.promise.resolve(popover);
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
            type: "fromTemplate",
            data: {
                template: template,
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.isOpen) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderFromTemplate = function (data) {
        return $ionicPopover
        .fromTemplate(data.template, data.config)
        .then(function (popover) {
            service.currentPopover = popover;
            data.promise.resolve(popover);
        });
    };

    return service;
});
