/* global
 angular
 */
angular.module('starter').service('$translate', function () {
    var service = {};

    service.translations = [];

    service.instant = function (text) {
        return angular.isDefined(service.translations[text]) ? service.translations[text] : text;
    };

    return service;
});
