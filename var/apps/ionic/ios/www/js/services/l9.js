App.service('layout_9', function($rootScope, $location, $timeout, $ionicHistory, $state) {

    var service = {};

    var _features = null;

    service.getTemplate = function() {
        return "templates/home/l9/view.html";
    };

    service.getModalTemplate = function() {
        return "templates/home/modal/view.html";
    };

    service.onResize = function() {
        var time_out = ($rootScope.isOverview) ? 1000 : 300;
        $timeout(function() {
            $ionicHistory.nextViewOptions({
                disableBack: true
            });
            var feature_first = document.getElementById("feature-0");
            angular.element(feature_first).triggerHandler("click");
        }, time_out);
    };

    service.features = function (features, more_button) {
        _features = features;

        return features;
    };

    return service;
});