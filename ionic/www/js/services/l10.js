/* global
 angular
 */
angular.module('starter').service('layout_10', function ($rootScope) {
    var service = {};

    service.getTemplate = function () {
        return 'templates/home/l10/view.html';
    };

    service.getModalTemplate = function () {
        return 'templates/home/l10/modal.html';
    };

    service.onResize = function () {};

    service.features = function (features, moreButton) {
        var thirdOption = features.overview.options[2];
        var fourthOption = features.overview.options[3];
        // Placing more button at the third place (middle in layout)!
        features.overview.options[2] = moreButton;
        features.overview.options[3] = thirdOption;
        features.overview.options[4] = fourthOption;
        // Removing 4 first option for the modal!
        features.options = features.options.slice(4, features.options.length);

        return features;
    };

    return service;
});
