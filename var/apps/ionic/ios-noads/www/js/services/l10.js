App.service('layout_10', function($rootScope) {

    var service = {};

    service.getTemplate = function() {
        return "templates/home/l10/view.html";
    };

    service.getModalTemplate = function() {
        return "templates/home/l10/modal.html";
    };

    service.onResize = function() {};

    service.features = function(features, more_button) {
        var third_option = features.overview.options[2];
        var fourth_option = features.overview.options[3];
        /** Placing more button at the third place (middle in layout) */
        features.overview.options[2] = more_button;
        features.overview.options[3] = third_option;
        features.overview.options[4] = fourth_option;
        /** Removing 4 first option for the modal */
        features.options = features.options.slice(4, features.options.length);

        return features;
    };

    return service;
});