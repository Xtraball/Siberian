/*global
    angular
 */
angular.module('starter').service('layout_8', function () {
    var service = {};

    service.getTemplate = function () {
        return 'templates/home/l8/view.html';
    };

    service.getModalTemplate = function () {
        return 'templates/home/modal/view.html';
    };

    service.onResize = function () {};

    service.features = function (features, more_button) {
        var first_option = null;
        var options = [];

        if (features.options.length !== 0) {
            first_option = features.options[0];
            options = features.options.slice(1);
        }

        features.first_option = first_option;
        features.options = options;

        return features;
    };

    return service;
});
