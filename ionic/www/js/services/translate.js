/**
 * $translate service
 */
angular.module('starter').service('$translate', function () {
    var service = {};

    /**
     *
     * @type {Array}
     */
    service.translations = [];

    /**
     *
     * @param text
     * @param context
     * @returns {*}
     */
    service.instant = function (text, context) {
        if (context === undefined) {
            return angular.isDefined(service.translations[text]) ?
                service.translations[text] : text;
        }
        return angular.isDefined(service.translations[context]) &&
        angular.isDefined(service.translations[context][text]) ?
            service.translations[context][text] : text;
    };

    return service;
});
